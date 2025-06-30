<?php

declare(strict_types=1);

namespace WechatOfficialAccountBundle\Exception;

class InvalidAccountTypeException extends \InvalidArgumentException
{
    public static function onlyAccountInstancesSupported(): self
    {
        return new self('Only Account instances can refresh access token');
    }
}