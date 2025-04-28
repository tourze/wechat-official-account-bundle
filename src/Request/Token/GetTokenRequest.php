<?php

namespace WechatOfficialAccountBundle\Request\Token;

use HttpClientBundle\Request\ApiRequest;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @see https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
 */
class GetTokenRequest extends ApiRequest
{
    /**
     * @var Account 请求关联的Account信息
     */
    private Account $account;

    private string $grantType = 'client_credential';

    /**
     * @var bool 默认使用 false。1. force_refresh = false 时为普通调用模式，access_token 有效期内重复调用该接口不会更新 access_token；2. 当force_refresh = true 时为强制刷新模式，会导致上次获取的 access_token 失效，并返回新的 access_token
     */
    private bool $forceRefresh = false;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/cgi-bin/token';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'grant_type' => $this->getGrantType(),
                'appid' => $this->getAccount()->getAppId(),
                'secret' => $this->getAccount()->getAppSecret(),
            ],
        ];
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function setGrantType(string $grantType): void
    {
        $this->grantType = $grantType;
    }

    public function isForceRefresh(): bool
    {
        return $this->forceRefresh;
    }

    public function setForceRefresh(bool $forceRefresh): void
    {
        $this->forceRefresh = $forceRefresh;
    }
}
