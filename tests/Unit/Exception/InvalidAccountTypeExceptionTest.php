<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Exception\InvalidAccountTypeException;

class InvalidAccountTypeExceptionTest extends TestCase
{
    public function testOnlyAccountInstancesSupported(): void
    {
        $exception = InvalidAccountTypeException::onlyAccountInstancesSupported();
        
        $this->assertInstanceOf(InvalidAccountTypeException::class, $exception);
        $this->assertSame('Only Account instances can refresh access token', $exception->getMessage());
    }
}