<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Entity\CallbackIP;
use WechatOfficialAccountBundle\Repository\CallbackIPRepository;

/**
 * @template-extends AbstractRepositoryTestCase<CallbackIP>
 * @internal
 */
#[CoversClass(CallbackIPRepository::class)]
#[RunTestsInSeparateProcesses]
final class CallbackIPRepositoryTest extends AbstractRepositoryTestCase
{
    private CallbackIPRepository $repository;

    public function testConstruct(): void
    {
        // 从容器获取服务而不是直接实例化
        $repository = self::getService(CallbackIPRepository::class);

        $this->assertInstanceOf(CallbackIPRepository::class, $repository);
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $account1 = $this->createAccount('Account 1', 'app_1', 'secret');
        $account2 = $this->createAccount('Account 2', 'app_2', 'secret');
        $this->persistAndFlush($account1);
        $this->persistAndFlush($account2);

        $ip1 = new CallbackIP();
        $ip1->setAccount($account1);
        $ip1->setIp('192.168.1.1');

        $ip2 = new CallbackIP();
        $ip2->setAccount($account2);
        $ip2->setIp('192.168.1.2');

        $this->persistAndFlush($ip1);
        $this->persistAndFlush($ip2);

        $result = $this->repository->findOneBy(['account' => $account1]);

        $this->assertInstanceOf(CallbackIP::class, $result);
        $this->assertEquals('192.168.1.1', $result->getIp());
        $this->assertEquals($account1->getId(), $result->getAccount()->getId());
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $account1 = $this->createAccount('Account 1', 'app_1', 'secret');
        $account2 = $this->createAccount('Account 2', 'app_2', 'secret');
        $this->persistAndFlush($account1);
        $this->persistAndFlush($account2);

        $ip1 = new CallbackIP();
        $ip1->setAccount($account1);
        $ip1->setIp('192.168.1.1');

        $ip2 = new CallbackIP();
        $ip2->setAccount($account1);
        $ip2->setIp('192.168.1.2');

        $ip3 = new CallbackIP();
        $ip3->setAccount($account2);
        $ip3->setIp('192.168.1.3');

        $this->persistAndFlush($ip1);
        $this->persistAndFlush($ip2);
        $this->persistAndFlush($ip3);

        $result = $this->repository->count(['account' => $account1]);

        $this->assertEquals(2, $result);
    }

    public function testFindByWithAccountRelationShouldReturnMatchingEntities(): void
    {
        $account = $this->createAccount('Test Account', 'test_app', 'secret');
        $this->persistAndFlush($account);

        $ip = new CallbackIP();
        $ip->setAccount($account);
        $ip->setIp('192.168.1.1');

        $this->persistAndFlush($ip);

        $result = $this->repository->findBy(['account' => $account]);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(CallbackIP::class, $result[0]);
        $this->assertEquals($account->getId(), $result[0]->getAccount()->getId());
    }

    public function testSaveNewEntityShouldPersistEntity(): void
    {
        $account = $this->createAccount('Test Account', 'test_app', 'secret');
        $this->persistAndFlush($account);

        $ip = new CallbackIP();
        $ip->setAccount($account);
        $ip->setIp('192.168.1.100');
        $ip->setRemark('New IP');

        $this->repository->save($ip);

        $this->assertNotNull($ip->getId());
        $this->assertEquals('192.168.1.100', $ip->getIp());

        $found = $this->repository->find($ip->getId());
        $this->assertInstanceOf(CallbackIP::class, $found);
        $this->assertEquals('192.168.1.100', $found->getIp());
    }

    public function testSaveExistingEntityShouldUpdateEntity(): void
    {
        $account = $this->createAccount('Test Account', 'test_app', 'secret');
        $this->persistAndFlush($account);

        $ip = new CallbackIP();
        $ip->setAccount($account);
        $ip->setIp('192.168.1.1');
        $ip->setRemark('Original Remark');

        $this->persistAndFlush($ip);

        $ip->setRemark('Updated Remark');
        $this->repository->save($ip);

        $found = $this->repository->find($ip->getId());
        $this->assertNotNull($found);
        $this->assertEquals('Updated Remark', $found->getRemark());
    }

    public function testSaveWithFlushFalseShouldNotFlushImmediately(): void
    {
        $account = $this->createAccount('Test Account', 'test_app', 'secret');
        $this->persistAndFlush($account);

        $ip = new CallbackIP();
        $ip->setAccount($account);
        $ip->setIp('192.168.1.1');

        // 记录调用save前的状态
        $initialEntityCount = $this->repository->count([]);

        $this->repository->save($ip, false);

        // 验证实体在UnitOfWork中被管理但还未flush到数据库
        $this->assertTrue(self::getEntityManager()->getUnitOfWork()->isInIdentityMap($ip));
        $this->assertTrue(self::getEntityManager()->getUnitOfWork()->isScheduledForInsert($ip));

        // 验证数据库中的记录数没有变化（因为没有flush）
        $this->assertEquals($initialEntityCount, $this->repository->count([]));

        self::getEntityManager()->flush();

        // flush后验证实体已被持久化
        $this->assertNotNull($ip->getId());
        $this->assertFalse(self::getEntityManager()->getUnitOfWork()->isScheduledForInsert($ip));
        $this->assertEquals($initialEntityCount + 1, $this->repository->count([]));
    }

    public function testRemoveEntityShouldDeleteEntity(): void
    {
        $account = $this->createAccount('Test Account', 'test_app', 'secret');
        $this->persistAndFlush($account);

        $ip = new CallbackIP();
        $ip->setAccount($account);
        $ip->setIp('192.168.1.100');

        $this->persistAndFlush($ip);
        $id = $ip->getId();

        $this->repository->remove($ip);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testRemoveWithFlushFalseShouldNotFlushImmediately(): void
    {
        $account = $this->createAccount('Test Account', 'test_app', 'secret');
        $this->persistAndFlush($account);

        $ip = new CallbackIP();
        $ip->setAccount($account);
        $ip->setIp('192.168.1.100');

        $this->persistAndFlush($ip);
        $id = $ip->getId();

        $this->repository->remove($ip, false);

        $found = $this->repository->find($id);
        $this->assertInstanceOf(CallbackIP::class, $found);
    }

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $repository = $container->get(CallbackIPRepository::class);
        self::assertInstanceOf(CallbackIPRepository::class, $repository);
        $this->repository = $repository;
    }

    private function createAccount(string $name, string $appId, string $appSecret): Account
    {
        $account = new Account();
        $account->setName($name);
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);

        return $account;
    }

    protected function createNewEntity(): CallbackIP
    {
        $entity = new CallbackIP();

        // 创建并设置必需的 account 关联
        $account = $this->createAccount('Test Account ' . uniqid(), 'app_' . uniqid(), 'secret_' . uniqid());
        $this->persistAndFlush($account);

        $entity->setAccount($account);
        $entity->setIp('192.168.1.' . rand(1, 255));
        $entity->setRemark('Test CallbackIP ' . uniqid());

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<CallbackIP>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
