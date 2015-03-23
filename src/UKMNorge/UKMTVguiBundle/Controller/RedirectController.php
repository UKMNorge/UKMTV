<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use stdClass;
use tv;
use tv_files;
use monstring;
use sql;

class RedirectController extends Controller
{
	private function _safeURL($string) {
		$string = str_replace(array(' ','Æ','æ','Ø','ø','Å','å'), array('-','Ae','ae','O','o','A','a'), $string);
		$string = preg_replace('/[^a-z0-9A-Z-_]+/', '', $string);
		return str_replace('--','-', $string);
	}

	
	private function _goToFilm( Request $request, $id ) {
	
		require_once('UKM/tv.class.php');
	    $TV = new tv( $id );
	    
	    $data = array('id' => $id,
	    			  'title' => $request->attributes->get('title')
	    			 );
	    			 
	    switch( $TV->category ) {
		    case 'UKM-Festivaler':
		    	$data['year'] = $TV->tag('s');
		    	return $this->redirect( $this->get('router')->generate('ukmn_tvgui_festivalen_film', $data ) );
		    break;
		    case 'Fylkesmønstringer':
		    	$data['year'] = $TV->tag('s');
				$data['fylke'] = $this->get('ukmnorge.FylkeService')->id_to_name( $TV->tag('f') );
		    	return $this->redirect( $this->get('router')->generate('ukmn_tvgui_fylke_film', $data ) );
		    case 'Lokalmønstringer':
		    	$data['season'] = $TV->tag('s');
				$data['kommune'] = $TV->tag('k');
				
	            $kommune_qry = new SQL( "SELECT `name` FROM `smartukm_kommune` WHERE `id` = '#id'", array('id'=> $data['kommune']) );
	            $data['name'] = $this->_safeURL($kommune_qry->run('field','name'));

		    	return $this->redirect( $this->get('router')->generate('ukmn_tvgui_lokal_film', $data ) );
			default:
				return $this->redirect( $this->get('router')->generate('ukmn_tvgui_info_film', $data ) );
	    }
	}
	
	private function _goToSet( $TV ) {	    
	    $data = array();
	    			 
	    switch( $TV->category ) {
		    case 'UKM-Festivaler':
		    	$data['year'] = $TV->tag('s');
		    	return $this->redirect( $this->get('router')->generate('ukmn_tvgui_festivalen_year', $data ) );
		    break;
		    case 'Fylkesmønstringer':
		    	$data['year'] = $TV->tag('s');
				$data['fylke'] = $this->get('ukmnorge.FylkeService')->id_to_name( $TV->tag('f') );
		    	return $this->redirect( $this->get('router')->generate('ukmn_tvgui_fylke_year', $data ) );
		    case 'Lokalmønstringer':
		    	$data['season'] = $TV->tag('s');
				$data['kommune'] = $TV->tag('k');
				
	            $kommune_qry = new SQL( "SELECT `name` FROM `smartukm_kommune` WHERE `id` = '#id'", array('id'=> $data['kommune']) );
	            $data['name'] = $this->_safeURL($kommune_qry->run('field','name'));

		    	return $this->redirect( $this->get('router')->generate('ukmn_tvgui_lokal_year', $data ) );
			default:
				return $this->redirect( $this->get('router')->generate('ukmn_tvgui_info_homepage', $data ) );
	    }
	    
	    die( 'FILM REDIR' );
		
	}
    public function goAction( Request $request, $goTo )
    {

		switch( $goTo ) {
			case 'film':
				return $this->_goToFilm( $request, $request->attributes->get('id') );

			case 'samling':
				require_once('UKM/tv_files.class.php');
				$tv_files = new tv_files('set', $request->attributes->get('samling'));
				$tv_files->limit(1);
				$redirFile = $tv_files->fetch(1);
				if( !$redirFile->id ) {
					return $this->redirect( $this->get('router')->generate('ukmn_tvgui_homepage') );
				}
				return $this->_goToSet( $redirFile );
			case 'festivaler':
				return $this->redirect( $this->get('router')->generate('ukmn_tvgui_festivalen_homepage') );
			case 'lokalmonstringer':
				return $this->redirect( $this->get('router')->generate('ukmn_tvgui_lokal_homepage') );
			case 'fylkesmonstringer':
				return $this->redirect( $this->get('router')->generate('ukmn_tvgui_fylke_homepage') );
			case 'info':
				return $this->redirect( $this->get('router')->generate('ukmn_tvgui_info_homepage') );

			default:
				return $this->redirect( $this->get('router')->generate('ukmn_tvgui_homepage') );
		}
		var_dump( $goTo );
     
    }
}