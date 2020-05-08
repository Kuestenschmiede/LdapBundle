<?php


namespace con4gis\AuthBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class con4gisAuthExtension extends Extension
{

//    public function getAlias()
//    {
//        return "con4gis_auth";
//    }

    private $files = [
        "listener.yml"
    ];

    /**
     * {@inheritdoc}
     */
    public function load(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        foreach ($this->files as $file) {
            $loader->load($file);
        }
    }
}