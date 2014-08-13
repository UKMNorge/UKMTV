<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use stdClass;
use monstring;

class LokalController extends Controller
{
    public function indexAction()
    {
        $kommune_id = "";

        $em = $this->getDoctrine()->getEntityManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare("SELECT `k`.`name` AS `kommune`,
                                                  `k`.`id` AS `kommune_id`,
                                                  `f`.`name` AS `fylke`,
                                                  `f`.`id` AS `fylke_id`
                                            FROM `smartukm_kommune` AS `k`
                                            JOIN `ukm_tv_tags` AS `tvtag` 
                                            	ON (`k`.`id` = `tvtag`.`foreign_id` AND `tvtag`.`type` = 'kommune' 
                                            	    AND (SELECT `tvtag2`.`id` 
                                            			 FROM `ukm_tv_tags` AS `tvtag2` 
                                            			 WHERE `tvtag2`.`tv_id` = `tvtag`.`tv_id` 
                                            			 AND `tvtag2`.`type` = 'type'
                                            			 AND `foreign_id` = 1)
                                            		)
                                            JOIN `smartukm_fylke` AS `f` ON (`k`.`idfylke` = `f`.`id`)
                                            GROUP BY `k`.`id`
                                            ORDER BY `fylke` ASC, `kommune` ASC");
        #$statement->bindValue('id', 123);
        $statement->execute();
        $results = $statement->fetchAll();
        
        $monstringer = [];
        
        foreach( $results as $data ) {
            if( !isset( $monstringer[ $data['fylke_id'] ] ) ) {
                $fylke = new stdClass();
                $fylke->name        = $data['fylke'];
                $fylke->id          = $data['fylke_id'];
                $fylke->kommuner    = [];
                
                $monstringer[ $fylke->id ] = $fylke;
            }
            
            $kommune = new stdClass();
            $kommune->name = $data['kommune'];
            $kommune->id   = $data['kommune_id'];
            $kommune->url  = $this->get('router')->generate('ukmn_tvgui_lokal_years', array('kommune' => $data['kommune_id'], 'name' => $data['kommune']) );
            
            $monstringer[ $data['fylke_id'] ]->kommuner[] = $kommune;
        }
        
        return $this->render('UKMNtvguiBundle:Lokal:index.html.twig', array( 'monstringer' => $monstringer ));
    }
    
    
    public function yearsAction( $kommune, $name ) {
        
        require_once('UKM/monstring.class.php');
        
        $all_years = array();
        
        $year_start = 2009;
        $year_stop = (int) date('Y')+3;
        
        for( $i = $year_start; $i < $year_stop; $i++ ) {
            $monstring = new \kommune_monstring( $kommune, $i );
            $monstring = $monstring->monstring_get();
            if( get_class( $monstring ) == 'monstring' && $monstring->g('pl_id') != false ) {
                if( !$monstring->har_ukmtv() ) {
                    continue;
                }
                $year = new stdClass();
                $year->year = $i;
                $year->title = $monstring->g('pl_name');
                $year->url = $this->get('router')->generate('ukmn_tvgui_lokal_year', array('plid' => $monstring->g('pl_id'), 'name' => $monstring->g('pl_name') ) );
                $all_years[] = $year;
            }
        }

    
        return $this->render('UKMNtvguiBundle:Lokal:years.html.twig', array('kommune' => $name, 'years' => $all_years ));        
    }
    
    public function yearAction($plid, $name) {
        
        require_once('UKM/monstring.class.php');
        $monstring = new monstring( $plid );
        
        if( $monstring->g('pl_id') == false ) {
            throw $this->createNotFoundException('Beklager, vi finner ikke lokalmønstringen for '. $year .'. Sikker på at du har skrevet inn riktig URL?');
        }
        
        require_once('UKM/tv_files.class.php');
        $tv_files = new \tv_files('place', $monstring->get('pl_id') );
        $files = array();
        
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $category = $file->set;         
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_film', array('title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[ $category ][] = $file;
            
        }
      
        return $this->render('UKMNtvguiBundle:Lokal:year.html.twig', array( 'year' => $monstring->get('season'), 'files' => $files, 'title' => $monstring->get('pl_name') ) );
    }
}
