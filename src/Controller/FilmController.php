<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Film;
use UKMNorge\Filmer\UKMTV\Filmer;
use UKMNorge\Filmer\UKMTV\Tags\Tag;
use UKMNorge\Filmer\UKMTV\Tags\Tags;

require_once('UKM/Autoloader.php');

class FilmController extends AbstractController
{
    /**
     * List ut de årene vi har filmer for
     *
     * @return Response ?
     */
    public function film( Int $id ) 
    {
        $film = Filmer::getById($id);

        return $this->render('Film/Film.html.twig', ['film' => $film, 'ukmHostname' => UKM_HOSTNAME]);
    }
}
