# 微信公众号 Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg?style=flat-square)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-official-account-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-bundle)
[![Build Status](https://img.shields.io/travis/tourze/wechat-official-account-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/wechat-official-account-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/wechat-official-account-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/wechat-official-account-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/wechat-official-account-bundle/master.svg?style=flat-square)](https://codecov.io/gh/tourze/wechat-official-account-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-official-account-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-bundle)

一个全面的 Symfony Bundle，用于微信公众号 API 集成。该 Bundle 提供了管理微信公众号操作的核心功能，
包括访问令牌管理、账号配置和 API 请求，支持自动令牌刷新和全面的错误处理机制。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [快速开始](#快速开始)
- [配置](#配置)
- [高级用法](#高级用法)
- [API 参考](#api-参考)
- [测试](#测试)
- [贡献](#贡献)
- [安全](#安全)
- [许可证](#许可证)

## 功能特性

- **账号管理**: 存储和管理多个微信公众号配置
- **访问令牌处理**: 自动访问令牌刷新和缓存，支持过期时间管理
- **API 客户端**: 预配置的微信公众号 API 请求 HTTP 客户端
- **回调 IP 管理**: 管理和验证回调 IP 地址
- **Doctrine 集成**: 完整的 ORM 支持，包含仓库和实体
- **Symfony Bundle**: 原生 Symfony 集成，支持依赖注入
- **错误处理**: 全面的错误处理和重试机制

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 3.0 或更高版本

## 安装

```bash
composer require tourze/wechat-official-account-bundle
```

## 快速开始

### 1. Bundle 注册

Bundle 应该会自动注册。如果没有，请将其添加到您的 `bundles.php`：

```php
<?php
// config/bundles.php

return [
    // ... 其他 bundles
    WechatOfficialAccountBundle\WechatOfficialAccountBundle::class => ['all' => true],
];
```

### 2. 数据库迁移

创建并运行数据库迁移：

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 3. 创建微信公众号

```php
<?php

use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

// 创建账号实体
$account = new Account();
$account->setName('我的微信账号');
$account->setAppId('your-app-id');
$account->setAppSecret('your-app-secret');
$account->setToken('your-token'); // 可选
$account->setEncodingAesKey('your-encoding-aes-key'); // 可选
$account->setValid(true);

// 持久化到数据库
$entityManager->persist($account);
$entityManager->flush();
```

### 4. 发起 API 请求

```php
<?php

use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountBundle\Request\Token\GetTokenRequest;

// 注入客户端服务
public function __construct(
    private OfficialAccountClient $client
) {}

// 获取访问令牌
$tokenRequest = new GetTokenRequest();
$tokenRequest->setAccount($account);

$response = $this->client->request($tokenRequest);
// 响应包含: ['access_token' => '...', 'expires_in' => 7200]
```

## 配置

### 访问令牌管理

Bundle 自动处理访问令牌刷新：

- 令牌缓存在数据库中
- 过期时自动刷新
- 使用锁的线程安全令牌刷新
- 可配置的令牌过期缓冲区（默认：10秒）

### 账号实体功能

- **时间戳**: 自动创建和更新时间戳
- **可归责**: 跟踪创建/更新的用户
- **索引**: 优化的数据库查询
- **验证**: 内置验证规则

### 回调 IP 管理

```php
<?php

use WechatOfficialAccountBundle\Entity\CallbackIP;

$callbackIP = new CallbackIP();
$callbackIP->setIp('127.0.0.1');
$callbackIP->setRemark('开发服务器');
$callbackIP->setAccount($account);

$account->addCallbackIP($callbackIP);
```

## 高级用法

### 自定义 API 请求

通过继承 `WithAccountRequest` 创建自定义请求：

```php
<?php

use WechatOfficialAccountBundle\Request\WithAccountRequest;

class GetUserInfoRequest extends WithAccountRequest
{
    private string $openid;
    
    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/user/info';
    }
    
    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'openid' => $this->openid,
                'lang' => 'zh_CN',
            ],
        ];
    }
    
    public function getRequestMethod(): ?string
    {
        return 'GET';
    }
    
    public function setOpenid(string $openid): void
    {
        $this->openid = $openid;
    }
}
```

### 错误处理

Bundle 提供全面的错误处理：

```php
<?php

use WechatOfficialAccountBundle\Exception\WechatApiException;

try {
    $response = $this->client->request($request);
} catch (WechatApiException $e) {
    // 处理微信 API 特定错误
    $errorCode = $e->getErrorCode();
    $errorMessage = $e->getErrorMessage();
}
```

### 令牌刷新策略

配置令牌刷新行为：

```php
<?php

// Bundle 在以下情况下自动刷新令牌：
// 1. 令牌为 null 或空
// 2. 令牌已过期（带 10 秒缓冲区）
// 3. API 返回令牌相关错误

$tokenRequest = new GetTokenRequest();
$tokenRequest->setAccount($account);
$tokenRequest->setForceRefresh(true); // 强制令牌刷新
```

## API 参考

### 服务

- `OfficialAccountClient`: 微信 API 的主要 HTTP 客户端
- `AccountRepository`: 账号管理仓库
- `CallbackIPRepository`: 回调 IP 管理仓库

### 实体

- `Account`: 微信公众号配置
- `CallbackIP`: 回调 IP 地址管理
- `AccessTokenAware`: 访问令牌感知实体接口

### 请求

- `GetTokenRequest`: 获取访问令牌
- `GetStableTokenRequest`: 获取稳定访问令牌
- `WithAccountRequest`: 账号感知请求的基类

### 异常

- `WechatApiException`: 微信 API 相关异常
- `TokenRefreshException`: 令牌刷新失败异常

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/wechat-official-account-bundle/tests
```

带覆盖率运行：

```bash
./vendor/bin/phpunit packages/wechat-official-account-bundle/tests --coverage-html coverage
```

## 贡献

详情请参见 [CONTRIBUTING.md](CONTRIBUTING.md)。

## 安全

如果您发现任何安全相关问题，请发送邮件至 security@tourze.com，而不是使用问题跟踪器。

## 许可证

MIT 许可证 (MIT)。详情请参见 [许可证文件](LICENSE)。