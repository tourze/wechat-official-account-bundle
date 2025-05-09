<?php

namespace WechatOfficialAccountBundle\Tests\Request\Token;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Request\Token\GetTokenRequest;

class GetTokenRequestTest extends TestCase
{
    private GetTokenRequest $request;
    private Account $account;

    protected function setUp(): void
    {
        $this->request = new GetTokenRequest();
        
        // 创建模拟账号
        $this->account = $this->createMock(Account::class);
        $this->account->method('getAppId')->willReturn('test_app_id');
        $this->account->method('getAppSecret')->willReturn('test_app_secret');
        
        // 设置账号
        $this->request->setAccount($this->account);
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/token', $this->request->getRequestPath());
    }

    public function testGetRequestOptions(): void
    {
        $expectedOptions = [
            'query' => [
                'grant_type' => 'client_credential',
                'appid' => 'test_app_id',
                'secret' => 'test_app_secret',
            ],
        ];
        
        $this->assertEquals($expectedOptions, $this->request->getRequestOptions());
    }

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }

    public function testForceRefresh(): void
    {
        // 默认为false
        $this->assertFalse($this->request->isForceRefresh());
        
        // 设置为true
        $this->request->setForceRefresh(true);
        $this->assertTrue($this->request->isForceRefresh());
        
        // 恢复为false
        $this->request->setForceRefresh(false);
        $this->assertFalse($this->request->isForceRefresh());
    }

    public function testGrantType(): void
    {
        // 默认值
        $this->assertEquals('client_credential', $this->request->getGrantType());
        
        // 设置新值
        $this->request->setGrantType('custom_grant_type');
        $this->assertEquals('custom_grant_type', $this->request->getGrantType());
        
        // 验证请求选项中的值也更新了
        $options = $this->request->getRequestOptions();
        $this->assertEquals('custom_grant_type', $options['query']['grant_type']);
    }

    public function testGetAccount(): void
    {
        $this->assertSame($this->account, $this->request->getAccount());
    }

    public function testSetAccount(): void
    {
        // 创建新的模拟账号
        $newAccount = $this->createMock(Account::class);
        $newAccount->method('getAppId')->willReturn('new_app_id');
        $newAccount->method('getAppSecret')->willReturn('new_app_secret');
        
        // 设置新账号
        $this->request->setAccount($newAccount);
        
        // 验证账号已更新
        $this->assertSame($newAccount, $this->request->getAccount());
        
        // 验证请求选项中的值也更新了
        $options = $this->request->getRequestOptions();
        $this->assertEquals('new_app_id', $options['query']['appid']);
        $this->assertEquals('new_app_secret', $options['query']['secret']);
    }
} 