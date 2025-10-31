<?php

namespace WechatOfficialAccountBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @extends ServiceEntityRepository<Account>
 */
#[AsRepository(entityClass: Account::class)]
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function save(Account $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Account $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Account
    {
        // 如果传入的是字符串，假设是appId
        if (is_string($id)) {
            return $this->findOneBy(['appId' => $id]);
        }

        // 否则按正常ID查找
        return parent::find($id, $lockMode, $lockVersion);
    }
}
