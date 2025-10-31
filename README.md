# WeChat Official Account Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg?style=flat-square)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-official-account-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-bundle)
[![Build Status](https://img.shields.io/travis/tourze/wechat-official-account-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/wechat-official-account-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/wechat-official-account-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/wechat-official-account-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/wechat-official-account-bundle/master.svg?style=flat-square)](https://codecov.io/gh/tourze/wechat-official-account-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-official-account-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-official-account-bundle)

A comprehensive Symfony bundle for WeChat Official Account API integration. 
This bundle provides essential functionalities for managing WeChat Official 
Account operations including access token management, account configuration, 
and API requests with automatic token refresh and comprehensive error handling.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Advanced Usage](#advanced-usage)
- [API Reference](#api-reference)
- [Testing](#testing)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Features

- **Account Management**: Store and manage multiple WeChat Official Account 
  configurations
- **Access Token Handling**: Automatic access token refresh and caching with 
  expiration management
- **API Client**: Pre-configured HTTP client for WeChat Official Account API 
  requests
- **Callback IP Management**: Manage and validate callback IP addresses
- **Doctrine Integration**: Full ORM support with repositories and entities
- **Symfony Bundle**: Native Symfony integration with dependency injection
- **Error Handling**: Comprehensive error handling and retry mechanisms

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 3.0 or higher

## Installation

```bash
composer require tourze/wechat-official-account-bundle
```

## Quick Start

### 1. Bundle Registration

The bundle should be automatically registered. If not, add it to your 
`bundles.php`:

```php
<?php
// config/bundles.php

return [
    // ... other bundles
    WechatOfficialAccountBundle\WechatOfficialAccountBundle::class => ['all' => true],
];
```

### 2. Database Migration

Create and run the database migration:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 3. Create WeChat Official Account

```php
<?php

use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

// Create account entity
$account = new Account();
$account->setName('My WeChat Account');
$account->setAppId('your-app-id');
$account->setAppSecret('your-app-secret');
$account->setToken('your-token'); // Optional
$account->setEncodingAesKey('your-encoding-aes-key'); // Optional
$account->setValid(true);

// Persist to database
$entityManager->persist($account);
$entityManager->flush();
```

### 4. Making API Requests

```php
<?php

use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountBundle\Request\Token\GetTokenRequest;

// Inject the client service
public function __construct(
    private OfficialAccountClient $client
) {}

// Get access token
$tokenRequest = new GetTokenRequest();
$tokenRequest->setAccount($account);

$response = $this->client->request($tokenRequest);
// Response contains: ['access_token' => '...', 'expires_in' => 7200]
```

## Configuration

### Access Token Management

The bundle automatically handles access token refresh:

- Tokens are cached in the database
- Automatic refresh when expired
- Thread-safe token refresh using locks
- Configurable token expiration buffer (default: 10 seconds)

### Account Entity Features

- **Timestamps**: Automatic creation and update timestamps
- **Blameable**: Track created/updated by user
- **Indexing**: Optimized database queries
- **Validation**: Built-in validation rules

### Callback IP Management

```php
<?php

use WechatOfficialAccountBundle\Entity\CallbackIP;

$callbackIP = new CallbackIP();
$callbackIP->setIp('127.0.0.1');
$callbackIP->setDescription('Development server');
$callbackIP->setAccount($account);

$account->addCallbackIP($callbackIP);
```

## Advanced Usage

### Custom API Requests

Create custom requests by extending `WithAccountRequest`:

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

### Error Handling

The bundle provides comprehensive error handling:

```php
<?php

use WechatOfficialAccountBundle\Exception\WechatApiException;

try {
    $response = $this->client->request($request);
} catch (WechatApiException $e) {
    // Handle WeChat API specific errors
    $errorCode = $e->getErrorCode();
    $errorMessage = $e->getErrorMessage();
}
```

### Token Refresh Strategy

Configure token refresh behavior:

```php
<?php

// The bundle automatically refreshes tokens when:
// 1. Token is null or empty
// 2. Token has expired (with 10-second buffer)
// 3. API returns token-related error

$tokenRequest = new GetTokenRequest();
$tokenRequest->setAccount($account);
$tokenRequest->setForceRefresh(true); // Force token refresh
```

## API Reference

### Services

- `OfficialAccountClient`: Main HTTP client for WeChat API
- `AccountRepository`: Repository for account management
- `CallbackIPRepository`: Repository for callback IP management

### Entities

- `Account`: WeChat Official Account configuration
- `CallbackIP`: Callback IP address management
- `AccessTokenAware`: Interface for access token aware entities

### Requests

- `GetTokenRequest`: Get access token
- `GetStableTokenRequest`: Get stable access token
- `WithAccountRequest`: Base class for account-aware requests

### Exceptions

- `WechatApiException`: WeChat API related exceptions
- `TokenRefreshException`: Token refresh failures

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/wechat-official-account-bundle/tests
```

Run with coverage:

```bash
./vendor/bin/phpunit packages/wechat-official-account-bundle/tests --coverage-html coverage
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@tourze.com 
instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.