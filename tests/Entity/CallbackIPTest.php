<?php

namespace WechatOfficialAccountBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Entity\CallbackIP;

/**
 * @internal
 */
#[CoversClass(CallbackIP::class)]
final class CallbackIPTest extends AbstractEntityTestCase
{
    protected function createEntity(): CallbackIP
    {
        return new CallbackIP();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $account = new Account();
        $account->setName('测试公众号');

        $createTime = new \DateTimeImmutable();
        $updateTime = new \DateTimeImmutable();

        yield 'ip' => ['ip', '192.168.1.1'];
        yield 'remark' => ['remark', '测试回调IP'];
        yield 'account' => ['account', $account];
        yield 'createdBy' => ['createdBy', 'admin'];
        yield 'updatedBy' => ['updatedBy', 'admin'];
        yield 'createTime' => ['createTime', $createTime];
        yield 'updateTime' => ['updateTime', $updateTime];
    }

    public function testToString(): void
    {
        // 设置ID和IP
        $callbackIP = $this->createEntity();
        $reflection = new \ReflectionClass(CallbackIP::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($callbackIP, '123456789');

        $callbackIP->setIp('192.168.1.1');

        // 验证__toString方法
        $this->assertEquals('192.168.1.1', (string) $callbackIP);
    }

    public function testToStringWithEmptyId(): void
    {
        // 不设置ID，保持为默认值null
        $callbackIP = $this->createEntity();
        $callbackIP->setIp('192.168.1.1');

        // 验证__toString方法对于ID为null的情况
        $this->assertEquals('', (string) $callbackIP);
    }

    public function testSetAccount(): void
    {
        // 创建账号
        $account = new Account();
        $account->setName('测试公众号');

        // 设置账号
        $callbackIP = $this->createEntity();
        $callbackIP->setAccount($account);

        // 验证账号已设置
        $this->assertSame($account, $callbackIP->getAccount());
    }

    public function testSetIp(): void
    {
        // 设置IP
        $callbackIP = $this->createEntity();
        $callbackIP->setIp('192.168.1.1');

        // 验证IP已设置
        $this->assertEquals('192.168.1.1', $callbackIP->getIp());
    }

    public function testSetRemark(): void
    {
        // 设置备注
        $callbackIP = $this->createEntity();
        $callbackIP->setRemark('测试回调IP');

        // 验证备注已设置
        $this->assertEquals('测试回调IP', $callbackIP->getRemark());

        // 测试null值
        $callbackIP->setRemark(null);
        $this->assertNull($callbackIP->getRemark());
    }

    public function testGetId(): void
    {
        // 初始情况下ID应该为null
        $callbackIP = $this->createEntity();
        $this->assertNull($callbackIP->getId());

        // 通过反射设置ID
        $reflection = new \ReflectionClass(CallbackIP::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($callbackIP, '123456789');

        // 验证ID
        $this->assertEquals('123456789', $callbackIP->getId());
    }
}
