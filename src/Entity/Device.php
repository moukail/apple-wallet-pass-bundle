<?php

namespace Moukail\AppleWalletPassBundle\Entity;

/**
 * Class Device
 * @package Moukail\AppleWalletPassBundle\Entity
 */
class Device
{
    const OS_ANDROID = 'android';
    const OS_IOS = 'ios';

    private int $id;

    private PassInterface $pass;

    private string $type;

    private string $os;

    private string $deviceLibraryIdentifier;

    private string $pushToken;

    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return PassInterface
     */
    public function getPass(): PassInterface
    {
        return $this->pass;
    }

    /**
     * @param PassInterface $pass
     *
     * @return $this
     */
    public function setPass(PassInterface $pass): self
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * @param string $os
     *
     * @return $this
     */
    public function setOs(string $os): self
    {
        $this->os = $os;

        return $this;
    }

    /**
     * @return string
     */
    public function getPushToken(): string
    {
        return $this->pushToken;
    }

    /**
     * @param string $pushToken
     *
     * @return $this
     */
    public function setPushToken(string $pushToken): self
    {
        $this->pushToken = $pushToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceLibraryIdentifier(): string
    {
        return $this->deviceLibraryIdentifier;
    }

    /**
     * @param string $deviceLibraryIdentifier
     *
     * @return $this
     */
    public function setDeviceLibraryIdentifier(string $deviceLibraryIdentifier): self
    {
        $this->deviceLibraryIdentifier = $deviceLibraryIdentifier;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
