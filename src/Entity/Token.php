<?php

namespace Moukail\AppleWalletPassBundle\Entity;

/**
 * Class Token
 * @package Moukail\AppleWalletPassBundle\Entity
 */
class Token
{
    private int $id;

    private PassInterface $pass;

    private string $token;

    private \DateTimeInterface $createdAt;

    private \DateTimeInterface $expiresAt;

    public function __construct(PassInterface $pass, \DateTimeInterface $expiresAt, string $token)
    {
        $this->pass = $pass;
        $this->token = $token;
        $this->createdAt = new \DateTimeImmutable('now');
        $this->expiresAt = $expiresAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPass(): PassInterface
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= \time();
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}
