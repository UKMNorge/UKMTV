<?php
/* UKM LOADER */ if(!defined('UKM_HOME')) define('UKM_HOME', '/home/ukmno/public_html/UKM/'); require_once(UKM_HOME.'loader.php');
UKM_loader('sql');

$sql = new SQL("SELECT `tv_id`, COUNT(`p_id`) AS `plays`
				FROM `ukm_tv_plays`
				GROUP BY `tv_id`"
				);
$res = $sql->run();
while($r = SQL::fetch( $res )) {
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