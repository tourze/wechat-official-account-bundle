<?php

namespace WechatOfficialAccountBundle\Entity;

interface AccessTokenAware
{
    public function getAccessToken(): ?string;

    public function getAccessTokenKeyName(): string;
}
