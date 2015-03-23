<?php
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
##################################################
echo '<h2>Oppdaterer db for Smartukm TAG</h2>';
$qry = new SQL("SELECT `smartukm_tag`.`b_id`,
					   `smartcore_videos`.`id`,
					   `smartcore_videos`.`file`
				FROM `smartukm_tag`
				JOIN `smartcore_videos` ON (`smartukm_tag`.`foreign_id` = `smartcore_videos`.`id`)
				WHERE `foreign_table` = 'smartcore_videos'
				AND `status` = 'approved'
				AND `conv_status` = 'done'
				AND `b_id` > 0");
$res = $qry->run();
if($res) {
	while($r = mysql_fetch_assoc($res)) {
		$data = video_calc_data('smartukm_tag', $r);
		if(!$data)
			continue;
		tv_update($data);
	}
	
}
##################################################

echo '<h3>UKM-TV oppdatert</h3>';
