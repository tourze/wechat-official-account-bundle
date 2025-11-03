<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
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

        // 创建测试数据，确保后续测试有Account实体可用
        $em = self::getEntityManager();
        $existingAccount = $em->getRepository(Account::class)->findOneBy(['appId' => 'test-app-id-123']);
        if (null === $existingAccount) {
            $account = new Account();
            $account->setName('测试公众号');
            $account->setAppId('test-app-id-123');
            $account->setAppSecret('test-app-secret-456');
            $account->setValid(true);
            $account->setCreatedBy('admin');
            $account->setUpdatedBy('admin');

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
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/wechat-official-account/account/new');

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '创建公众号账号');
    }

    public function testAccountConfiguration(): void
    {
        $client = $this->createAuthenticatedClient();

        // Create test account data
        $em = self::getEntityManager();
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('test-app-id-123');
        $account->setAppSecret('test-app-secret-456');
        $account->setValid(true);
        $account->setCreatedBy('admin');
        $account->setUpdatedBy('admin');

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
        $client = $this->createAuthenticatedClient();

        // Create test account
        $em = self::getEntityManager();
        $account = new Account();
        $account->setName('测试公众号');
        $account->setAppId('test-app-id-123');
        $account->setAppSecret('test-app-secret-456');
        $account->setValid(true);
        $account->setCreatedBy('admin');
        $account->setUpdatedBy('admin');

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
        $client = $this->createAuthenticatedClient();

        // 创建测试数据
        $em = self::getEntityManager();
        $existingAccount = $em->getRepository(Account::class)->findOneBy(['appId' => 'test-app-id-123']);
        if (null === $existingAccount) {
            $account = new Account();
            $account->setName('测试公众号');
            $account->setAppId('test-app-id-123');
            $account->setAppSecret('test-app-secret-456');
            $account->setValid(true);
            $account->setCreatedBy('admin');
            $account->setUpdatedBy('admin');

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
     * 创建测试用的Account实体
     * 用于确保testIndexRowActionLinksShouldNotReturn500有数据可测试
     */
    private function createTestAccount(?KernelBrowser $client = null): Account
    {
        if ($client === null) {
            $client = $this->createAuthenticatedClient();
        }

        $em = self::getEntityManager();

        // 清理现有的Account实体，确保我们的测试数据是ID=1
        $existingAccounts = $em->getRepository(Account::class)->findAll();
        foreach ($existingAccounts as $existingAccount) {
            $em->remove($existingAccount);
        }
        $em->flush();

        $account = new Account();
        $account->setName('测试公众号-IndexTest');
        $account->setAppId('test-index-app-id-123');
        $account->setAppSecret('test-index-app-secret-456');
        $account->setValid(true);
        $account->setCreatedBy('admin');
        $account->setUpdatedBy('admin');

        $em->persist($account);
        $em->flush();

        // 重新获取以确认ID
        $em->clear();
        $savedAccount = $em->getRepository(Account::class)->findOneBy(['appId' => 'test-index-app-id-123']);
        if ($savedAccount === null) {
            throw new \RuntimeException('Failed to create test account');
        }
        return $savedAccount;
    }

    /**
     * 该测试创建测试数据以确保基类的testIndexRowActionLinksShouldNotReturn500有数据可测试
     */
    public function testIndexPageShouldHaveData(): void
    {
        $client = $this->createAuthenticatedClient();
        $account = $this->createTestAccount();

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

    /**
     * 这个测试专门用来验证基类的testIndexRowActionLinksShouldNotReturn500能正常工作
     * 它创建数据并手动执行基类测试的逻辑
     */
    public function testIndexRowActionLinksShouldWork(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createTestAccount($client);

        // 确保客户端正确设置
        self::getClient($client);

        // 执行基类测试的逻辑
        $indexUrl = $this->generateAdminUrl(Action::INDEX);
        $crawler = $client->request('GET', $indexUrl);
        $this->assertTrue($client->getResponse()->isSuccessful(), 'Index page should be successful');

        // 收集每一行里的动作按钮（a 链接）
        $links = [];
        foreach ($crawler->filter('table tbody tr[data-id]') as $row) {
            $rowCrawler = new Crawler($row);
            foreach ($rowCrawler->filter('td.actions a[href]') as $a) {
                /** @var \DOMElement $a */
                $href = $a->getAttribute('href');
                if ($href === '') {
                    continue;
                }
                if (str_starts_with($href, 'javascript:') || '#' === $href) {
                    continue;
                }

                // 跳过需要 POST 的删除类动作（避免 Method Not Allowed）
                $aCrawler = new Crawler($a);
                $actionNameAttr = strtolower((string) ($aCrawler->attr('data-action-name') ?? $aCrawler->attr('data-action') ?? ''));
                $text = strtolower(trim($a->textContent ?? ''));
                $hrefLower = strtolower($href);
                $isDelete = (
                    'delete' === $actionNameAttr
                    || str_contains($text, 'delete')
                    || 1 === preg_match('#/delete(?:$|[/?\#])#i', $hrefLower)
                    || 1 === preg_match('/(^|[?&])crudAction=delete\b/i', $hrefLower)
                );
                if ($isDelete) {
                    continue; // 删除操作需要POST与CSRF，跳过
                }

                $links[] = $href;
            }
        }

        $links = array_values(array_unique($links, SORT_STRING));
        if (empty($links)) {
            $this->markTestSkipped('没有动作链接，跳过');
        }

        // 逐个请求，跟随重定向并确保最终不是 500
        foreach ($links as $href) {
            $client->request('GET', $href);

            // 跟随最多3次重定向，覆盖常见动作跳转链
            $hops = 0;
            while ($client->getResponse()->isRedirection() && $hops < 3) {
                $client->followRedirect();
                ++$hops;
            }

            $status = $client->getResponse()->getStatusCode();
            $this->assertLessThan(500, $status, sprintf('链接 %s 最终返回了 %d', $href, $status));
        }
    }
}
