<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Filmer;

require_once('UKM/Autoloader.php');

class HomeController extends AbstractController{
    public function index() {
        
        #var_dump($this);die();
        return $this->render(
            'Front/Home.html.twig',
            ['filmer' => Filmer::getLatest(50)->getAll()]
        );
    }
}