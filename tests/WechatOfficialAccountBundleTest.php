<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatOfficialAccountBundle\WechatOfficialAccountBundle;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatOfficialAccountBundleTest extends AbstractBundleTestCase
{
}
