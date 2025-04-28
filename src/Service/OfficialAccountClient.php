<?php

namespace WechatOfficialAccountBundle\Service;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Request\Token\GetTokenRequest;
use WechatOfficialAccountBundle\Request\WithAccountRequest;
use Yiisoft\Json\Json;

/**
 * 微信公众号请求客户端
 */
#[Autoconfigure(lazy: true, public: true)]
class OfficialAccountClient extends ApiClient
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
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
    public function refreshAccessToken(Account $account): void
    {
        $request = new GetTokenRequest();
        $request->setAccount($account);

        $cacheVal = $this->request($request);

        $account->setAccessToken($cacheVal['access_token']);
        $account->setAccessTokenExpireTime(Carbon::now()->addSeconds($cacheVal['expires_in'] - 10));

        try {
            $this->entityManager->persist($account);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->apiClientLogger?->error('保存AccessToken到数据库时发生异常', [
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

    protected function getRequestOptions(RequestInterface $request): ?array
    {
        return $request->getRequestOptions();
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        $json = Json::decode($response->getContent());
        $errcode = $json['errcode'] ?? null;
        $errmsg = $json['errmsg'] ?? '微信公众号接口出错';
        if (null !== $errcode && 0 !== $errcode) {
            throw new HttpClientException($request, $response, $errmsg);
        }

        return $json;
    }
}
