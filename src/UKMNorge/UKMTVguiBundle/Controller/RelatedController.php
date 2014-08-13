<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use stdClass;
use tv;
use tv_files;
use monstring;
use innslag;
use person;

class RelatedController extends Controller
{
    public function bandAction($id)
    {
        require_once('UKM/tv_files.class.php');
        require_once('UKM/tv.class.php');
        require_once('UKM/innslag.class.php');

        $tv_files = new tv_files('band', $id );
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[] = $file;
            
        }
        
        $innslag = new innslag( $id );
        $band = new stdClass();
        $band->name = $innslag->g('b_name');
        
        return $this->render('UKMNtvguiBundle:Related:band.html.twig', array('band' => $band, 'list' => $files ));
    }
   
   
    public function personAction($id)
    {
        require_once('UKM/tv_files.class.php');
        require_once('UKM/tv.class.php');
        require_once('UKM/person.class.php');

        $tv_files = new tv_files('person', $id );
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[] = $file;
            
        }
        
        $p = new person( $id );
        $person = new stdClass();
        $person->name = $p->g('name');
        
        return $this->render('UKMNtvguiBundle:Related:person.html.twig', array('person' => $person, 'list' => $files ));
    } 
}
