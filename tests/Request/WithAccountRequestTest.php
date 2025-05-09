<?php

namespace WechatOfficialAccountBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\AccessTokenAware;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Tests\TestCase\ConcreteWithAccountRequest;

class WithAccountRequestTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        // 创建测试对象
        $request = new ConcreteWithAccountRequest();
        
        // 测试默认值
        $this->assertEquals('https://api.example.com/test', $request->getRequestPath());
        $this->assertEquals('GET', $request->getRequestMethod());
        $this->assertNull($request->getRequestOptions());
        
        // 测试设置和获取值
        $request->setRequestPath('https://api.example.com/other');
        $this->assertEquals('https://api.example.com/other', $request->getRequestPath());
        
        $request->setRequestMethod('POST');
        $this->assertEquals('POST', $request->getRequestMethod());
        
        $options = ['query' => ['param' => 'value']];
        $request->setRequestOptions($options);
        $this->assertEquals($options, $request->getRequestOptions());
    }
    
    public function testSetAccountWithAccount(): void
    {
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        
        // 创建测试对象
        $request = new ConcreteWithAccountRequest();
        
        // 设置账号
        $request->setAccount($account);
        
        // 断言
        $this->assertSame($account, $request->getAccount());
    }
    
    public function testSetAccountWithAccessTokenAware(): void
    {
        // 创建模拟对象，同时实现Account和AccessTokenAware接口
        $account = $this->createMock(AccessTokenAware::class);
        
        // 创建测试对象
        $request = new ConcreteWithAccountRequest();
        
        // 设置账号
        $request->setAccount($account);
        
        // 断言
        $this->assertSame($account, $request->getAccount());
    }
} 