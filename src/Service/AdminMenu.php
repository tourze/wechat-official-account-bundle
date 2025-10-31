<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Entity\CallbackIP;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('微信公众号')) {
            $item->addChild('微信公众号');
        }

        $wechatMenu = $item->getChild('微信公众号');
        if (null === $wechatMenu) {
            return;
        }

        // 公众号账号管理
        $wechatMenu->addChild('账号管理')
            ->setUri($this->linkGenerator->getCurdListPage(Account::class))
            ->setAttribute('icon', 'fas fa-user-cog')
        ;

        // 回调IP管理
        $wechatMenu->addChild('回调IP管理')
            ->setUri($this->linkGenerator->getCurdListPage(CallbackIP::class))
            ->setAttribute('icon', 'fas fa-server')
        ;
    }
}
