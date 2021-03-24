<?php

namespace Moukail\AppleWalletPassBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MoukailAppleWalletPassExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        //$loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__) .'/Resources/config'));
        //$loader->load('services.yml');

        //$container->setParameter('moukail_apple_wallet_notification.email_base_url', $config['email_base_url']);

        $helperDefinition = $container->getDefinition('moukail.apple_wallet_pass.device_controller');
        $helperDefinition->replaceArgument(0, new Reference($config['pass_repository']));
        $helperDefinition->replaceArgument(1, new Reference($config['pass_service']));
    }

    public function getAlias()
    {
        return 'moukail_apple_wallet_pass';
    }
}
