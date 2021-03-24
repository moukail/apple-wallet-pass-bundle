<?php

namespace App\Message;

class ApplePassPushNotification
{
    private $pushToken;

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
