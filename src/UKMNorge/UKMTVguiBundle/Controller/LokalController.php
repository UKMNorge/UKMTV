<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use stdClass;
use monstring;
use kommune_monstring;
use sql;

class LokalController extends Controller
{
	private function _safeURL($string) {
		$string = mb_convert_encoding( $string, 'UTF-8');
		$string = str_replace(array(' ','Æ','æ','Ø','ø','Å','å'), array('-','Ae','ae','O','o','A','a'), $string);
		$string = preg_replace('/[^a-z0-9A-Z-_]+/', '', $string);
		return str_replace('--','-', $string);
	}
    public function indexAction( Request $request )
    {
        $kommune_id = "";

        $em = $this->getDoctrine()->getManager();
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
            $kommune->url  = $this->get('router')->generate('ukmn_tvgui_lokal_years', array('kommune' => $data['kommune_id'], 'name' => $this->_safeURL($data['kommune'])) );
            
            $monstringer[ $data['fylke_id'] ]->kommuner[] = $kommune;
        }

		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( 'Lokalmønstringer i UKM-TV' );
		$SEO->setDescription( 'UKM-filmer fra lokalmønstringene 2009 - '. date("Y") );
		
        return $this->render('UKMNtvguiBundle:Lokal:index.html.twig', array( 'monstringer' => $monstringer ));
    }
    
    
    public function yearsAction( Request $request, $kommune, $name ) {
        
        require_once('UKM/monstring.class.php');
        require_once('UKM/sql.class.php');
        
        $all_years = array();
        
        $year_start = 2009;
        $year_stop = (int) date('Y')+3;
        
        $kommune_navn = new SQL("SELECT `name` FROM `smartukm_kommune` WHERE `id` = '#kommune'",
        				array('kommune'=>$kommune));
        $kommune_navn = $kommune_navn->run('field', 'name');
        
        
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
                $year->url = $this->get('router')->generate('ukmn_tvgui_lokal_year', array('kommune' => $kommune, 'name' => $name, 'season' => $year->year ) );
                $all_years[] = $year;
            }
        }

		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( $kommune_navn .' i UKM-TV' );
		$SEO->setDescription( 'Alle filmer fra lokalmønstringen i '. $kommune_navn );

        return $this->render('UKMNtvguiBundle:Lokal:years.html.twig', array('kommune' => $kommune_navn, 'years' => $all_years ));        
    }
    
    public function yearAction( Request $request, $kommune, $name, $season) {
        
        require_once('UKM/monstring.class.php');
        $monstring = new kommune_monstring( $kommune, $season );
        $monstring = $monstring->monstring_get();
        
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

            $kommune_qry = new SQL("SELECT `name` FROM `smartukm_kommune` WHERE `id` = '#id'", array('id'=>$file->tag('k')));
            $kommune_name = $this->_safeURL($kommune_qry->run('field','name'));
           
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_lokal_film', array('kommune'=>$kommune,'name'=>$kommune_name,'season'=>$season,'title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[ $category ][] = $file;
            
        }

		/* SET SEO STUFF */
		$SEO = $this->get('ukmdesign.seo');
		$SEO->setSiteName('UKM.no');
		$SEO->setSection('UKM-TV');
		$SEO->setCanonical( $request->getUri() );
		
		$SEO->setTitle( $monstring->get('pl_name') .' '. $season .' i UKM-TV' );
		$SEO->setDescription( 'Alle filmer fra lokalmønstringen i '. $monstring->get('pl_name') .' '. $season );

        return $this->render('UKMNtvguiBundle:Lokal:year.html.twig', array( 'year' => $monstring->get('season'), 'files' => $files, 'title' => $monstring->get('pl_name'), 'monstring' => $monstring ) );
    }
}
