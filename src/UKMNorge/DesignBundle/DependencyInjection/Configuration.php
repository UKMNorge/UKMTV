<?php

namespace App\UKMNorge\DesignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder() {

        $treebuilder = new TreeBuilder('ukm_design');
        
        die('diiiiie');

        return $treebuilder;
    }
}