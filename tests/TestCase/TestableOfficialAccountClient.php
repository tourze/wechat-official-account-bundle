<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\TestCase;

use HttpClientBundle\Request\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * 测试用的 OfficialAccountClient 子类，暴露 protected 方法
 * 
 * @internal 此类仅用于测试，不需要对应的测试类
 */
class TestableOfficialAccountClient extends OfficialAccountClient
{
    public function getRequestUrlTest(RequestInterface $request): string
    {
        return $this->getRequestUrl($request);
    }

    public function getRequestMethodTest(RequestInterface $request): string
    {
        return $this->getRequestMethod($request);
    }

    public function getRequestOptionsTest(RequestInterface $request): ?array
    {
        return $this->getRequestOptions($request);
    }

    public function formatResponseTest(RequestInterface $request, ResponseInterface $response): mixed
    {
        return $this->formatResponse($request, $response);
    }
}