<?php

// src/UKMNorge/DesignBundle/Twig/AppExtension.php

namespace App\UKMNorge\DesignBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use UKMNorge\Twig\Definitions\Filters;
use UKMNorge\Twig\Definitions\Functions;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        require_once('UKM/Autoloader.php');
        $filters = [
            new TwigFilter('UKMpath', [static::class,'UKMpath'])
        ];
        
        $definitionClass = new Filters();
        foreach( get_class_methods( $definitionClass ) as $function ) {
            $filters[] = new TwigFilter($function, [$definitionClass,$function]);
        }

        return $filters;
    }

    public function getFunctions()
    {
        require_once('UKM/Autoloader.php');

        $functions = [];

        $definitionClass = new Functions();
        foreach( get_class_methods( $definitionClass ) as $function ) {
            $functions[] = new TwigFunction($function, [$definitionClass, $function]);
        }
        return $functions;
    }

    public static function UKMpath($path)
    {
        return str_replace(array('UKMDesign/', 'UKMDesignBundle', ':'), array('@UKMDesign/', '', '/'), $path);
    }
}
