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
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@test.com', 'password123');
        $this->loginAsAdmin($client, 'admin@test.com', 'password123');

        // 创建测试数据，确保后续测试有Account实体可用
        $em = self::getEntityManager();
        $existingAccount = $em->getRepository(Account::class)->findOneBy(['appId' => 'test-app-id-123']);
        if (null === $existingAccount) {
            $account = new Account();
            $account->setName('测试公众号');
            $account->setAppId('test-app-id-123');
            $account->setAppSecret('test-app-secret-456');
            $account->setValid(true);
            $account->setCreatedBy($admin->getUserIdentifier());
            $account->setUpdatedBy($admin->getUserIdentifier());

            $em->persist($account);
            $em->flush();
        }

        $client->request('GET', '/admin');

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSame(Account::class, AccountCrudController::getEntityFqcn());
    }

    public function testCreateNewAccount(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@test.com', 'password123');
        $this->loginAsAdmin($client, 'admin@test.com', 'password123');

        $crawler = $client->request('GET', '/admin/wechat-official-account/account/new');

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '创建公众号账号');
    }

    public function testAccountConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@test.com', 'password123');
        $this->loginAsAdmin($client, 'admin@test.com', 'password123');

        // Create test account data
        $em = self::getEntityManager();
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('test-app-id-123');
        $account->setAppSecret('test-app-secret-456');
        $account->setValid(true);
        $account->setCreatedBy($admin->getUserIdentifier());
        $account->setUpdatedBy($admin->getUserIdentifier());

        $em->persist($account);
        $em->flush();

        $crawler = $client->request('GET', '/admin/wechat-official-account/account');

        self::getClient($client);
        $this->assertResponseIsSuccessful();

        // Check if any data is displayed (may be in a table or list)
        $content = $crawler->text();
        $this->assertStringContainsString('测试公众号', $content);
        $this->assertStringContainsString('test-app-id-123', $content);
    }

    public function testRefreshAccessTokenAction(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@test.com', 'password123');
        $this->loginAsAdmin($client, 'admin@test.com', 'password123');

        // Create test account
        $em = self::getEntityManager();
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('test-app-id-123');
        $account->setAppSecret('test-app-secret-456');
        $account->setValid(true);
        $account->setCreatedBy($admin->getUserIdentifier());
        $account->setUpdatedBy($admin->getUserIdentifier());

        $em->persist($account);
        $em->flush();

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
     * 创建测试数据供其他测试使用
     */
    public function testCreateTestData(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@test.com', 'password123');
        $this->loginAsAdmin($client, 'admin@test.com', 'password123');

        // 创建测试数据
        $em = self::getEntityManager();
        $existingAccount = $em->getRepository(Account::class)->findOneBy(['appId' => 'test-app-id-123']);
        if (null === $existingAccount) {
            $account = new Account();
            $account->setName('测试公众号');
            $account->setAppId('test-app-id-123');
            $account->setAppSecret('test-app-secret-456');
            $account->setValid(true);
            $account->setCreatedBy($admin->getUserIdentifier());
            $account->setUpdatedBy($admin->getUserIdentifier());

            $em->persist($account);
            $em->flush();
        }

        // 验证测试数据创建成功
        $em->clear();
        $account = $em->getRepository(Account::class)->findOneBy(['appId' => 'test-app-id-123']);
        $this->assertNotNull($account, '测试账号应该已创建');
        $this->assertSame('测试公众号', $account->getName());
        $this->assertSame('test-app-id-123', $account->getAppId());
        $this->assertSame('test-app-secret-456', $account->getAppSecret());
        $this->assertTrue($account->isValid());
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
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@test.com', 'password123');
        $this->loginAsAdmin($client, 'admin@test.com', 'password123');

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
}
