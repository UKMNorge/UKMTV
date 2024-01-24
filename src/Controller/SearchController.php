<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use UKMNorge\Filmer\UKMTV\Filmer;

require_once('UKM/Autoloader.php');
require_once('UKMconfig.inc.php');

class SearchController extends AbstractController
{
    /**
     * List ut de årene vi har filmer for
     *
     * @return Response ?
     */
    public function treff(Request $request) 
    {
        $sok = $request->request->get('doSearchFor');
        return $this->render(
            'Sok/Resultat.html.twig',
            [
                'soker' => $sok,
                'filmer' => Filmer::getBySearchString( $sok ),
                'ukmHostname' => UKM_HOSTNAME
            ]
        );
    }

    public function home() {
        return $this->render('Sok/Home.html.twig', ['ukmHostname' => UKM_HOSTNAME]);
    }
}
