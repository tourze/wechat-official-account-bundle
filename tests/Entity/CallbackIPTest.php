<?php

namespace WechatOfficialAccountBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Entity\CallbackIP;

class CallbackIPTest extends TestCase
{
    private CallbackIP $callbackIP;

    protected function setUp(): void
    {
        $this->callbackIP = new CallbackIP();
    }

    public function testGettersAndSetters(): void
    {
        // 设置属性
        $this->callbackIP->setIp('192.168.1.1');
        $this->callbackIP->setRemark('测试回调IP');
        
        $account = new Account();
        $account->setName('测试公众号');
        $this->callbackIP->setAccount($account);
        
        $this->callbackIP->setCreatedBy('admin');
        $this->callbackIP->setUpdatedBy('admin');
        
        $createTime = new DateTimeImmutable();
        $this->callbackIP->setCreateTime($createTime);
        
        $updateTime = new DateTimeImmutable();
        $this->callbackIP->setUpdateTime($updateTime);

        // 验证属性
        $this->assertEquals('192.168.1.1', $this->callbackIP->getIp());
        $this->assertEquals('测试回调IP', $this->callbackIP->getRemark());
        $this->assertSame($account, $this->callbackIP->getAccount());
        $this->assertEquals('admin', $this->callbackIP->getCreatedBy());
        $this->assertEquals('admin', $this->callbackIP->getUpdatedBy());
        $this->assertSame($createTime, $this->callbackIP->getCreateTime());
        $this->assertSame($updateTime, $this->callbackIP->getUpdateTime());
    }

    public function testToString(): void
    {
        // 设置ID和IP
        $reflection = new \ReflectionClass(CallbackIP::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->callbackIP, '123456789');
        
        $this->callbackIP->setIp('192.168.1.1');

        // 验证__toString方法
        $this->assertEquals('192.168.1.1', (string)$this->callbackIP);
    }

    public function testToStringWithEmptyId(): void
    {
        // 不设置ID，保持为默认值null
        $this->callbackIP->setIp('192.168.1.1');

        // 验证__toString方法对于ID为null的情况
        $this->assertEquals('', (string)$this->callbackIP);
    }

    public function testSetAccount(): void
    {
        // 创建账号
        $account = new Account();
        $account->setName('测试公众号');
        
        // 设置账号
        $result = $this->callbackIP->setAccount($account);
        
        // 验证返回值是自身，支持链式调用
        $this->assertSame($this->callbackIP, $result);
        
        // 验证账号已设置
        $this->assertSame($account, $this->callbackIP->getAccount());
    }

    public function testSetIp(): void
    {
        // 设置IP
        $result = $this->callbackIP->setIp('192.168.1.1');
        
        // 验证返回值是自身，支持链式调用
        $this->assertSame($this->callbackIP, $result);
        
        // 验证IP已设置
        $this->assertEquals('192.168.1.1', $this->callbackIP->getIp());
    }

    public function testSetRemark(): void
    {
        // 设置备注
        $result = $this->callbackIP->setRemark('测试回调IP');
        
        // 验证返回值是自身，支持链式调用
        $this->assertSame($this->callbackIP, $result);
        
        // 验证备注已设置
        $this->assertEquals('测试回调IP', $this->callbackIP->getRemark());
        
        // 测试null值
        $this->callbackIP->setRemark(null);
        $this->assertNull($this->callbackIP->getRemark());
    }

    public function testGetId(): void
    {
        // 初始情况下ID应该为null
        $this->assertNull($this->callbackIP->getId());
        
        // 通过反射设置ID
        $reflection = new \ReflectionClass(CallbackIP::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->callbackIP, '123456789');
        
        // 验证ID
        $this->assertEquals('123456789', $this->callbackIP->getId());
    }
} 