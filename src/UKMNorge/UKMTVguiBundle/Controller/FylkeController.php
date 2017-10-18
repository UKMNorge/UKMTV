<?php

namespace UKMNorge\UKMTVguiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use stdClass;

class FylkeController extends Controller
{
    public function indexAction()
    {
        return $this->render('UKMNtvguiBundle:Fylke:index.html.twig', array( ));
    }
    
    public function yearsAction( $fylke )
    {
    
        $fylke_id = $this->get('ukmnorge.FylkeService')->name_to_id( $fylke );
        $fylke_name = 'UKM-festivalen i '. $this->get('ukmnorge.FylkeService')->id_to_human( $fylke_id );
       
        require_once('UKM/monstring.class.php');
        
        $all_years = array();
        
        $year_start = 2009;
        $year_stop = (int) date('Y')+3;
        
        for( $i = $year_start; $i < $year_stop; $i++ ) {
            $monstring = new \fylke_monstring( $fylke_id, $i );
            $monstring = $monstring->monstring_get();
            if( get_class( $monstring ) == 'monstring' && $monstring->g('pl_id') != false ) {
                if( !$monstring->har_ukmtv() ) {
                    continue;
                }
                $year = new stdClass();
                $year->year = $i;
                $year->title = $monstring->g('pl_name');
                $year->url = $this->get('router')->generate('ukmn_tvgui_fylke_year', array('fylke' => $fylke, 'year' => $i ) );
                $all_years[] = $year;
            }
        }
        return $this->render('UKMNtvguiBundle:Fylke:years.html.twig', array('fylke_name' => $fylke_name, 'years' => $all_years ));
    }
    
    public function yearAction( $fylke,  $year ) {

        $fylke_id = $this->get('ukmnorge.FylkeService')->name_to_id( $fylke );
        
        require_once('UKM/monstring.class.php');
        $monstring = new \fylke_monstring( $fylke_id, $year );
        $monstring = $monstring->monstring_get();
        
        if( $monstring->g('pl_id') == false ) {
            throw $this->createNotFoundException('Beklager, vi finner ikke fylkesmønstringen for '. $year .'. Sikker på at du har skrevet inn riktig URL?');
        }
        
        require_once('UKM/tv_files.class.php');
        $tv_files = new \tv_files('place', $monstring->get('pl_id') );
        $files = array();
        
        while( $file = $tv_files->fetch() ) {
            if( !$file->id ) {
                continue;
            }       
            $category = $file->set;
            if( $category == 'Fylkesmønstringen i '. $monstring->get('fylke_name') .' '. $year ) {
	            $category = 'Alle innslag';
            } else {
	            $category = mb_convert_case( str_replace( $monstring->get('fylke_name') .' '. $year, '', $category ), MB_CASE_TITLE);
            }
            
            $file->full_url = $this->get('router')->generate('ukmn_tvgui_fylke_film', array('fylke'=>$fylke, 'year'=>$year, 'title' => $file->title_urlsafe, 'id' => $file->id) );
            $files[ $category ][] = $file;
            
        }
      
        return $this->render('UKMNtvguiBundle:Fylke:year.html.twig', array( 'year' => $year, 'fylke' => $monstring->get('fylke_name'), 'files' => $files ) );
    }
}
