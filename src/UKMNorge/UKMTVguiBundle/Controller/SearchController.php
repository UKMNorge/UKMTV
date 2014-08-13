<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use stdClass;
use tv;
use tv_files;
use monstring;

class SearchController extends Controller
{
    public function indexAction()
    {
        require_once('UKM/tv_files.class.php');
        require_once('UKM/tv.class.php');
        require_once('UKM/monstring.class.php');
     
        return $this->render('UKMNtvguiBundle:Search:index.html.twig', array());
    }
    
    public function doSearchAction() {
        $searchstring = $this->get('request')->request->get('doSearchFor');
        
        return $this->redirect( $this->get('router')->generate('ukmn_tvgui_searchfor', array('doSearchFor' => $searchstring ) ) );
    }
    
    public function resultAction( $doSearchFor ) {
    
        require_once('UKM/tv_files.class.php');
        require_once('UKM/tv.class.php');

        $tv_files = new tv_files('search', $doSearchFor );
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[] = $file;
            
        }
        return $this->render('UKMNtvguiBundle:Search:results.html.twig', array('doSearchFor' => $doSearchFor, 'list' => $files ));
    }
}
