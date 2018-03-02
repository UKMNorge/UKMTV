<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use stdClass;
use tv_files;

class InfoController extends Controller
{
    public function indexAction( Request $request )
    {
        require_once('UKM/tv_files.class.php');
        $tv_files = new \tv_files('place', 0 );
        $files = array();
        
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $category = $file->set;         
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_info_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[ $category ][] = $file;
            
        }

		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( 'UKM-TV: Infovideoer' );
		$SEO->setDescription( 'Filmer fra UKM Norge' );

        return $this->render('UKMNtvguiBundle:Info:index.html.twig', array( 'files' => $files ) );
    }
}
