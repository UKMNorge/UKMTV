<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use stdClass;

class FestivalenController extends Controller
{
    public function indexAction( Request $request )
    {
        require_once('UKM/monstring.class.php');
        
        $all_years = array();
        
        $year_start = 2009;
        $year_stop = (int) date('Y')+3;
        
        for( $i = $year_start; $i < $year_stop; $i++ ) {
            $monstring = new \landsmonstring( $i );
            $monstring = $monstring->monstring_get();
            if( get_class( $monstring ) == 'monstring' && $monstring->g('pl_id') != false ) {
                $year = new stdClass();
                $year->year = $i;
                $year->title = $monstring->g('pl_name');
                $year->url = $this->get('router')->generate('ukmn_tvgui_festivalen_year', array('year'=>$i) );
                $all_years[] = $year;
            }
        }

		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( 'UKM-festivalen i UKM-TV' );
		$SEO->setDescription( 'UKM-filmer fra UKM-festivalen 2009 - '. date("Y") );

        return $this->render('UKMNtvguiBundle:Festivalen:index.html.twig', array( 'years' => $all_years ));
    }
    
    public function yearAction( Request $request, $year ) {
    
        require_once('UKM/monstring.class.php');
        $monstring = new \landsmonstring( $year );
        $monstring = $monstring->monstring_get();
        
        if( $monstring->g('pl_id') == false ) {
            throw $this->createNotFoundException('Beklager, vi finner ikke UKM-festivalen '. $year .'. Sikker på at du har skrevet inn riktig URL?');
        }
        
        require_once('UKM/tv_files.class.php');
        $tv_files = new \tv_files('place', $monstring->get('pl_id') );
        $files = array();
        
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $category = $file->set;         
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_festivalen_film', array('year'=>$file->tag('s'), 'title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[ $category ][] = $file;
            
        }

   		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( 'UKM-festivalen '. $year .' i UKM-TV' );
		$SEO->setDescription( 'UKM-filmer fra UKM-festivalen '. $year );
   
        return $this->render('UKMNtvguiBundle:Festivalen:year.html.twig', array( 'year' => $year, 'files' => $files ) );
    }
}
