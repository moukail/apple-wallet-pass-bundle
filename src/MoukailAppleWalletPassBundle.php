<?php

namespace Moukail\AppleWalletPassBundle;

use Moukail\AppleWalletPassBundle\DependencyInjection\MoukailAppleWalletPassExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MoukailAppleWalletPassBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new MoukailAppleWalletPassExtension();
        }

        return $this->extension;
    }
}
