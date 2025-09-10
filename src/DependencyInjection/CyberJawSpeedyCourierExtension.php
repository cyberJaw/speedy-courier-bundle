<?php
namespace Cyberjaw\SpeedyCourierBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class SpeedyCourierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('speedy_courier.base_uri', $config['base_uri']);
        $container->setParameter('speedy_courier.username', $config['username']);
        $container->setParameter('speedy_courier.password', $config['password']);
        $container->setParameter('speedy_courier.timeout',  $config['timeout']);
        $container->setParameter('speedy_courier.sandbox',  $config['sandbox']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');
    }

    public function getAlias(): string { return 'speedy_courier'; }
}
