<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use stdClass;
use tv;
use tv_files;
use innslag;
use person;
use monstring;

class FilmController extends Controller
{ 
	private function _safeURL($string) {
		$string = str_replace(array(' ','Æ','æ','Ø','ø','Å','å'), array('-','Ae','ae','O','o','A','a'), $string);
		$string = preg_replace('/[^a-z0-9A-Z-_]+/', '', $string);
		return str_replace('--','-', $string);
	}
	
    public function indexAction( Request $request, $id, $title )
    {
        require_once('UKM/tv.class.php');
        require_once('UKM/tv_files.class.php');
        require_once('UKM/monstring.class.php');
        require_once('UKM/innslag.class.php');
        require_once('UKM/person.class.php');
        $TV = new tv( $id );
             
        $predashtitle = substr( $TV->title, 0, strpos( $TV->title, ' - ') );
        $postdashtitle = substr( $TV->title, 3+strpos( $TV->title, ' - ') );
        
        $metadata = new stdClass();
        $metadata->personer = [];
        $metadata->category = new stdClass();
        $metadata->category->parent = new stdClass();
        $files = [];

        // PERSONS
        if( $TV->b_id > 0 ) {
            $inn = new innslag( $TV->b_id );
    		foreach($inn->personer() as $pers) {
    			$p = new person( $pers['p_id'] );
    			$person = new stdClass();
    			$person->navn = $p->get('name');
    			$person->url = $this->get('router')->generate('ukmn_tvgui_person', array('id' => $p->get('p_id'), 'name' => $this->_safeURL($person->navn) ));
    
    			$metadata->personer[] = $person;
            }
        }
        // METADATA
            // PLACE
            preg_match('|pl_([0-9]+)|', $TV->tags, $matches_pl);
            $pl_id = $matches_pl[1];
            $monstring = new monstring( $pl_id );
            
            // BAND RELATED
            $metadata->bandrelated = $TV->b_id > 0;
            if( $metadata->bandrelated ) {
                $metadata->band = new stdClass();
                $metadata->band->title = $inn->g('b_name');
                $metadata->band->url = $this->get('router')
                                            ->generate('ukmn_tvgui_band', array('id' => $inn->g('b_id'), 'name' => $this->_safeURL($metadata->band->title)) );
            }
            
            if( $pl_id == 0 ) {
                $cat_type = 'info';
                $metadata->category->type = $cat_type;
            } else {
                // RELATED VIDEOS OF COLLECTION
                $related = new tv_files('related', $TV);
                while( $file = $related->fetch( 12 ) ) {
                    if( !$file->id ) {
                        continue;
                    }       
                    $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
                    $files[ false ][] = $file;
                }

                $cat_type = preg_match( '|t_([a-z]+)|', $TV->tags, $matches);
                $metadata->category->type = $matches[1];
                $metadata->category->title = $monstring->g('pl_name') .' '. $monstring->g('season');
            }
            // SET / COLLECTION
            switch( $metadata->category->type ) {
                case 'land':
                    $metadata->category->url = $this->get('router')
                                                    ->generate('ukmn_tvgui_festivalen_year', array('year' => $monstring->g('season') ) );
                    $metadata->category->parent->url = $this->get('router')->generate('ukmn_tvgui_festivalen_homepage');
                    $metadata->category->parent->title = 'UKM-festivaler';
                    break;
                case 'fylke':
                    $fylke_urlname = $this->get('ukmnorge.FylkeService')->id_to_name( $monstring->g('fylke_id') );
                    $metadata->category->url = $this->get('router')
                                                    ->generate('ukmn_tvgui_fylke_year', array('fylke' => $fylke_urlname, 'year' => $monstring->g('season') ) );
                    $metadata->category->parent->url = $this->get('router')->generate('ukmn_tvgui_fylke_homepage');
                    $metadata->category->parent->title = 'Fylkesmønstringer';
                    break;
                case 'kommune':
                	// URL-hentede attributter:
                        $kommune = $request->attributes->get('kommune'); // Kommune-ID
                        $name = $request->attributes->get('name'); // Kommune-name
                        $season = $request->attributes->get('season'); // Current season
                        // UKM-tv fix 13.02.16 pga lokalsidelinktull fra innslagsvideoer
                        // Asgeirsh@ukmmedia.no
                        if ($metadata->bandrelated) {
                        	// Fordi $inn ikke finnes for videoreportasjer, men da i URLen.
                                $kommune = $inn->get('b_kommune');
                                $season = $inn->get('b_season');
                        }
                        $name = $monstring->_sanitize_nordic($monstring->get('pl_name'));
                        
                    $metadata->category->url = $this->get('router')
                                                    ->generate('ukmn_tvgui_lokal_year', array('kommune' => $kommune, 'name'=>$name,'season'=>$season) );
                    $metadata->category->parent->url = $this->get('router')->generate('ukmn_tvgui_lokal_homepage');
                    $metadata->category->parent->title = 'Lokalmønstringer';
                    break;
                case 'info':
                    $metadata->category->parent->url = $this->get('router')->generate('ukmn_tvgui_info_homepage');
                    $metadata->category->parent->title = 'Infovideoer';
                    break;
            }
			
		if( !mb_detect_encoding($TV->description, 'UTF-8', true) ) {
	        $TV->description = mb_convert_encoding($TV->description, 'UTF-8' );
	    }

        // RENDER
        return $this->render('UKMNtvguiBundle:Film:index.html.twig', array( 'tv' => $TV, 'jumbo_title' => $predashtitle, 'jumbo_description' => $postdashtitle, 'meta' => $metadata, 'files' => $files ));
    }
}
