<?php

namespace WechatOfficialAccountBundle\Tests\Request\Token;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Request\Token\GetStableTokenRequest;

/**
 * @internal
 */
#[CoversClass(GetStableTokenRequest::class)]
final class GetStableTokenRequestTest extends RequestTestCase
{
    private GetStableTokenRequest $request;

    private Account $account;

    protected function setUp(): void
    {
        $this->request = new GetStableTokenRequest();

        // 创建模拟账号
        // 使用具体类 Account 的原因：
        // 1. GetStableTokenRequest 需要调用 getAppSecret() 方法，而现有的接口不包含此方法
        // 2. 此测试主要验证 GetStableTokenRequest 的行为，需要完整的 Account 接口
        // 3. Account 是稳定的核心实体类，在此上下文中使用具体类比较合适
        $this->account = $this->createMock(Account::class);
        $this->account->method('getAppId')->willReturn('test_app_id');
        $this->account->method('getAppSecret')->willReturn('test_app_secret');

        // 设置账号
        $this->request->setAccount($this->account);
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('https://api.weixin.qq.com/cgi-bin/stable_token', $this->request->getRequestPath());
    }

    public function testGetRequestOptions(): void
    {
        $expectedOptions = [
            'json' => [
                'grant_type' => 'client_credential',
                'appid' => 'test_app_id',
                'secret' => 'test_app_secret',
                'force_refresh' => false,
            ],
        ];

        $this->assertEquals($expectedOptions, $this->request->getRequestOptions());
    }

    public function testGetRequestOptionsWithForceRefresh(): void
    {
        // 设置强制刷新为true
        $this->request->setForceRefresh(true);

        $expectedOptions = [
            'json' => [
                'grant_type' => 'client_credential',
                'appid' => 'test_app_id',
                'secret' => 'test_app_secret',
                'force_refresh' => true,
            ],
        ];

        $this->assertEquals($expectedOptions, $this->request->getRequestOptions());
    }

    public function testGetRequestMethod(): void
    {
        // GetStableTokenRequest没有覆盖getRequestMethod，所以应该是默认的null
        $this->assertNull($this->request->getRequestMethod());
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
        $this->assertNotNull($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('grant_type', $options['json']);
        $this->assertEquals('custom_grant_type', $options['json']['grant_type']);
    }

    public function testGetAccount(): void
    {
        $this->assertSame($this->account, $this->request->getAccount());
    }

    public function testSetAccount(): void
    {
        // 创建新的模拟账号
        // 使用具体类 Account 的原因：
        // 1. 需要模拟 getAppSecret() 方法，现有接口不支持
        // 2. 此测试验证与 Account 实体直接交互的功能
        // 3. Account 是核心实体，在此场景下直接测试比较合适
        $newAccount = $this->createMock(Account::class);
        $newAccount->method('getAppId')->willReturn('new_app_id');
        $newAccount->method('getAppSecret')->willReturn('new_app_secret');

        // 设置新账号
        $this->request->setAccount($newAccount);

        // 验证账号已更新
        $this->assertSame($newAccount, $this->request->getAccount());

        // 验证请求选项中的值也更新了
        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('appid', $options['json']);
        $this->assertArrayHasKey('secret', $options['json']);
        $this->assertEquals('new_app_id', $options['json']['appid']);
        $this->assertEquals('new_app_secret', $options['json']['secret']);
    }
}
