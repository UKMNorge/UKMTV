<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use stdClass;
use tv;
use tv_files;
use monstring;

class SearchController extends Controller
{
    public function indexAction( Request $request )
    {
        require_once('UKM/tv_files.class.php');
        require_once('UKM/tv.class.php');
        require_once('UKM/monstring.class.php');

		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( 'Søk i UKM-TV' );
		$SEO->setDescription( 'Søk blant filmer fra 2009 - '. date('Y') );
		     
        return $this->render('UKMNtvguiBundle:Search:index.html.twig', array());
    }
    
    public function doSearchAction() {
        $searchstring = $this->get('request')->request->get('doSearchFor');
        
        return $this->redirect( $this->get('router')->generate('ukmn_tvgui_searchfor', array('doSearchFor' => $searchstring ) ) );
    }
    
    public function resultAction( Request $request, $doSearchFor ) {
    
        require_once('UKM/tv_files.class.php');
        require_once('UKM/tv.class.php');
		$files = array();
        $tv_files = new tv_files('search', $doSearchFor );
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[] = $file;
            
        }
        
		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( 'Søkeresultat for '. $doSearchFor .' i UKM-TV' );
		$SEO->setDescription( 'Viser '. sizeof( $files ) .' filme'. ( sizeof( $files ) == 1 ? '' : 'r' ) );

        return $this->render('UKMNtvguiBundle:Search:results.html.twig', array('doSearchFor' => $doSearchFor, 'list' => $files ));
    }
}
