<?php

namespace WechatOfficialAccountBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(Account::class)]
final class AccountTest extends AbstractEntityTestCase
{
    protected function createEntity(): Account
    {
        return new Account();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $createTime = new \DateTimeImmutable();
        $updateTime = new \DateTimeImmutable();
        $expireTime = new \DateTimeImmutable();

        yield 'name' => ['name', '测试公众号'];
        yield 'appId' => ['appId', 'wx123456789'];
        yield 'appSecret' => ['appSecret', 'test_secret'];
        yield 'token' => ['token', 'test_token'];
        yield 'encodingAesKey' => ['encodingAesKey', 'test_aes_key'];
        yield 'componentAppId' => ['componentAppId', 'component_app_id'];
        yield 'valid' => ['valid', true];
        yield 'createdBy' => ['createdBy', 'admin'];
        yield 'updatedBy' => ['updatedBy', 'admin'];
        yield 'createTime' => ['createTime', $createTime];
        yield 'updateTime' => ['updateTime', $updateTime];
        yield 'accessToken' => ['accessToken', 'test_access_token'];
        yield 'accessTokenExpireTime' => ['accessTokenExpireTime', $expireTime];
    }

    public function testToString(): void
    {
        // 使用反射设置id属性
        $account = $this->createEntity();
        $reflection = new \ReflectionClass(Account::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($account, 1);

        // 设置其他属性
        $account->setName('测试公众号');
        $account->setAppId('wx123456789');

        // 验证__toString方法
        $this->assertEquals('测试公众号(wx123456789)', (string) $account);
    }

    public function testToStringWithEmptyId(): void
    {
        // 不设置ID，保持为默认值
        $account = $this->createEntity();
        $account->setName('测试公众号');
        $account->setAppId('wx123456789');

        // 验证__toString方法对于ID为null的情况
        $this->assertEquals('', (string) $account);
    }

    public function testGetAccessTokenKeyName(): void
    {
        // 验证getAccessTokenKeyName方法
        $account = $this->createEntity();
        $this->assertEquals('access_token', $account->getAccessTokenKeyName());
    }

    public function testRetrieveLockResource(): void
    {
        // 设置AppId和AppSecret
        $account = $this->createEntity();
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        // 验证锁资源格式
        $expectedLockResource = 'WechatOfficialAccountBundle_refreshAccessToken_test_app_id_test_app_secret';
        $this->assertEquals($expectedLockResource, $account->retrieveLockResource());
    }
}
