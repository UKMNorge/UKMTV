<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use stdClass;
use tv;
use tv_files;
use monstring;
use landsmonstring;
use sql;

class DefaultController extends Controller
{
	private function _safeURL($string) {
		$string = str_replace(array(' ','Æ','æ','Ø','ø','Å','å'), array('-','Ae','ae','O','o','A','a'), $string);
		$string = preg_replace('/[^a-z0-9A-Z-_]+/', '', $string);
		return str_replace('--','-', $string);
	}


    public function indexAction()
    {
        require_once('UKM/tv_files.class.php');
        require_once('UKM/tv.class.php');
        require_once('UKM/monstring.class.php');

        $files = $this->_getPopular();        
        $page_nav = $this->_getPageNav();
        $events = $this->_getEvents();   
        
        $jumbo = new stdClass();
        $jumbo->header = 'UKM-TV';
        $jumbo->content = 'UKM-filmer fra de siste '. (date("Y")-2009 ). ' årene';
        
        $data = array('jumbo' => $jumbo, 'page_nav' => $page_nav, 'popular' => $files, 'events' => $events);
        
        $etter_festivalen = true; // AKA quickfix
        
        if( $etter_festivalen ) {
		    $monstring = new landsmonstring(date("Y"));
		    $this->festivalen = $monstring->monstring_get();

	        $festival = array('' => $this->_getFestival());
	        $festivalfilm = $this->_getFestivalFilm();
	        
	        $data['popular'] = array(''=>$festivalfilm);
	        
	        $data['festivalen'] = array('filer' => $festival, 
	        							'festivalfilm' => $festivalfilm,
	        							'pl_id' => $this->festivalen->get('pl_id'),
	        							'year' => $this->festivalen->get('season')
	        							);
		}
        return $this->render('UKMNtvguiBundle:Front:index.html.twig', $data);
    }
    
	private function _getFestivalFilm() {
        $id = 7288;	// AKA quickfix 2
        $TV = new tv( $id );
        $TV->predashtitle = $TV->title;#substr( $TV->title, 0, strpos( $TV->title, ' - ') );
        $TV->postdashtitle = utf8_encode($TV->description);#substr( $TV->title, 3+strpos( $TV->title, ' - ') );
        $TV->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $TV->title_urlsafe, 'id' => $TV->id) );
		return $TV;
    }
    
    private function _getFestival() {
        @include_once('UKM/monstring_tidligere.class.php'); // TODO: hva er riktig - dev-API eller prod-API? (førstnevnte..?)
	    $tv_files = new tv_files('popular_from_plid', $this->festivalen->get('pl_id'));
		$files = [];
        while( $file = $tv_files->fetch(6) ) {
            if( !$file->id ) {
                continue;
            }       
            $file->predashtitle = substr( $file->title, 0, strpos( $file->title, ' - ') );
            $file->postdashtitle = substr( $file->title, 3+strpos( $file->title, ' - ') );
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[] = $file;            
        }


	    return $files;
    }
    
    private function _getPopular() {
         // POPULÆRE FILMER DENNE MÅNEDEN
        $files = [];
        $tv_files = new tv_files('popular',date('Y-m'));
        while( $file = $tv_files->fetch(6) ) {
            if( !$file->id ) {
                continue;
            }       
            $file->predashtitle = substr( $file->title, 0, strpos( $file->title, ' - ') );
            $file->postdashtitle = substr( $file->title, 3+strpos( $file->title, ' - ') );
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[] = $file;            
        }
        
        // POPULÆRE FILMER TOTALT, HVIS INGEN POPULÆRE FILMER DENNE MÅNEDEN        
        if( sizeof( $files == 0 ) ) {
            $tv_files = new tv_files('popular');
            while( $file = $tv_files->fetch(6) ) {
                if( !$file->id ) {
                    continue;
                }
                $file->predashtitle = substr( $file->title, 0, strpos( $file->title, ' - ') );
                $file->postdashtitle = substr( $file->title, 3+strpos( $file->title, ' - ') );
                $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
                $files[] = $file;            
            }
        }
        return $files;
    }
    
    private function _getPageNav() {
        $page_nav = [];
		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_search'),
									  'title'		 	=> 'Søk i UKM-TV',
									  'icon'			=> 'nav-search',
									  'description'	=> ''
									  );
        $page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_festivalen_homepage'),
									  'title'		 	=> 'TV fra festivalen',
									  'icon'			=> 'nav-rocket',
									  'description'	=> ''
									  );
									  
		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_fylke_homepage'),
									  'title'		 	=> 'TV fra fylkesmønstringer',
									  'icon'			=> 'file',
									  'description'	=> ''
									  );

		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_lokal_homepage'),
									  'title'		 	=> 'TV fra lokalmønstringer',
									  'icon'			=> 'file',
									  'description'	=> ''
									  );

		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_info_homepage'),
									  'title'		 	=> 'Infovideoer på UKM-TV',
									  'icon'			=> 'nav-i',
									  'description'	=> ''
									  );
        return $page_nav;
    }
    
    
    private function _getEvents() {
        // SISTE HENDELSER
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare("SELECT `tv`.`tv_id`
                                           FROM `ukm_tv_tags` AS `tag`
                                           JOIN `ukm_tv_files` AS `tv`
                                                ON (`tv`.`tv_id` = `tag`.`tv_id`)
                                           WHERE `type` = 'monstring'
                                           AND `tv_deleted` = 'false'
                                           GROUP BY `foreign_id`
                                           ORDER BY `tv_id` DESC
                                           LIMIT 6");
        $statement->execute();
        $event_files = $statement->fetchAll();
        $events = [];
        
        foreach( $event_files as $eventdata ) {
            $TV = new tv( $eventdata['tv_id'] );
            if( !$TV->id ) {
                continue;
            }

            // PLACE
            preg_match('|pl_([0-9]+)|', $TV->tags, $matches_pl);
            $pl_id = $matches_pl[1];
            $monstring = new monstring( $pl_id );
            
            $type = $monstring->get('type');
            switch( $type ) {
                case 'kommune':
	                require_once('UKM/sql.class.php');
                    $route = 'ukmn_tvgui_lokal_year';
					$kommune_id = $TV->tag('k');					
		            $kommune_qry = new SQL( "SELECT `name` FROM `smartukm_kommune` WHERE `id` = '#id'", array('id'=> $kommune_id) );
		            $kommune = $this->_safeURL($kommune_qry->run('field','name'));
                    
                    $route_data = array('kommune' => $TV->tag('k') , 'name' => $kommune, 'season' => $TV->tag('s'));
                    $title = 'UKM '. $monstring->get('pl_name') .' '. $monstring->get('season');
                    break;
                case 'fylke':
                    $route = 'ukmn_tvgui_fylke_year';
                    $fylkename = $this->get('UKMNorge.FylkeService')->id_to_name( $monstring->get('fylke_id') );
                    $route_data = array('fylke' => $fylkename , 'year' => $monstring->get('season'));
                    $title = 'Fylkesmønstringen i '. $monstring->get('pl_name') .' '. $monstring->get('season');
                    break;
                case 'land':
                    $route = 'ukmn_tvgui_festivalen_year';
                    $route_data = array('year' => $monstring->get('season'));
                    $title = $monstring->get('pl_name') .' '. $monstring->get('season');
                    break;
            }
                        
            $event = new stdClass();
            $event->url = $this->get('router')->generate( $route, $route_data );
            $event->title = $title;

            $events[] = $event;
        }
        return $events;
    }
}
