<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatOfficialAccountBundle\Controller\Admin\AccountCrudController;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(AccountCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AccountCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): AccountCrudController
    {
        return self::getService(AccountCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '账号名称' => ['账号名称'];
        yield 'AppID' => ['AppID'];
        yield '状态' => ['状态'];
        yield '创建时间' => ['创建时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        // 编辑功能已在控制器中禁用，但基础测试类要求至少一个字段
        // 测试将在检测到编辑功能禁用后自动跳过
        yield 'name' => ['name'];
    }

    public function testGetEntityFqcn(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin');

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSame(Account::class, AccountCrudController::getEntityFqcn());
    }

    public function testCreateNewAccount(): void
    {
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/wechat-official-account/account/new');

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '创建公众号账号');
    }

    public function testAccountConfiguration(): void
    {
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/wechat-official-account/account');

        self::getClient($client);
        $this->assertResponseIsSuccessful();

        // Check if any data is displayed (should have fixtures data)
        $content = $crawler->text();
        $this->assertStringContainsString('公众号账号', $content);
    }

    public function testRefreshAccessTokenAction(): void
    {
        $client = $this->createAuthenticatedClient();

        // Simply test that the method exists and can be called
        $controllerReflection = new \ReflectionClass(AccountCrudController::class);
        $this->assertTrue($controllerReflection->hasMethod('refreshAccessToken'));

        $method = $controllerReflection->getMethod('refreshAccessToken');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        if (null !== $returnType) {
            $this->assertSame('void', (string) $returnType);
        }
    }

    /**
     * 验证测试数据已正确加载
     */
    public function testCreateTestData(): void
    {
        $client = $this->createAuthenticatedClient();

        // 验证fixtures数据已正确加载
        $em = self::getEntityManager();
        $account = $this->getTestAccount();

        $this->assertNotNull($account, '测试账号应该已通过Fixtures加载');
        $this->assertNotEmpty($account->getName(), '账号名称不应为空');
        $this->assertNotEmpty($account->getAppId(), 'AppID不应为空');
        $this->assertNotEmpty($account->getAppSecret(), 'AppSecret不应为空');
        $this->assertTrue($account->isValid(), '账号应该有效');
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'name' => ['name'];
        yield 'appId' => ['appId'];
        yield 'appSecret' => ['appSecret'];
        yield 'valid' => ['valid'];
    }

    public function testRequiredFieldValidation(): void
    {
        $client = $this->createAuthenticatedClient();

        // Submit form with empty required fields
        $crawler = $client->request('GET', '/admin/wechat-official-account/account/new');
        $form = $crawler->selectButton('Create')->form();

        $crawler = $client->submit($form, [
            'Account[name]' => '',
            'Account[appId]' => '',
            'Account[appSecret]' => '',
        ]);

        // Ensure client is properly set before making assertions
        self::getClient($client);
        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * 获取测试用的Account实体
     *
     * 从 DataFixtures 加载的数据中获取账号
     * cleanDatabase() 会自动加载 AccountFixtures
     */
    private function getTestAccount(): Account
    {
        $em = self::getEntityManager();

        // 从 fixtures 加载的数据中获取第一个账号
        $account = $em->getRepository(Account::class)->findOneBy([]);

        if (null === $account) {
            throw new \RuntimeException('No test account found. Ensure AccountFixtures is loaded.');
        }

        return $account;
    }

    /**
     * 该测试创建测试数据以确保基类的testIndexRowActionLinksShouldNotReturn500有数据可测试
     */
    public function testIndexPageShouldHaveData(): void
    {
        $client = $this->createAuthenticatedClient();
        $account = $this->getTestAccount();

        // 访问索引页面
        $crawler = $client->request('GET', $this->generateAdminUrl('index'));

        // 确保客户端正确设置
        self::getClient($client);
        $this->assertResponseIsSuccessful();

        // 验证至少有一行数据
        $rows = $crawler->filter('table tbody tr[data-id]');
        self::assertGreaterThan(0, $rows->count(), '索引页面应该显示至少一条记录');

        // 验证行动作链接存在
        $actionLinks = $rows->first()->filter('td.actions a[href]');
        self::assertGreaterThan(0, $actionLinks->count(), '每行应该有动作链接');

        // 验证DETAIL链接可以访问
        $detailLink = $actionLinks->filter('[title*="Show"], [title*="查看"], [title*="详情"]')->first();
        if ($detailLink->count() > 0) {
            $href = $detailLink->attr('href');
            if (null !== $href && '' !== $href) {
                $client->request('GET', $href);
                self::assertLessThan(500, $client->getResponse()->getStatusCode(), 'DETAIL链接不应返回500错误');
            }
        }
    }
}
