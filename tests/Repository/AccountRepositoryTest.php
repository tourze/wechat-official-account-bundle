<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;

/**
 * @template-extends AbstractRepositoryTestCase<Account>
 * @internal
 */
#[CoversClass(AccountRepository::class)]
#[RunTestsInSeparateProcesses]
final class AccountRepositoryTest extends AbstractRepositoryTestCase
{
    private AccountRepository $repository;

    public function testConstruct(): void
    {
        // 从容器获取服务而不是直接实例化
        $repository = self::getService(AccountRepository::class);

        $this->assertInstanceOf(AccountRepository::class, $repository);
    }

    public function testSaveNewEntityShouldPersistEntity(): void
    {
        $account = new Account();
        $account->setName('New Account');
        $account->setAppId('new_app_id');
        $account->setAppSecret('new_secret');

        $this->repository->save($account);

        $this->assertGreaterThan(0, $account->getId());
        $this->assertEquals('New Account', $account->getName());

        $found = $this->repository->find($account->getId());
        $this->assertInstanceOf(Account::class, $found);
        $this->assertEquals('New Account', $found->getName());
    }

    public function testSaveExistingEntityShouldUpdateEntity(): void
    {
        $account = new Account();
        $account->setName('Original Name');
        $account->setAppId('app_id');
        $account->setAppSecret('secret');

        $this->persistAndFlush($account);

        $account->setName('Updated Name');
        $this->repository->save($account);

        $found = $this->repository->find($account->getId());
        $this->assertInstanceOf(Account::class, $found);
        $this->assertEquals('Updated Name', $found->getName());
    }

    public function testSaveWithFlushFalseShouldNotFlushImmediately(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app');
        $account->setAppSecret('secret');

        $this->repository->save($account, false);

        // 验证实体在UnitOfWork中被管理但还未flush到数据库
        $this->assertTrue(self::getEntityManager()->getUnitOfWork()->isScheduledForInsert($account));
    }

    public function testRemoveEntityShouldDeleteEntity(): void
    {
        $account = new Account();
        $account->setName('To Delete');
        $account->setAppId('delete_app');
        $account->setAppSecret('secret');

        $this->persistAndFlush($account);
        $id = $account->getId();

        $this->repository->remove($account);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testRemoveWithFlushFalseShouldNotFlushImmediately(): void
    {
        $account = new Account();
        $account->setName('To Delete Later');
        $account->setAppId('delete_later_app');
        $account->setAppSecret('secret');

        $this->persistAndFlush($account);
        $id = $account->getId();

        $this->repository->remove($account, false);

        $found = $this->repository->find($id);
        $this->assertInstanceOf(Account::class, $found);
    }

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $repository = $container->get(AccountRepository::class);
        self::assertInstanceOf(AccountRepository::class, $repository);
        $this->repository = $repository;
    }

    protected function createNewEntity(): Account
    {
        $entity = new Account();

        $entity->setName('Test Account ' . uniqid());
        $entity->setAppId('test_app_' . uniqid());
        $entity->setAppSecret('test_secret_' . uniqid());

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<Account>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
