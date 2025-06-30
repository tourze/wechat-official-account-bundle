<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatOfficialAccountBundle\DependencyInjection\WechatOfficialAccountExtension;

class WechatOfficialAccountExtensionTest extends TestCase
{
    private WechatOfficialAccountExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new WechatOfficialAccountExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证配置已加载
        self::assertNotEmpty($this->container->getDefinitions());
    }
}