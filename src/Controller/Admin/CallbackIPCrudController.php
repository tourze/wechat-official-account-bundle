<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatOfficialAccountBundle\Entity\CallbackIP;

/**
 * @extends AbstractCrudController<CallbackIP>
 */
#[AdminCrud(routePath: '/wechat-official-account/callback-ip', routeName: 'wechat_official_account_callback_ip')]
final class CallbackIPCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CallbackIP::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('回调IP')
            ->setEntityLabelInPlural('回调IP管理')
            ->setPageTitle('index', '回调IP列表')
            ->setPageTitle('detail', '回调IP详情')
            ->setPageTitle('new', '添加回调IP')
            ->setPageTitle('edit', '编辑回调IP')
            ->setHelp('index', '管理微信公众号服务端回调IP白名单')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['ip', 'remark'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();

        yield AssociationField::new('account', '所属账号')
            ->setRequired(true)
            ->setHelp('选择公众号账号')
        ;

        yield TextField::new('ip', 'IP地址')
            ->setRequired(true)
            ->setMaxLength(20)
            ->setHelp('允许回调的IP地址')
        ;

        yield TextField::new('remark', '备注')
            ->setMaxLength(100)
            ->setHelp('IP地址用途备注')
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
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '所属账号'))
            ->add(TextFilter::new('ip', 'IP地址'))
            ->add(TextFilter::new('remark', '备注'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
