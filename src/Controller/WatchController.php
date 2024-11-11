<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Filmer;

require_once('UKM/Autoloader.php');
require_once('UKMconfig.inc.php');



class WatchController extends AbstractController
{
    /**
     * Se en film i UKM-TV i en side med innslag info og filmen
     *
     * @return Response ?
     */
    public function watch(String $id) 
    {
        $film = Filmer::getByCFId($id);
        
        return $this->render('Watch/Watch.html.twig', ['film' => $film, 'ukmHostname' => UKM_HOSTNAME]);
    }
}
