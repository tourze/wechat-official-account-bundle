<?php

namespace WechatOfficialAccountBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Request\Token\GetTokenRequest;
use WechatOfficialAccountBundle\Request\WithAccountRequest;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountBundle\Tests\TestCase\TestableOfficialAccountClient;

class OfficialAccountClientTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private TestableOfficialAccountClient $client;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // 创建测试用的 OfficialAccountClient 子类
        $this->client = new TestableOfficialAccountClient($this->entityManager);
        
        // 使用反射设置apiClientLogger属性
        $reflection = new \ReflectionClass(get_parent_class($this->client));
        $loggerProperty = $reflection->getProperty('apiClientLogger');
        $loggerProperty->setAccessible(true);
        $loggerProperty->setValue($this->client, $this->logger);
    }


    public function testRefreshAccessToken_withValidResponse(): void
    {
        // 创建测试用的可模拟的子类
        $testClient = new class($this->entityManager) extends OfficialAccountClient {
            private array $mockResponse = [];
            
            public function setMockResponse(array $response): void 
            {
                $this->mockResponse = $response;
            }
            
            public function request(RequestInterface $request): mixed
            {
                return $this->mockResponse;
            }
        };
        
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('setAccessToken')->with('test_token');
        $account->expects($this->once())->method('setAccessTokenExpireTime')
            ->with($this->callback(function ($time) {
                return $time instanceof \DateTimeInterface;
            }));

        // 模拟EntityManager
        $this->entityManager->expects($this->once())->method('persist')->with($account);
        $this->entityManager->expects($this->once())->method('flush');
        
        // 设置模拟响应
        $testClient->setMockResponse([
            'access_token' => 'test_token',
            'expires_in' => 7200,
        ]);

        // 执行测试
        $testClient->refreshAccessToken($account);
    }

    public function testRefreshAccessToken_withDbError(): void
    {
        // 创建测试用的可模拟的子类
        $testClient = new class($this->entityManager) extends OfficialAccountClient {
            private array $mockResponse = [];
            
            public function setMockResponse(array $response): void 
            {
                $this->mockResponse = $response;
            }
            
            public function request(RequestInterface $request): mixed
            {
                return $this->mockResponse;
            }
        };
        
        // 使用反射设置apiClientLogger属性
        $reflection = new \ReflectionClass(get_parent_class($testClient));
        $loggerProperty = $reflection->getProperty('apiClientLogger');
        $loggerProperty->setAccessible(true);
        $loggerProperty->setValue($testClient, $this->logger);
        
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('setAccessToken')->with('test_token');
        $account->expects($this->once())->method('setAccessTokenExpireTime');

        // 模拟EntityManager抛出异常
        $this->entityManager->expects($this->once())->method('persist')->with($account);
        $this->entityManager->expects($this->once())->method('flush')
            ->willThrowException(new \Exception('Database error'));
        
        // 期望记录错误日志
        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->equalTo('保存AccessToken到数据库时发生异常'), $this->arrayHasKey('exception'));
        
        // 设置模拟响应
        $testClient->setMockResponse([
            'access_token' => 'test_token',
            'expires_in' => 7200,
        ]);

        // 执行测试 - 应该捕获但不抛出异常
        $testClient->refreshAccessToken($account);
        // 如果没有抛出异常，测试通过
        $this->assertTrue(true);
    }

    public function testGetRequestUrl_withNormalRequest(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestPath')->willReturn('https://api.example.com/test');

        // 执行测试
        $result = $this->client->getRequestUrlTest($request);

        // 断言
        $this->assertEquals('https://api.example.com/test', $result);
    }

    public function testGetRequestUrl_withAccountRequest(): void
    {
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        $account->method('getAccessTokenKeyName')->willReturn('access_token');
        $account->method('getAccessToken')->willReturn('test_token');

        $request = $this->createMock(WithAccountRequest::class);
        $request->method('getRequestPath')->willReturn('https://api.example.com/test');
        $request->method('getAccount')->willReturn($account);

        // 执行测试 - 无参数URL
        $result = $this->client->getRequestUrlTest($request);

        // 断言
        $this->assertEquals('https://api.example.com/test?access_token=test_token', $result);

        // 测试带参数URL
        $request = $this->createMock(WithAccountRequest::class);
        $request->method('getRequestPath')->willReturn('https://api.example.com/test?param=value');
        $request->method('getAccount')->willReturn($account);

        // 执行测试
        $result = $this->client->getRequestUrlTest($request);

        // 断言
        $this->assertEquals('https://api.example.com/test?param=value&access_token=test_token', $result);
    }

    public function testGetRequestMethod_withDefaultMethod(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn(null);

        // 执行测试
        $result = $this->client->getRequestMethodTest($request);

        // 断言
        $this->assertEquals('POST', $result);
    }

    public function testGetRequestMethod_withSpecifiedMethod(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn('GET');

        // 执行测试
        $result = $this->client->getRequestMethodTest($request);

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
        $result = $this->client->getRequestOptionsTest($request);

        // 断言
        $this->assertEquals($options, $result);
    }

    public function testFormatResponse_withSuccessResponse(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode(['data' => 'test']));

        // 执行测试
        $result = $this->client->formatResponseTest($request, $response);

        // 断言
        $this->assertEquals(['data' => 'test'], $result);
    }

    public function testFormatResponse_withErrorResponse(): void
    {
        // 创建模拟对象
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode([
            'errcode' => 40001,
            'errmsg' => 'invalid credential',
        ]));

        // 断言抛出异常
        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('invalid credential');

        // 执行测试
        $this->client->formatResponseTest($request, $response);
    }
} 