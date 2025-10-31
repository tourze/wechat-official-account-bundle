<?php

namespace WechatOfficialAccountBundle\Tests\Service;

use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

/**
 * Mock 的 LinkGenerator 实现，用于测试
 */
class MockLinkGenerator implements LinkGeneratorInterface
{
    public function getCurdListPage(string $entityClass): string
    {
        return match ($entityClass) {
            'WechatOfficialAccountBundle\Entity\Account' => '/admin/account',
            'WechatOfficialAccountBundle\Entity\CallbackIP' => '/admin/callback-ip',
            default => '/admin/default',
        };
    }

    public function extractEntityFqcn(string $url): ?string
    {
        return match ($url) {
            '/admin/account' => 'WechatOfficialAccountBundle\Entity\Account',
            '/admin/callback-ip' => 'WechatOfficialAccountBundle\Entity\CallbackIP',
            default => null,
        };
    }

    public function setDashboard(string $dashboardControllerFqcn): void
    {
        // Mock implementation - no-op for testing
    }
}
