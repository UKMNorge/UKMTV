<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use UKMNorge\Filmer\UKMTV\Filmer;

require_once('UKM/Autoloader.php');
require_once('UKMconfig.inc.php');

class TagController extends AbstractController
{
    /**
     * List ut de årene vi har filmer for
     *
     * @return Response ?
     */
    public function tag(String $key, Int $id) 
    {
        return $this->render(
            'Tag/Filmer.html.twig',
            [
                'filmer' => Filmer::getByTag( $key, $id ),
                'ukmHostname' => UKM_HOSTNAME
            ]
        );
    }
}
