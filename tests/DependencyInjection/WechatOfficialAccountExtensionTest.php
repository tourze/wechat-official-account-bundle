<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use WechatOfficialAccountBundle\DependencyInjection\WechatOfficialAccountExtension;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountExtension::class)]
final class WechatOfficialAccountExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function createExtension(): WechatOfficialAccountExtension
    {
        return new WechatOfficialAccountExtension();
    }

    protected function getExtensionAlias(): string
    {
        return 'wechat_official_account';
    }
}
