<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatOfficialAccountBundle\Entity\Account;

#[When(env: 'test')]
#[When(env: 'dev')]
class AccountFixtures extends Fixture
{
    public const ACCOUNT_REFERENCE = 'account';

    public function load(ObjectManager $manager): void
    {
        // 创建多个测试账号，确保基类测试有足够数据
        for ($i = 1; $i <= 3; ++$i) {
            $account = new Account();
            $account->setName("测试公众号-{$i}");
            $account->setAppId('test_fixtures_app_id_' . $i . '_' . uniqid());
            $account->setAppSecret("test_app_secret_{$i}");
            $account->setValid(true);
            $account->setCreatedBy('system');
            $account->setUpdatedBy('system');

            $manager->persist($account);

            // 第一个账号设置为主引用
            if (1 === $i) {
                $this->addReference(self::ACCOUNT_REFERENCE, $account);
            }
        }

        $manager->flush();
    }
}
