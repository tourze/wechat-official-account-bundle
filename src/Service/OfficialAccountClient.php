<?php

namespace WechatOfficialAccountBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Request\RequestInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use WechatOfficialAccountBundle\Entity\AccessTokenAware;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Exception\InvalidAccountTypeException;
use WechatOfficialAccountBundle\Exception\WechatHttpClientException;
use WechatOfficialAccountBundle\Request\Token\GetTokenRequest;
use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 微信公众号请求客户端
 */
#[WithMonologChannel(channel: 'wechat_official_account')]
#[Autoconfigure(lazy: true, public: true)]
class OfficialAccountClient extends ApiClient
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
        private readonly LockFactory $lockFactory,
        private readonly CacheInterface $cache,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AsyncInsertService $asyncInsertService,
    ) {
    }

    protected function getLockFactory(): LockFactory
    {
        return $this->lockFactory;
    }

    protected function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getCache(): CacheInterface
    {
        return $this->cache;
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    protected function getAsyncInsertService(): AsyncInsertService
    {
        return $this->asyncInsertService;
    }

    public function request(RequestInterface $request): mixed
    {
        try {
            return parent::request($request);
        } catch (\Throwable $exception) {
            if (str_starts_with($exception->getMessage(), 'access_token expired') && $request instanceof WithAccountRequest) {
                // 刷新 token，然后我们重新请求一次
                $this->refreshAccessToken($request->getAccount());

                return parent::request($request);
            }
            throw $exception;
        }
    }

    /**
     * 刷新 AccessToken
     */
    public function refreshAccessToken(Account|AccessTokenAware $account): void
    {
        if (!$account instanceof Account) {
            throw InvalidAccountTypeException::onlyAccountInstancesSupported();
        }

        $request = new GetTokenRequest();
        $request->setAccount($account);

        $cacheVal = $this->request($request);

        if (!is_array($cacheVal)) {
            throw new \RuntimeException('微信接口返回数据格式错误');
        }

        if (!isset($cacheVal['access_token']) || !is_string($cacheVal['access_token'])) {
            throw new \RuntimeException('微信接口返回的access_token格式错误');
        }

        if (!isset($cacheVal['expires_in']) || !is_int($cacheVal['expires_in'])) {
            throw new \RuntimeException('微信接口返回的expires_in格式错误');
        }

        $account->setAccessToken($cacheVal['access_token']);
        $account->setAccessTokenExpireTime(CarbonImmutable::now()->addSeconds($cacheVal['expires_in'] - 10));

        try {
            $this->entityManager->persist($account);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('保存AccessToken到数据库时发生异常', [
                'exception' => $exception,
            ]);
        }
    }

    protected function getRequestUrl(RequestInterface $request): string
    {
        $path = $request->getRequestPath();
        if ($request instanceof WithAccountRequest) {
            $key = $request->getAccount()->getAccessTokenKeyName();

            $accessToken = $request->getAccount()->getAccessToken();
            if (str_contains($path, '?')) {
                $path = "{$path}&{$key}={$accessToken}";
            } else {
                $path = "{$path}?{$key}={$accessToken}";
            }
        }

        return $path;
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        return $request->getRequestMethod() ?? 'POST';
    }

    /**
     * @return array<array-key, mixed>|null
     */
    protected function getRequestOptions(RequestInterface $request): ?array
    {
        return $request->getRequestOptions();
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        $json = json_decode($response->getContent(), true);

        if (!is_array($json)) {
            throw new WechatHttpClientException($request, $response, '微信接口返回数据格式错误');
        }

        $errcode = $json['errcode'] ?? null;
        $errmsg = is_string($json['errmsg'] ?? null) ? $json['errmsg'] : '微信公众号接口出错';

        if (null !== $errcode && 0 !== $errcode) {
            throw new WechatHttpClientException($request, $response, $errmsg);
        }

        return $json;
    }
}
