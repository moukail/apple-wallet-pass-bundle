<?php

namespace Moukail\AppleWalletPassBundle;

use Moukail\AppleWalletPassBundle\Entity\PassInterface;

interface PassServiceInterface
{
    public function makeWalletPass(PassInterface $pass);
}