<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Filmer;
use UKMNorge\Filmer\UKMTV\Tags\Tag;
use UKMNorge\Filmer\UKMTV\Tags\Tags;

require_once('UKM/Autoloader.php');
require_once('UKMconfig.inc.php');

class InfoController extends AbstractController
{
    /**
     * List ut alle info-filmer
     *
     * @return Response ?
     */
    public function filmer()
    {

        $filmer = Filmer::getByTags(
            [
                new Tag('arrangement', 0),
                
            ]
        );
        return $this->render('Info/Filmer.html.twig', ['filmer' => $filmer, 'ukmHostname' => UKM_HOSTNAME]);
    }
}
