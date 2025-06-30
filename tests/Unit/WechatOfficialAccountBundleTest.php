<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WechatOfficialAccountBundle\WechatOfficialAccountBundle;

class WechatOfficialAccountBundleTest extends TestCase
{
    public function testBundleExtends(): void
    {
        $bundle = new WechatOfficialAccountBundle();
        
        $this->assertInstanceOf(Bundle::class, $bundle);
    }
    
    public function testBundleBuild(): void
    {
        $bundle = new WechatOfficialAccountBundle();
        $container = new ContainerBuilder();
        
        // build 方法应该不会抛出异常
        $bundle->build($container);
        
        // 如果没有抛出异常，测试通过
        $this->assertTrue(true);
    }
}