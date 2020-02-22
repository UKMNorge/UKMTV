<?php

namespace App\UKMNorge\DesignBundle\DependencyInjection;
namespace App\UKMNorge\UKMDesign\Services;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
#use Symfony\Component\Routing\Loader\YamlFileLoader;

class DesignBundleExtension extends Extension {
    
    public function load( array $configs, ContainerBuilder $container) {

        $configuration = new Configuration();
        #$loader = new YamlFileLoader(new FileLocator)
        die('diiiiie');
    }
}