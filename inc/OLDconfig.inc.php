<?php
/* UKM LOADER */ if(!defined('UKM_HOME')) define('UKM_HOME', '/home/ukmno/public_html/UKM/'); require_once(UKM_HOME.'loader.php');
UKM_loader('sql|curl');
define('BASEPATH', dirname(dirname(__FILE__)).'/');
define('BASEURL', 'http://tv.ukm.no/');
define('STORAGE', 'http://video.ukm.no/');

function load_videos($by, $key='') {
	$videos = array();
	switch($by) {
		case 'alphabet':
			$qry = new SQL("SELECT `tv_id`
							FROM `ukm_tv_files`
							WHERE `tv_title` LIKE '#key%'
							ORDER BY `tv_title` ASC",
							array('key' => $key));
			$res = $qry->run();
			if($res)
				while($r = mysql_fetch_assoc($res))
					$videos[] = $r['tv_id'];
		return $videos;
	}
}

function videoInfo( $v_id ) {
	if(!is_numeric($v_id))
		$qry = new SQL("SELECT *
						FROM `ukm_tv_files`
						WHERE `tv_file` = '#tvid'",
						array('tvid' => $v_id ));
	else
		$qry = new SQL("SELECT *
					FROM `ukm_tv_files`
					WHERE `tv_id` = '#tvid'",
					array('tvid' => $v_id ));

	$data = $qry->run('array');
	
	$dash = strpos($data['tv_title'], ' - ');
	if(!$dash)
		$dash = strlen($data['tv_title']);
	$url = substr($data['tv_title'],0, $dash);
	$url = str_replace(' ', '-', $url);
	$data['url'] = urlSafe($url).'/'.$data['tv_id'];
	$data['full_url'] = 'http://tv.ukm.no/'.urlSafe($url).'/'.$data['tv_id'];
#	$data['tv_category'] = $data['tv_category'];
	$data['category_link'] = BASEURL.'samling/'.$data['tv_category'];
	
	$category = new SQL("SELECT `f_name`
						 FROM `ukm_tv_categories` AS `cat`
						 JOIN `ukm_tv_category_folders` AS `fold` ON (`cat`.`f_id` = `fold`.`f_id`)
						 WHERE `cat`.`c_name` = '#category'",
						 array('category' => $data['tv_category']));
	$data['real_category'] = utf8_encode($category->run('field','f_name'));
	$data['real_category_link'] = '/kategorier/'.$data['real_category'];
/*
	foreach($data as $key => $val)
		$data[$key] = utf8_decode($val);
*/
	
	return $data;
}

function videoFile(&$video) {
	$file = $video['tv_file'];
	// Hvis det er videoconverteren som spør..
/*
	if($_SERVER['REMOTE_ADDR'] == '81.0.146.162') {
		$video['tv_file'] = $file;
	} else {
*/
	$lastslash = strrpos($file, '/');
	$path = substr($file, 0, $lastslash);
	$name = substr($file, $lastslash+1);

	$check = STORAGE.'find.php?file='.$name.'&path='.urlencode($path);
	$filename = curlURL($check,6);
	if(empty($filename))
		$video['tv_file'] = $file;
	else
		$video['tv_file'] = $filename;
/* 	} */
}

function urlSafe($url) {
	return preg_replace('/[^a-z0-9A-Z-_]+/', '', $url);
}

function videoPlay($video) {
	$ins = new SQLins('ukm_tv_plays');
	$ins->add('tv_id', $video['tv_id']);
	$ins->add('interval', 0);
	$ins->add('ip', $_SERVER['REMOTE_ADDR']);
	$ins->run();
}

function featured($listname, $limit=10, $perrow=5) {
	$videos = array();
	
	$sql = new SQL("SELECT `tv_id`, `feature_name`
					FROM `ukm_tv_featured`
					WHERE `feature_list` = '#list'
					ORDER BY RAND()
					LIMIT #limit
					",
					array('list' => $listname,
						  'limit' => $limit));
	$res = $sql->run();
	if($res)
		while($r = mysql_fetch_assoc( $res )) {
			$videos[] = array('tv_id' => $r['tv_id'], 'feat' => $r['feature_name']);
		}
	shuffle_and_limit($videos, $perrow);
	return $videos;
}

