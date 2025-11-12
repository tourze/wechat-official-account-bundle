<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @extends AbstractCrudController<Account>
 */
#[AdminCrud(routePath: '/wechat-official-account/account', routeName: 'wechat_official_account_account')]
#[Autoconfigure(public: true)]
final class AccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Account::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信公众号账号')
            ->setEntityLabelInPlural('公众号账号管理')
            ->setPageTitle('index', '公众号账号列表')
            ->setPageTitle('detail', '公众号账号详情')
            ->setPageTitle('new', '创建公众号账号')
            ->setPageTitle('edit', '编辑公众号账号')
            ->setHelp('index', '管理微信公众号账号信息')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['name', 'appId'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();

        yield TextField::new('name', '账号名称')
            ->setRequired(true)
            ->setMaxLength(32)
            ->setHelp('公众号名称')
        ;

        yield TextField::new('appId', 'AppID')
            ->setRequired(true)
            ->setMaxLength(64)
            ->setHelp('微信公众号AppID')
        ;

        yield TextField::new('appSecret', 'AppSecret')
            ->setRequired(true)
            ->setMaxLength(120)
            ->setHelp('微信公众号AppSecret')
            ->hideOnIndex()
        ;

        yield TextField::new('token', 'TOKEN')
            ->setMaxLength(128)
            ->setHelp('消息加解密TOKEN')
            ->hideOnIndex()
        ;

        yield TextField::new('encodingAesKey', 'EncodingAESKey')
            ->setMaxLength(100)
            ->setHelp('消息加解密密钥')
            ->hideOnIndex()
        ;

        yield TextField::new('componentAppId', '开放平台应用ID')
            ->setMaxLength(64)
            ->setHelp('关联的开放平台应用ID')
            ->hideOnIndex()
        ;

        yield BooleanField::new('valid', '状态')
            ->setHelp('账号是否有效')
            ->renderAsSwitch(false)
        ;

        yield TextField::new('accessToken', 'AccessToken')
            ->setMaxLength(300)
            ->setHelp('当前AccessToken')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('accessTokenExpireTime', 'AccessToken过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('AccessToken过期时间')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('callbackIPs', '回调IP列表')
            ->setHelp('允许的回调IP地址')
            ->onlyOnDetail()
            ->formatValue(function ($value) {
                if (!$value instanceof \Countable) {
                    return '暂无IP配置';
                }
                $count = $value->count();

                return $count > 0 ? "共 {$count} 个IP地址" : '暂无IP配置';
            })
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $refreshTokenAction = Action::new('refreshToken', '刷新Token', 'fas fa-sync')
            ->linkToCrudAction('refreshAccessToken')
            ->displayAsButton()
            ->addCssClass('btn btn-info')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $refreshTokenAction)
            ->disable(Action::EDIT)  // 禁用编辑功能，账号信息应该通过专门的配置接口管理
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '账号名称'))
            ->add(TextFilter::new('appId', 'AppID'))
            ->add(BooleanFilter::new('valid', '状态'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    #[AdminAction(routePath: '{entityId}/refresh-token', routeName: 'wechat_official_account_account_refresh_token')]
    public function refreshAccessToken(AdminContext $context): void
    {
        // 从AdminContext获取当前实体
        $account = $context->getEntity()->getInstance();

        // 这里可以调用刷新AccessToken的服务方法
        // $this->officialAccountService->refreshAccessToken($account);
        $this->addFlash('success', 'AccessToken刷新功能待实现');
    }
}
