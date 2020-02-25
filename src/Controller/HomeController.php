<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

require_once('UKM/Autoloader.php');

class HomeController extends AbstractController{
    public function index() {

        #var_dump($this);die();
        return $this->render('Front/Home.html.twig');
    }
}