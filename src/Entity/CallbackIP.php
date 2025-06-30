<?php

namespace WechatOfficialAccountBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use WechatOfficialAccountBundle\Repository\CallbackIPRepository;

#[ORM\Entity(repositoryClass: CallbackIPRepository::class)]
#[ORM\Table(name: 'wechat_official_account_callback_ip', options: ['comment' => '服务端回调IP'])]
#[ORM\UniqueConstraint(name: 'wechat_official_account_callback_ip_idx_uniq', columns: ['account_id', 'ip'])]
class CallbackIP implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'callbackIPs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => 'IP'])]
    private ?string $ip = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;


    public function __toString(): string
    {
        if ($this->getId() === null || $this->getId() === '') {
            return '';
        }

        return $this->getIp();
    }


    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        if ($account !== null) {
            $this->account = $account;
        }

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }
}

