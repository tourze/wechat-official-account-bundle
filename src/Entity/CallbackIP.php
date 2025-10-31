<?php

namespace WechatOfficialAccountBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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

    #[Assert\NotBlank(message: 'IP地址不能为空')]
    #[Assert\Length(max: 20, maxMessage: 'IP地址长度不能超过 {{ limit }} 个字符')]
    #[Assert\Ip(message: '请输入有效的IP地址')]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => 'IP'])]
    private ?string $ip = null;

    #[Assert\Length(max: 100, maxMessage: '备注长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    public function __toString(): string
    {
        if (null === $this->getId() || '' === $this->getId()) {
            return '';
        }

        return $this->getIp() ?? '';
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): void
    {
        if (null !== $account) {
            $this->account = $account;
        }
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }
}
