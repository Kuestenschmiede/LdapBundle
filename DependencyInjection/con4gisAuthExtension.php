<?php


namespace con4gis\AuthBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class con4gisAuthExtension extends Extension implements PrependExtensionInterface
{

//    public function getAlias()
//    {
//        return "con4gis_auth";
//    }

    public function prepend(ContainerBuilder $container)
    {
//        // get all bundles
//        $bundles = $container->getParameter('kernel.bundles');
//        // determine if AcmeGoodbyeBundle is registered
//            // disable AcmeGoodbyeBundle in bundles
//            $config = ['use_acme_goodbye' => false];
//            foreach ($container->getExtensions() as $name => $extension) {
//                switch ($name) {
//                    case 'acme_something':
//                    case 'con4gis_auth':
//                        // set use_acme_goodbye to false in the config of
//                        // acme_something and acme_other
//                        //
//                        // note that if the user manually configured
//                        // use_acme_goodbye to true in config/services.yaml
//                        // then the setting would in the end be true and not false
//                        $container->prependExtensionConfig($name, $config);
//                        break;
//                }
//            }
//
//        // process the configuration of AcmeHelloExtension
//        $configs = $container->getExtensionConfig($this->getAlias());
//        // use the Configuration class to generate a config array with
//        // the settings "acme_hello"
//        $config = $this->processConfiguration(new Configuration(), $configs);
//
//        // check if entity_manager_name is set in the "acme_hello" configuration
//        if (isset($config['entity_manager_name'])) {
//            // prepend the acme_something settings with the entity_manager_name
//            $config = ['entity_manager_name' => $config['entity_manager_name']];
//            $container->prependExtensionConfig('acme_something', $config);
//        }
    }

    private $files = [
        "listener.yml"
    ];

    /**
     * {@inheritdoc}
     */
    public function load(array $mergedConfig, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        foreach ($this->files as $file) {
            $loader->load($file);
        }
    }
}