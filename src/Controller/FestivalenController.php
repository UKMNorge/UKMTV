<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Filmer;

require_once('UKM/Autoloader.php');

class FestivalenController extends AbstractController{
    public function years() {

        $sesonger = [];
        for( $sesong = 2009; $sesong < intval(date('Y')+1); $sesong++) {
            if( Filmer::harTagsFilmer('arrangement_type', 3, 'sesong', $sesong) ) {
                $sesonger[] = $sesong;
            }
        }

        return $this->render('Festivalen/Years.html.twig', ['years' => $sesonger]);
    }

    public function year( Int $year ) {
        
        $filmer = Filmer::getByTags('arrangement_type', 3, 'sesong', $year);

        return $this->render('Filmer/Filmer.html.twig', ['filmer' => $filmer]);
    }
}