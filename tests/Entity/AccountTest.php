<?php

namespace WechatOfficialAccountBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;

class AccountTest extends TestCase
{
    private Account $account;

    protected function setUp(): void
    {
        $this->account = new Account();
    }

    public function testGettersAndSetters(): void
    {
        // 使用反射设置id属性
        $reflection = new \ReflectionClass(Account::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->account, 1);

        // 设置其他属性
        $this->account->setName('测试公众号');
        $this->account->setAppId('wx123456789');
        $this->account->setAppSecret('test_secret');
        $this->account->setToken('test_token');
        $this->account->setEncodingAesKey('test_aes_key');
        $this->account->setComponentAppId('component_app_id');
        $this->account->setValid(true);
        $this->account->setCreatedBy('admin');
        $this->account->setUpdatedBy('admin');

        $createTime = new DateTime();
        $this->account->setCreateTime($createTime);

        $updateTime = new DateTime();
        $this->account->setUpdateTime($updateTime);

        $accessToken = 'test_access_token';
        $this->account->setAccessToken($accessToken);

        $expireTime = new DateTime();
        $this->account->setAccessTokenExpireTime($expireTime);

        // 验证属性
        $this->assertEquals(1, $this->account->getId());
        $this->assertEquals('测试公众号', $this->account->getName());
        $this->assertEquals('wx123456789', $this->account->getAppId());
        $this->assertEquals('test_secret', $this->account->getAppSecret());
        $this->assertEquals('test_token', $this->account->getToken());
        $this->assertEquals('test_aes_key', $this->account->getEncodingAesKey());
        $this->assertEquals('component_app_id', $this->account->getComponentAppId());
        $this->assertTrue($this->account->isValid());
        $this->assertEquals('admin', $this->account->getCreatedBy());
        $this->assertEquals('admin', $this->account->getUpdatedBy());
        $this->assertSame($createTime, $this->account->getCreateTime());
        $this->assertSame($updateTime, $this->account->getUpdateTime());
        $this->assertEquals($accessToken, $this->account->getAccessToken());
        $this->assertSame($expireTime, $this->account->getAccessTokenExpireTime());
    }

    public function testToString(): void
    {
        // 使用反射设置id属性
        $reflection = new \ReflectionClass(Account::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->account, 1);

        // 设置其他属性
        $this->account->setName('测试公众号');
        $this->account->setAppId('wx123456789');

        // 验证__toString方法
        $this->assertEquals('测试公众号(wx123456789)', (string)$this->account);
    }

    public function testToStringWithEmptyId(): void
    {
        // 不设置ID，保持为默认值
        $this->account->setName('测试公众号');
        $this->account->setAppId('wx123456789');

        // 验证__toString方法对于ID为null的情况
        $this->assertEquals('', (string)$this->account);
    }

    public function testGetAccessTokenKeyName(): void
    {
        // 验证getAccessTokenKeyName方法
        $this->assertEquals('access_token', $this->account->getAccessTokenKeyName());
    }

    public function testRetrieveLockResource(): void
    {
        // 设置AppId和AppSecret
        $this->account->setAppId('test_app_id');
        $this->account->setAppSecret('test_app_secret');

        // 验证锁资源格式
        $expectedLockResource = "WechatOfficialAccountBundle_refreshAccessToken_test_app_id_test_app_secret";
        $this->assertEquals($expectedLockResource, $this->account->retrieveLockResource());
    }
}
