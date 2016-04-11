<?php

namespace UKMNorge\UKMtvBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SQL;
use SQLins;
use innslag;
use monstring;
use person;

class CronController extends Controller
{
    public function playcountAction()
    {
	    require_once('UKM/sql.class.php');
	    $sql = new SQL("SELECT `tv_id`, COUNT(`p_id`) AS `plays`
				FROM `ukm_tv_plays`
				GROUP BY `tv_id`"
				);
		$res = $sql->run();
		while($r = mysql_fetch_assoc( $res )) {
			$upd = new SQLins('ukm_tv_plays_cache', array('tv_id' => $r['tv_id']));
			$upd->add('plays', $r['plays']);
			$updRES = $upd->run();
			if($updRES == 0) {
				$ins = new SQLins('ukm_tv_plays_cache');
				$ins->add('tv_id', $r['tv_id']);
				$ins->add('plays', $r['plays']);
				$insRES = $ins->run();
			}
		}
	    
        return $this->render('UKMtvBundle:Cron:playcount.html.twig', array());
    }
    
    public function syncAction() {
	    require_once('UKM/sql.class.php');
		require_once('UKM/innslag.class.php');
		require_once('UKM/monstring.class.php');
		require_once('UKM/person.class.php');
		require_once('cron.functions.tv.php');
		##################################################
		echo '<h2>Oppdaterer db for WP_related</h2>';
		$qry = new SQL("SELECT * 
						FROM `ukmno_wp_related`
						WHERE `post_type` = 'video'
						ORDER BY `rel_id` ASC
						");
		$res = $qry->run();
		while($r = mysql_fetch_assoc($res)) {
			$data = video_calc_data('wp_related', $r);
			tv_update($data);
		}
		##################################################
		echo '<h2>Oppdaterer db for UKM standalone video (2013)</h2>';
		$qry = new SQL("SELECT * FROM `ukm_standalone_video`");
		$res = $qry->run();
		while( $r = mysql_fetch_assoc( $res ) ) {
			$data = video_calc_data('standalone_video', $r );
			tv_update($data);
		}
		
        return $this->render('UKMtvBundle:Cron:sync.html.twig', array());
    }
    
    public function tagsAction($page=1) {
	    require_once('UKM/sql.class.php');

		$perpage = 500;
		$stop = $perpage*$page;
		$start = $stop-$perpage;
		$perpage++; // Korriger så man ikke hopper over en per pageload
		$sql = new SQL("SELECT * FROM `ukm_tv_files` WHERE `tv_id` > '$start' AND `tv_id` < '$stop' LIMIT $perpage");
		$res = $sql->run();
		
		while( $r = mysql_fetch_assoc( $res ) ) {
		    $tv_id = $r['tv_id'];
		    
		    $tags = $r['tv_tags'];
		    $all_tags = explode('|', $tags);    
		    
		    foreach( $all_tags as $tag ) {
		        if( empty( $tag ) ) 
		            continue;
		            
		        $tag_data = explode('_', $tag );
		        		        
		        switch( $tag_data[0] ) {
		            case 'b':   $type = 'innslag';      break;
		            case 'p':   $type = 'person';       break;
		            case 'k':   $type = 'kommune';      break;
		            case 'f':   $type = 'fylke';        break;
		            case 's':   $type = 'sesong';       break;
		            case 'pl':  $type = 'monstring';   break;
		            case 'p':   $type = 'person';       break;
		            case 't':
		                $type = 'type';
		                    switch( $tag_data[1] ) {
		                        case 'land':    $tag_data[1] = 3;   break;
		                        case 'fylke':   $tag_data[1] = 2;   break;
		                        case 'kommune':   $tag_data[1] = 1;   break;
		                    }
		                break;
		            #default:
		            #    die('Mangler støtte for '. $tag_data[0]);
		        }
		        $SQLins = new SQLins('ukm_tv_tags');
		        $SQLins->add('tv_id', $tv_id);
		        $SQLins->add('type', $type);
		        $SQLins->add('foreign_id', $tag_data[1]);
		        $SQLins->add('id', $tv_id.$type.$tag_data[1] );
		        echo $SQLins->debug();
		        $SQLins->run();
		    }
		}
		
        return $this->render('UKMtvBundle:Cron:tags.html.twig', array( 'nextpage' => $page+1 ));
    }
}
