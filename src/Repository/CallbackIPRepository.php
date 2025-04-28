<?php

namespace WechatOfficialAccountBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatOfficialAccountBundle\Entity\CallbackIP;

/**
 * @method CallbackIP|null find($id, $lockMode = null, $lockVersion = null)
 * @method CallbackIP|null findOneBy(array $criteria, array $orderBy = null)
 * @method CallbackIP[]    findAll()
 * @method CallbackIP[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallbackIPRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CallbackIP::class);
    }
}
