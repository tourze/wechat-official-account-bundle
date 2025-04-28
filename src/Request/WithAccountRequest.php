<?php

namespace WechatOfficialAccountBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use WechatOfficialAccountBundle\Entity\AccessTokenAware;
use WechatOfficialAccountBundle\Entity\Account;

abstract class WithAccountRequest extends ApiRequest
{
    private Account|AccessTokenAware $account;

    public function getAccount(): Account|AccessTokenAware
    {
        return $this->account;
    }

    public function setAccount(Account|AccessTokenAware $account): void
    {
        $this->account = $account;
    }
}
