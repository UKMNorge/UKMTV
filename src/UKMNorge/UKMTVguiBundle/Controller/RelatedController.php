<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use stdClass;
use tv;
use tv_files;
use monstring;
use innslag;
use person;

class RelatedController extends Controller
{
    public function bandAction( Request $request, $id)
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
        
		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( $band->name .' @ UKM-TV' );
		$SEO->setDescription( 'Alle filmer av '. $band->name .' i UKM-TV');

        return $this->render('UKMNtvguiBundle:Related:band.html.twig', array('band' => $band, 'list' => $files ));
    }
   
   
    public function personAction( Request $request, $id)
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
        
		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( $person->name .' @ UKM-TV' );
		$SEO->setDescription( 'Alle filmer av '. $person->name .' i UKM-TV');

        return $this->render('UKMNtvguiBundle:Related:person.html.twig', array('person' => $person, 'list' => $files ));
    } 
}
