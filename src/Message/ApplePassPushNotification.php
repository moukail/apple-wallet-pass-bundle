<?php

namespace Moukail\AppleWalletPassBundle\Message;

class ApplePassPushNotification
{
    private string $pushToken;

    public function __construct(string $pushToken)
    {
        $this->pushToken = $pushToken;
    }

    /**
     * @return string
     */
    public function getPushToken(): string
    {
        return $this->pushToken;
    }
}
