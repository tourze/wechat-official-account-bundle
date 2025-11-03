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
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('test_fixtures_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_456');
        $account->setValid(true);
        $account->setCreatedBy('system');
        $account->setUpdatedBy('system');

        $manager->persist($account);
        $this->addReference(self::ACCOUNT_REFERENCE, $account);

        $manager->flush();
    }
}
