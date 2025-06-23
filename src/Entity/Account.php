<?php

namespace WechatOfficialAccountBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\WechatOfficialAccountContracts\OfficialAccountInterface;
use WechatOfficialAccountBundle\Repository\AccountRepository;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(name: 'wechat_official_account_account', options: ['comment' => '公众号账号'])]
class Account implements \Stringable, AccessTokenAware, LockEntity, OfficialAccountInterface
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => 'AppID'])]
    private ?string $appId = null;

    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => 'AppSecret'])]
    private ?string $appSecret = null;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '加解密TOKEN'])]
    private ?string $token = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => 'EncodingAESKey'])]
    private ?string $encodingAesKey = null;

    /**
     * @var Collection<CallbackIP>
     */
    #[ORM\OneToMany(targetEntity: CallbackIP::class, mappedBy: 'account', cascade: ['persist'], orphanRemoval: true)]
    private Collection $callbackIPs;

    #[ORM\Column(length: 300, nullable: true, options: ['comment' => 'AccessToken'])]
    private ?string $accessToken = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'AccessToken过期时间'])]
    private ?\DateTimeInterface $accessTokenExpireTime = null;

    #[ORM\Column(length: 64, nullable: true, options: ['comment' => '关联开放平台应用'])]
    private ?string $componentAppId = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;


    public function __construct()
    {
        $this->callbackIPs = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->getId() === null || $this->getId() === 0) {
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

    public function getAccessTokenKeyName(): string
    {
        return 'access_token';
    }

    public function retrieveLockResource(): string
    {
        return "WechatOfficialAccountBundle_refreshAccessToken_{$this->getAppId()}_{$this->getAppSecret()}";
    }
}
