<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use stdClass;
use tv;
use tv_files;
use monstring;

class DefaultController extends Controller
{
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
     
        return $this->render('UKMNtvguiBundle:Front:index.html.twig', array('jumbo' => $jumbo, 'page_nav' => $page_nav, 'popular' => $files, 'events' => $events ));
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
        $page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_festivalen_homepage'),
									  'title'		 	=> 'Festivalen',
									  'icon'			=> 'nav-rocket',
									  'description'	=> ''
									  );
									  
		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_fylke_homepage'),
									  'title'		 	=> 'Fylkesmønstringer',
									  'icon'			=> 'file',
									  'description'	=> ''
									  );

		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_lokal_homepage'),
									  'title'		 	=> 'Lokalmønstringer',
									  'icon'			=> 'file',
									  'description'	=> ''
									  );

		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_info_homepage'),
									  'title'		 	=> 'Infovideoer',
									  'icon'			=> 'nav-i',
									  'description'	=> ''
									  );
		$page_nav[] = (object) array( 'url' 			=> $this->get('router')->generate('ukmn_tvgui_search'),
									  'title'		 	=> 'Søk',
									  'icon'			=> 'nav-search',
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
                    $route = 'ukmn_tvgui_lokal_year';
                    $route_data = array('plid' => $monstring->get('pl_id') , 'name' => $monstring->get('pl_name'));
                    $title = 'UKM '. $monstring->get('pl_name') .' '. $monstring->get('season');
                    continue;

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
