<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Filmer;
use UKMNorge\Filmer\UKMTV\Tags\Tag;
use UKMNorge\Filmer\UKMTV\Tags\Tags;

require_once('UKM/Autoloader.php');

class FestivalenController extends AbstractController
{
    /**
     * List ut de årene vi har filmer for
     *
     * @return Response ?
     */
    public function years()
    {
        $sesonger = [];
        for ($sesong = 2009; $sesong < intval(date('Y') + 1); $sesong++) {
            $tags = [
                new Tag('arrangement_type', Tags::getArrangementTypeId('land')),
                new Tag('sesong', $sesong)
            ];
            if (Filmer::harTagsFilmer($tags)) {
                $sesonger[] = $sesong;
            }
        }

        
        return $this->render('Festivalen/Years.html.twig', ['years' => $sesonger, 'ukmHostname' => UKM_HOSTNAME]);
    }

    /**
     * List ut alle filmer for et gitt år
     *
     * @param Int $year
     * @return Response ?
     */
    public function year(Int $year)
    {

        $filmer = Filmer::getByTags(
            [
                new Tag('arrangement_type', Tags::getArrangementTypeId('land')),
                new Tag('sesong', $year)
            ]
        );
        return $this->render('Festivalen/Filmer.html.twig', ['filmer' => $filmer]);
    }
}
