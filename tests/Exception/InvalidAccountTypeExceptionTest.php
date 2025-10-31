<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatOfficialAccountBundle\Exception\InvalidAccountTypeException;

/**
 * @internal
 */
#[CoversClass(InvalidAccountTypeException::class)]
final class InvalidAccountTypeExceptionTest extends AbstractExceptionTestCase
{
    public function testOnlyAccountInstancesSupported(): void
    {
        $exception = InvalidAccountTypeException::onlyAccountInstancesSupported();

        $this->assertInstanceOf(InvalidAccountTypeException::class, $exception);
        $this->assertSame('Only Account instances can refresh access token', $exception->getMessage());
    }
}
