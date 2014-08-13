<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use stdClass;
use tv;
use tv_files;
use innslag;
use person;
use monstring;

class FilmController extends Controller
{ 
    public function indexAction( $id, $title )
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
    			$person->url = $this->get('router')->generate('ukmn_tvgui_person', array('id' => $p->get('p_id') ));
    
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
                                            ->generate('ukmn_tvgui_band', array('id' => $inn->g('b_id')) );
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
                    $metadata->category->url = $this->get('router')
                                                    ->generate('ukmn_tvgui_lokal_year', array('plid' => $monstring->get('pl_id'), 'name' => $monstring->get('pl_name') ) );
                    $metadata->category->parent->url = $this->get('router')->generate('ukmn_tvgui_lokal_homepage');
                    $metadata->category->parent->title = 'Lokalmønstringer';
                    break;
                case 'info':
                    $metadata->category->parent->url = $this->get('router')->generate('ukmn_tvgui_info_homepage');
                    $metadata->category->parent->title = 'Infovideoer';
                    break;
            }

        $TV->description = utf8_encode( $TV->description );

        // RENDER
        return $this->render('UKMNtvguiBundle:Film:index.html.twig', array( 'tv' => $TV, 'jumbo_title' => $predashtitle, 'jumbo_description' => $postdashtitle, 'meta' => $metadata, 'files' => $files ));
    }
}