function videoRelated( $video, $perrow=5 ) {
	$videos = array();
	$sql = new SQL("SELECT `file`.`tv_id`,
						(SELECT COUNT(`playtable`.`p_id`)
						 FROM `ukm_tv_plays` AS `playtable`
						 WHERE `playtable`.`tv_id` = `file`.`tv_id`) AS `plays`
					FROM `ukm_tv_files` AS `file`
					WHERE `file`.`tv_category` = '#cat'
					AND `file`.`tv_id` != '#this'
					ORDER BY `plays` DESC, RAND()
					LIMIT 10",
					array('cat' => $video['tv_category'],
						  'this' => $video['tv_id']));
	// OPTIMIZE 2013.03.17 - cron cached table
	$sql = new SQL("SELECT `file`.`tv_id`,
						(SELECT `ukm_tv_plays_cache`.`plays`
						 FROM `ukm_tv_plays_cache`
						 WHERE `ukm_tv_plays_cache`.`tv_id` = `file`.`tv_id`) AS `plays`
					FROM `ukm_tv_files` AS `file`
					WHERE `file`.`tv_category` = '#cat'
					AND `file`.`tv_id` != '#this'
					ORDER BY `plays` DESC, RAND()
					LIMIT 10",
					array('cat' => $video['tv_category'],
						  'this' => $video['tv_id']));


	$res = $sql->run();
	if($res)
		while($r = mysql_fetch_assoc( $res )) {
			$videos[] = $r['tv_id'];
		}
	shuffle_and_limit($videos, $perrow);
	return $videos;
}

function videosPopular($limit=15) {
	$videos = array();
	$qry = new SQL("SELECT `tv_id`, COUNT(`tv_id`) AS `plays`
				FROM `ukm_tv_plays`
				WHERE `timestamp` LIKE '#timer%'
				GROUP BY `tv_id`
				ORDER BY `plays` DESC
				LIMIT #limit",
				array( 'timer' => date('Y-m'),
					   'limit' => $limit ));
	$res = $qry->run();
	if(!$res || ($res && mysql_num_rows($res)==0)) {
		$qry = new SQL("SELECT `tv_id`, COUNT(`tv_id`) AS `plays`
						FROM `ukm_tv_plays`
						GROUP BY `tv_id`
						ORDER BY `plays` DESC
						LIMIT 15");
		$res = $qry->run();
	}
	while( $r = mysql_fetch_assoc( $res ) ) {
		if(empty($r['tv_id']))
			continue;
		$videos[] = $r;
	}
	return $videos;
}

function shuffle_and_limit(&$videos, &$perrow) {
	shuffle($videos);
	if(!is_int(sizeof($videos) / $perrow)) {
		$total = sizeof($videos);
		$rows = sizeof($videos) % $perrow;
		$num_videos = $rows * $perrow;
		for($i = $num_videos; $i < $total; $i++) {
			array_pop($videos);
		}
	}
	return $videos;
}

function videoList( $v_id , $feature=false, $debuginfo='') {
	$video = videoInfo( $v_id );
	$title = $feature ? $feature : $video['tv_title'];
	$sub = $feature ? $video['tv_title'] : '';
	$debuginfo = !empty($debuginfo) ? ' ('.$debuginfo.') ': '';
?>
	<div class="span2 video" id="<?= $video['tv_id']?>">
		<a href="<?= BASEURL.$video['url'] ?>">
			<img src="http://video.ukm.no/<?= $video['tv_img'] ?>" />
			<h5><?= $title.$debuginfo ?></h5>
			<?= $sub ?>
		</a>
		<a href="<?= $video['category_link'] ?>">
			<div class="kat"><?= $video['tv_category'] ?></div>
		</a>
	</div>
<?php
} ?>