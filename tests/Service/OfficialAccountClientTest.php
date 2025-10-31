<?php

namespace WechatOfficialAccountBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountBundle\Entity\AccessTokenAware;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Exception\WechatHttpClientException;
use WechatOfficialAccountBundle\Request\WithAccountRequest;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * @internal
 */
#[CoversClass(OfficialAccountClient::class)]
#[RunTestsInSeparateProcesses]
final class OfficialAccountClientTest extends AbstractIntegrationTestCase
{
    private OfficialAccountClient $client;

    /** @var \ReflectionClass<OfficialAccountClient> */
    private \ReflectionClass $reflection;

    protected function onSetUp(): void
    {
        // 从服务容器获取客户端实例
        $this->client = self::getService(OfficialAccountClient::class);

        // 创建反射类用于访问 protected 方法
        $this->reflection = new \ReflectionClass($this->client);
    }

    /**
     * 调用 protected 方法
     *
     * @param array<int, mixed> $args
     */
    private function callProtectedMethod(string $methodName, array $args = []): mixed
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->client, $args);
    }

    public function testRefreshAccessTokenWithValidResponse(): void
    {
        // 创建Mock对象并进行类型断言
        $logger = $this->createMock(LoggerInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $lockFactory = $this->createMock(LockFactory::class);
        $cache = $this->createMock(CacheInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $asyncInsertService = $this->createMock(AsyncInsertService::class);

        // 创建测试用的可模拟的子类
        $testClient = new class($logger, self::getEntityManager(), $httpClient, $lockFactory, $cache, $eventDispatcher, $asyncInsertService) extends OfficialAccountClient {
            /** @var array<string, mixed> */
            private array $mockResponse = [];

            /**
             * @param array<string, mixed> $response
             */
            public function setMockResponse(array $response): void
            {
                $this->mockResponse = $response;
            }

            public function request(RequestInterface $request): mixed
            {
                return $this->mockResponse;
            }
        };

        // 创建真实的 Account 实体用于测试
        $account = new Account();
        $account->setName('test_account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        // 持久化账户以便后续操作
        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 设置模拟响应
        $testClient->setMockResponse([
            'access_token' => 'test_token',
            'expires_in' => 7200,
        ]);

        // 执行测试
        $testClient->refreshAccessToken($account);

        // 验证 token 已设置
        $this->assertEquals('test_token', $account->getAccessToken());
        $this->assertInstanceOf(\DateTimeInterface::class, $account->getAccessTokenExpireTime());
    }

    public function testRefreshAccessTokenWithDbError(): void
    {
        // 创建Mock对象并进行类型断言
        $logger = $this->createMock(LoggerInterface::class);

        // 创建会抛出数据库异常的 EntityManager mock
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('persist')->willThrowException(new \RuntimeException('Database error'));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $lockFactory = $this->createMock(LockFactory::class);
        $cache = $this->createMock(CacheInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $asyncInsertService = $this->createMock(AsyncInsertService::class);

        // 创建测试客户端，使用mock的EntityManager
        $testClient = new class($logger, $entityManagerMock, $httpClient, $lockFactory, $cache, $eventDispatcher, $asyncInsertService) extends OfficialAccountClient {
            /** @var array<string, mixed> */
            private array $mockResponse = [];

            /**
             * @param array<string, mixed> $response
             */
            public function setMockResponse(array $response): void
            {
                $this->mockResponse = $response;
            }

            public function request(RequestInterface $request): mixed
            {
                return $this->mockResponse;
            }
        };

        // 创建真实的 Account 实体
        $account = new Account();
        $account->setName('test_account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        // 期望记录错误日志
        $logger->expects($this->once())
            ->method('error')
            ->with($this->equalTo('保存AccessToken到数据库时发生异常'), self::arrayHasKey('exception'))
        ;

        // 设置模拟响应
        $testClient->setMockResponse([
            'access_token' => 'test_token',
            'expires_in' => 7200,
        ]);

        // 执行测试 - 应该捕获但不抛出异常
        $testClient->refreshAccessToken($account);

        // 验证即使数据库出错，token 仍然被设置在内存中
        $this->assertEquals('test_token', $account->getAccessToken());
        $this->assertInstanceOf(\DateTimeInterface::class, $account->getAccessTokenExpireTime());
    }

    public function testGetRequestUrlWithNormalRequest(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestPath')->willReturn('https://api.example.com/test');

        // 执行测试
        $result = $this->callProtectedMethod('getRequestUrl', [$request]);

        // 断言
        $this->assertEquals('https://api.example.com/test', $result);
    }

    public function testGetRequestUrlWithAccountRequest(): void
    {
        // 创建模拟对象
        // 使用具体类 Account 的原因：
        // 1. 测试需要验证 getAccessTokenKeyName 和 getAccessToken 方法
        // 2. Account 实现了 AccessTokenAware 接口，是主要的访问令牌载体
        // 3. 此测试专门验证与 Account 类型的 URL 构建逻辑
        $account = $this->createMock(Account::class);
        $account->method('getAccessTokenKeyName')->willReturn('access_token');
        $account->method('getAccessToken')->willReturn('test_token');

        $request = $this->createMock(WithAccountRequest::class);
        $request->method('getRequestPath')->willReturn('https://api.example.com/test');
        $request->method('getAccount')->willReturn($account);

        // 执行测试 - 无参数URL
        $result = $this->callProtectedMethod('getRequestUrl', [$request]);

        // 断言
        $this->assertEquals('https://api.example.com/test?access_token=test_token', $result);

        // 测试带参数URL
        $request = $this->createMock(WithAccountRequest::class);
        $request->method('getRequestPath')->willReturn('https://api.example.com/test?param=value');
        $request->method('getAccount')->willReturn($account);

        // 执行测试
        $result = $this->callProtectedMethod('getRequestUrl', [$request]);

        // 断言
        $this->assertEquals('https://api.example.com/test?param=value&access_token=test_token', $result);
    }

    public function testGetRequestMethodWithDefaultMethod(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn(null);

        // 执行测试
        $result = $this->callProtectedMethod('getRequestMethod', [$request]);

        // 断言
        $this->assertEquals('POST', $result);
    }

    public function testGetRequestMethodWithSpecifiedMethod(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn('GET');

        // 执行测试
        $result = $this->callProtectedMethod('getRequestMethod', [$request]);

        // 断言
        $this->assertEquals('GET', $result);
    }

    public function testGetRequestOptions(): void
    {
        // 创建模拟对象
        $options = ['query' => ['param' => 'value']];
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestOptions')->willReturn($options);

        // 执行测试
        $result = $this->callProtectedMethod('getRequestOptions', [$request]);

        // 断言
        $this->assertEquals($options, $result);
    }

    public function testFormatResponseWithSuccessResponse(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode(['data' => 'test']));

        // 执行测试
        $result = $this->callProtectedMethod('formatResponse', [$request, $response]);

        // 断言
        $this->assertEquals(['data' => 'test'], $result);
    }

    public function testFormatResponseWithErrorResponse(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode([
            'errcode' => 40001,
            'errmsg' => 'invalid credential',
        ]));

        // 断言抛出异常
        $this->expectException(WechatHttpClientException::class);
        $this->expectExceptionMessage('invalid credential');

        // 执行测试
        $this->callProtectedMethod('formatResponse', [$request, $response]);
    }

    public function testRequestWithNormalFlow(): void
    {
        // 创建Mock对象并进行类型断言
        $logger = $this->createMock(LoggerInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $lockFactory = $this->createMock(LockFactory::class);
        $cache = $this->createMock(CacheInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $asyncInsertService = $this->createMock(AsyncInsertService::class);

        // 创建测试用的可模拟的子类
        $testClient = new class($logger, self::getEntityManager(), $httpClient, $lockFactory, $cache, $eventDispatcher, $asyncInsertService) extends OfficialAccountClient {
            /** @var array<string, mixed> */
            private array $mockResponse = [];

            /**
             * @param array<string, mixed> $response
             */
            public function setMockResponse(array $response): void
            {
                $this->mockResponse = $response;
            }

            protected function performActualRequest(RequestInterface $request): mixed
            {
                return $this->mockResponse;
            }

            public function request(RequestInterface $request): mixed
            {
                return $this->performActualRequest($request);
            }
        };

        // 创建模拟请求
        $request = $this->createMock(RequestInterface::class);

        // 设置模拟响应
        $expectedResponse = ['data' => 'test_response'];
        $testClient->setMockResponse($expectedResponse);

        // 执行测试
        $result = $testClient->request($request);

        // 断言
        $this->assertEquals($expectedResponse, $result);
    }

    public function testRequestWithAccessTokenExpiredAndRetry(): void
    {
        // 创建Mock对象并进行类型断言
        $logger = $this->createMock(LoggerInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $lockFactory = $this->createMock(LockFactory::class);
        $cache = $this->createMock(CacheInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $asyncInsertService = $this->createMock(AsyncInsertService::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // 创建测试用的可模拟的子类
        $testClient = new class($logger, self::getEntityManager(), $httpClient, $lockFactory, $cache, $eventDispatcher, $asyncInsertService, $mockResponse) extends OfficialAccountClient {
            private int $callCount = 0;

            /** @var array<string, mixed> */
            private array $mockResponses = [];

            private ResponseInterface $mockResponseObj;

            public function __construct(
                LoggerInterface $logger,
                EntityManagerInterface $entityManager,
                HttpClientInterface $httpClient,
                LockFactory $lockFactory,
                CacheInterface $cache,
                EventDispatcherInterface $eventDispatcher,
                AsyncInsertService $asyncInsertService,
                ResponseInterface $mockResponse,
            ) {
                parent::__construct($logger, $entityManager, $httpClient, $lockFactory, $cache, $eventDispatcher, $asyncInsertService);
                $this->mockResponseObj = $mockResponse;
            }

            /**
             * @param array<string, mixed> $responses
             */
            public function setMockResponses(array $responses): void
            {
                $this->mockResponses = $responses;
            }

            protected function performActualRequest(RequestInterface $request): mixed
            {
                ++$this->callCount;

                if (1 === $this->callCount) {
                    // 第一次调用抛出 access_token expired 异常
                    throw new WechatHttpClientException($request, $this->mockResponseObj, 'access_token expired: invalid token');
                }

                // 第二次调用返回成功响应
                return $this->mockResponses['success'] ?? ['data' => 'success'];
            }

            public function request(RequestInterface $request): mixed
            {
                try {
                    return $this->performActualRequest($request);
                } catch (\Throwable $exception) {
                    if (str_starts_with($exception->getMessage(), 'access_token expired') && $request instanceof WithAccountRequest) {
                        // 刷新 token，然后我们重新请求一次
                        $this->refreshAccessToken($request->getAccount());

                        return $this->performActualRequest($request);
                    }
                    throw $exception;
                }
            }

            public function refreshAccessToken(Account|AccessTokenAware $account): void
            {
                // 模拟刷新 token 的过程，这里不做实际操作
            }
        };

        // 创建模拟的 WithAccountRequest
        $account = $this->createMock(Account::class);
        $request = $this->createMock(WithAccountRequest::class);
        $request->method('getAccount')->willReturn($account);

        // 设置模拟响应
        $expectedResponse = ['data' => 'success_after_retry'];
        $testClient->setMockResponses(['success' => $expectedResponse]);

        // 执行测试
        $result = $testClient->request($request);

        // 断言
        $this->assertEquals($expectedResponse, $result);
    }

    public function testRequestWithNonTokenError(): void
    {
        // 创建Mock对象并进行类型断言
        $logger = $this->createMock(LoggerInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $lockFactory = $this->createMock(LockFactory::class);
        $cache = $this->createMock(CacheInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $asyncInsertService = $this->createMock(AsyncInsertService::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // 创建测试用的可模拟的子类
        $testClient = new class($logger, self::getEntityManager(), $httpClient, $lockFactory, $cache, $eventDispatcher, $asyncInsertService, $mockResponse) extends OfficialAccountClient {
            private ResponseInterface $mockResponseObj;

            public function __construct(
                LoggerInterface $logger,
                EntityManagerInterface $entityManager,
                HttpClientInterface $httpClient,
                LockFactory $lockFactory,
                CacheInterface $cache,
                EventDispatcherInterface $eventDispatcher,
                AsyncInsertService $asyncInsertService,
                ResponseInterface $mockResponse,
            ) {
                parent::__construct($logger, $entityManager, $httpClient, $lockFactory, $cache, $eventDispatcher, $asyncInsertService);
                $this->mockResponseObj = $mockResponse;
            }

            protected function performActualRequest(RequestInterface $request): mixed
            {
                throw new WechatHttpClientException($request, $this->mockResponseObj, 'network error');
            }

            public function request(RequestInterface $request): mixed
            {
                try {
                    return $this->performActualRequest($request);
                } catch (\Throwable $exception) {
                    if (str_starts_with($exception->getMessage(), 'access_token expired') && $request instanceof WithAccountRequest) {
                        // 刷新 token，然后我们重新请求一次
                        $this->refreshAccessToken($request->getAccount());

                        return $this->performActualRequest($request);
                    }
                    throw $exception;
                }
            }
        };

        // 创建模拟请求
        $request = $this->createMock(RequestInterface::class);

        // 断言抛出异常
        $this->expectException(WechatHttpClientException::class);
        $this->expectExceptionMessage('network error');

        // 执行测试
        $testClient->request($request);
    }
}
