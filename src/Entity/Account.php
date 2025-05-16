<?php

namespace WechatOfficialAccountBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\WechatOfficialAccountContracts\OfficialAccountInterface;
use WechatOfficialAccountBundle\Repository\AccountRepository;

#[AsPermission(title: '公众号账号')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(name: 'wechat_official_account_account', options: ['comment' => '公众号账号'])]
class Account implements \Stringable, AccessTokenAware, LockEntity, OfficialAccountInterface
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[FormField]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[FormField(span: 10)]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => 'AppID'])]
    private ?string $appId = null;

    #[FormField(span: 14)]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => 'AppSecret'])]
    private ?string $appSecret = null;

    #[FormField]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '加解密TOKEN'])]
    private ?string $token = null;

    #[FormField]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => 'EncodingAESKey'])]
    private ?string $encodingAesKey = null;

    /**
     * @var Collection<CallbackIP>
     */
    #[FormField(title: '服务端白名单')]
    #[ListColumn(title: '服务端白名单')]
    #[ORM\OneToMany(targetEntity: CallbackIP::class, mappedBy: 'account', cascade: ['persist'], orphanRemoval: true)]
    private Collection $callbackIPs;

    #[ORM\Column(length: 300, nullable: true, options: ['comment' => 'AccessToken'])]
    private ?string $accessToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'AccessToken过期时间'])]
    private ?\DateTimeInterface $accessTokenExpireTime = null;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 64, nullable: true, options: ['comment' => '关联开放平台应用'])]
    private ?string $componentAppId = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function __construct()
    {
        $this->callbackIPs = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getName()}({$this->getAppId()})";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    public function getAppSecret(): ?string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): self
    {
        $this->appSecret = $appSecret;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, CallbackIP>
     */
    public function getCallbackIPs(): Collection
    {
        return $this->callbackIPs;
    }

    public function addCallbackIP(CallbackIP $callbackIP): self
    {
        if (!$this->callbackIPs->contains($callbackIP)) {
            $this->callbackIPs[] = $callbackIP;
            $callbackIP->setAccount($this);
        }

        return $this;
    }

    public function removeCallbackIP(CallbackIP $callbackIP): self
    {
        if ($this->callbackIPs->removeElement($callbackIP)) {
            // set the owning side to null (unless already changed)
            if ($callbackIP->getAccount() === $this) {
                $callbackIP->setAccount(null);
            }
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getEncodingAesKey(): ?string
    {
        return $this->encodingAesKey;
    }

    public function setEncodingAesKey(?string $encodingAesKey): self
    {
        $this->encodingAesKey = $encodingAesKey;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessTokenExpireTime(): ?\DateTimeInterface
    {
        return $this->accessTokenExpireTime;
    }

    public function setAccessTokenExpireTime(?\DateTimeInterface $accessTokenExpireTime): static
    {
        $this->accessTokenExpireTime = $accessTokenExpireTime;

        return $this;
    }

    public function getComponentAppId(): ?string
    {
        return $this->componentAppId;
    }

    public function setComponentAppId(?string $componentAppId): static
    {
        $this->componentAppId = $componentAppId;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function getAccessTokenKeyName(): string
    {
        return 'access_token';
    }

    public function retrieveLockResource(): string
    {
        return "WechatOfficialAccountBundle_refreshAccessToken_{$this->getAppId()}_{$this->getAppSecret()}";
    }
}
