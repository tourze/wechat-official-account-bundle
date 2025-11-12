<?php

namespace WechatOfficialAccountBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Entity\CallbackIP;

class CallbackIPFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $account = $this->getReference(AccountFixtures::ACCOUNT_REFERENCE, Account::class);

        $callbackIP1 = new CallbackIP();
        $callbackIP1->setAccount($account);
        $callbackIP1->setIp('127.0.0.1');
        $callbackIP1->setRemark('测试IP地址1');
        $callbackIP1->setCreatedBy('system');
        $callbackIP1->setUpdatedBy('system');
        $manager->persist($callbackIP1);

        $callbackIP2 = new CallbackIP();
        $callbackIP2->setAccount($account);
        $callbackIP2->setIp('192.168.1.1');
        $callbackIP2->setRemark('测试IP地址2');
        $callbackIP2->setCreatedBy('system');
        $callbackIP2->setUpdatedBy('system');
        $manager->persist($callbackIP2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
