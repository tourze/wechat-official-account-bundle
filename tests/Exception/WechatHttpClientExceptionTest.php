<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Exception;

use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatOfficialAccountBundle\Exception\WechatHttpClientException;

/**
 * @internal
 */
#[CoversClass(WechatHttpClientException::class)]
class WechatHttpClientExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getContent')->willReturn('test content');
        $response->method('getInfo')->willReturn([]);

        $exception = new WechatHttpClientException($request, $response, 'Test message', 123);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertArrayHasKey('request', $exception->getContext());
        $this->assertArrayHasKey('content', $exception->getContext());
        $this->assertArrayHasKey('info', $exception->getContext());
    }

    public function testExceptionInheritsFromHttpClientException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getContent')->willReturn('test content');
        $response->method('getInfo')->willReturn([]);

        $exception = new WechatHttpClientException($request, $response);

        $this->assertInstanceOf(HttpClientException::class, $exception);
    }
}
