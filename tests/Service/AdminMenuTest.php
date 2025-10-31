<?php

namespace WechatOfficialAccountBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatOfficialAccountBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private LinkGeneratorInterface $linkGenerator;

    protected function onSetUp(): void
    {
        $this->linkGenerator = new MockLinkGenerator();
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testInvokeCreatesMenuItems(): void
    {
        // Arrange
        $parentItem = $this->createMock(ItemInterface::class);
        $wechatItem = $this->createMock(ItemInterface::class);
        $accountItem = $this->createMock(ItemInterface::class);
        $callbackIPItem = $this->createMock(ItemInterface::class);

        $parentItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturnOnConsecutiveCalls(null, $wechatItem)
        ;

        $parentItem->expects($this->once())
            ->method('addChild')
            ->with('微信公众号')
            ->willReturn($wechatItem)
        ;

        $wechatItem->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function ($name) use ($accountItem, $callbackIPItem) {
                return match ($name) {
                    '账号管理' => $accountItem,
                    '回调IP管理' => $callbackIPItem,
                    default => $this->createMock(ItemInterface::class),
                };
            })
        ;

        $accountItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/account')
            ->willReturn($accountItem)
        ;

        $accountItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-user-cog')
            ->willReturn($accountItem)
        ;

        $callbackIPItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/callback-ip')
            ->willReturn($callbackIPItem)
        ;

        $callbackIPItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-server')
            ->willReturn($callbackIPItem)
        ;

        // Act
        ($this->adminMenu)($parentItem);

        // Assert - Mock expectations verified implicitly via PHPUnit
    }

    public function testInvokeWithExistingWechatMenu(): void
    {
        // Arrange
        $parentItem = $this->createMock(ItemInterface::class);
        $wechatItem = $this->createMock(ItemInterface::class);
        $accountItem = $this->createMock(ItemInterface::class);
        $callbackIPItem = $this->createMock(ItemInterface::class);

        $parentItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturn($wechatItem)
        ;

        $parentItem->expects($this->never())
            ->method('addChild')
        ;

        $wechatItem->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function ($name) use ($accountItem, $callbackIPItem) {
                return match ($name) {
                    '账号管理' => $accountItem,
                    '回调IP管理' => $callbackIPItem,
                    default => $this->createMock(ItemInterface::class),
                };
            })
        ;

        $accountItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/account')
            ->willReturn($accountItem)
        ;

        $accountItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-user-cog')
            ->willReturn($accountItem)
        ;

        $callbackIPItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/callback-ip')
            ->willReturn($callbackIPItem)
        ;

        $callbackIPItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-server')
            ->willReturn($callbackIPItem)
        ;

        // Act
        ($this->adminMenu)($parentItem);

        // Assert - Mock expectations verified implicitly via PHPUnit
    }

    public function testConstructor(): void
    {
        // 验证 AdminMenu 实例创建成功，依赖注入正常
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    public function testInvokeWithNullWechatMenuHandlesGracefully(): void
    {
        // Arrange - 测试边界情况：getChild返回null后无法继续
        $parentItem = $this->createMock(ItemInterface::class);

        $parentItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturn(null) // 始终返回null，模拟异常情况
        ;

        $parentItem->expects($this->once())
            ->method('addChild')
            ->with('微信公众号')
            ->willReturn($this->createMock(ItemInterface::class))
        ;

        // Act & Assert - 应该正常处理，不抛出异常
        ($this->adminMenu)($parentItem);
    }
}
