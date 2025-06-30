<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\CallbackIP;
use WechatOfficialAccountBundle\Repository\CallbackIPRepository;

class CallbackIPRepositoryTest extends TestCase
{
    public function testConstruct(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new CallbackIPRepository($registry);
        
        self::assertInstanceOf(CallbackIPRepository::class, $repository);
    }
}