<?php

namespace App\UKMNorge\DesignBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DesignBundleExtension extends Extension {
    
    public function load( array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        #$loader = new YamlFileLoader(new FileLocator)
        die('diiiiie');
    }
}