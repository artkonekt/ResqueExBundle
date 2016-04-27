<?php

namespace Konekt\ResqueExBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KonektResqueExExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('konekt_resqueex.resque.vendor_dir', $config['vendor_dir']);
        $container->setParameter('konekt_resqueex.resque.class', $config['class']);
        $container->setParameter('konekt_resqueex.resque.redis.host', $config['redis']['host']);
        $container->setParameter('konekt_resqueex.resque.redis.port', $config['redis']['port']);
        $container->setParameter('konekt_resqueex.resque.redis.database', $config['redis']['database']);

        if (!empty($config['prefix'])) {
            $container->setParameter('konekt_resqueex.prefix', $config['prefix']);
            $container->getDefinition('konekt_resqueex.resque')->addMethodCall('setPrefix', array($config['prefix']));
        }

        if (!empty($config['worker']['root_dir'])) {
            $container->setParameter('konekt_resqueex.worker.root_dir', $config['worker']['root_dir']);
        }

        if (!empty($config['auto_retry'])) {
            if (isset($config['auto_retry'][0])) {
                $container->getDefinition('konekt_resqueex.resque')->addMethodCall('setGlobalRetryStrategy', array($config['auto_retry'][0]));
            } else {
                if (isset($config['auto_retry']['default'])) {
                    $container->getDefinition('konekt_resqueex.resque')->addMethodCall('setGlobalRetryStrategy', array($config['auto_retry']['default']));
                    unset($config['auto_retry']['default']);
                }
                $container->getDefinition('konekt_resqueex.resque')->addMethodCall('setJobRetryStrategy', array($config['auto_retry']));
            }
        }
    }
}
