<?php

namespace WechatOfficialAccountBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
    private int $id = 0;

    #[Assert\NotBlank(message: '名称不能为空')]
    #[Assert\Length(max: 32, maxMessage: '名称长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[Assert\NotBlank(message: 'AppID不能为空')]
    #[Assert\Length(max: 64, maxMessage: 'AppID长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => 'AppID'])]
    private ?string $appId = null;

    #[Assert\NotBlank(message: 'AppSecret不能为空')]
    #[Assert\Length(max: 120, maxMessage: 'AppSecret长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => 'AppSecret'])]
    private ?string $appSecret = null;

    #[Assert\Length(max: 128, maxMessage: 'TOKEN长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '加解密TOKEN'])]
    private ?string $token = null;

    #[Assert\Length(max: 100, maxMessage: 'EncodingAESKey长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => 'EncodingAESKey'])]
    private ?string $encodingAesKey = null;

    /**
     * @var Collection<int, CallbackIP>
     */
    #[ORM\OneToMany(targetEntity: CallbackIP::class, mappedBy: 'account', cascade: ['persist'], orphanRemoval: true)]
    private Collection $callbackIPs;

    #[Assert\Length(max: 300, maxMessage: 'AccessToken长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(length: 300, nullable: true, options: ['comment' => 'AccessToken'])]
    private ?string $accessToken = null;

    #[Assert\Type(type: '\DateTimeInterface', message: 'AccessToken过期时间必须是有效的日期时间')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'AccessToken过期时间'])]
    private ?\DateTimeInterface $accessTokenExpireTime = null;

    #[Assert\Length(max: 64, maxMessage: '关联开放平台应用ID长度不能超过 {{ limit }} 个字符')]
    #[ORM\Column(length: 64, nullable: true, options: ['comment' => '关联开放平台应用'])]
    private ?string $componentAppId = null;

    #[Assert\Type(type: 'bool', message: '有效性必须是布尔值')]
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
        if (0 === $this->getId()) {
            return '';
        }

        return "{$this->getName()}({$this->getAppId()})";
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getAppSecret(): ?string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection<int, CallbackIP>
     */
    public function getCallbackIPs(): Collection
    {
        return $this->callbackIPs;
    }

    public function addCallbackIP(CallbackIP $callbackIP): void
    {
        if (!$this->callbackIPs->contains($callbackIP)) {
            $this->callbackIPs->add($callbackIP);
            $callbackIP->setAccount($this);
        }
    }

    public function removeCallbackIP(CallbackIP $callbackIP): void
    {
        if ($this->callbackIPs->removeElement($callbackIP)) {
            // set the owning side to null (unless already changed)
            if ($callbackIP->getAccount() === $this) {
                $callbackIP->setAccount(null);
            }
        }
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getEncodingAesKey(): ?string
    {
        return $this->encodingAesKey;
    }

    public function setEncodingAesKey(?string $encodingAesKey): void
    {
        $this->encodingAesKey = $encodingAesKey;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessTokenExpireTime(): ?\DateTimeInterface
    {
        return $this->accessTokenExpireTime;
    }

    public function setAccessTokenExpireTime(?\DateTimeInterface $accessTokenExpireTime): void
    {
        $this->accessTokenExpireTime = $accessTokenExpireTime;
    }

    public function getComponentAppId(): ?string
    {
        return $this->componentAppId;
    }

    public function setComponentAppId(?string $componentAppId): void
    {
        $this->componentAppId = $componentAppId;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
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
