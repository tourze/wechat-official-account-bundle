<?php

namespace WechatOfficialAccountBundle\Tests\TestCase;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * WithAccountRequest的具体实现类，用于测试
 */
class ConcreteWithAccountRequest extends WithAccountRequest
{
    private string $requestPath = 'https://api.example.com/test';
    private ?string $requestMethod = 'GET';
    private ?array $requestOptions = null;

    public function getRequestPath(): string
    {
        return $this->requestPath;
    }

    public function setRequestPath(string $requestPath): void
    {
        $this->requestPath = $requestPath;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(?string $requestMethod): void
    {
        $this->requestMethod = $requestMethod;
    }

    public function getRequestOptions(): ?array
    {
        return $this->requestOptions;
    }

    public function setRequestOptions(?array $requestOptions): void
    {
        $this->requestOptions = $requestOptions;
    }
} 