<?php

require_once('UKMconfig.inc.php');

header('Access-Control-Allow-Headers: true');
header('Access-Control-Allow-Origin: http://' . UKM_HOSTNAME);
header('Access-Control-Request-Method: OPTIONS, HEAD, GET, POST, PUT, PATCH, DELETE');
header('Access-Control-Allow-Credentials: true');

if(!isset($_GET['file'])&&!isset($_GET['cron']))
	die(json_encode(array('success'=>false, 'cron_id' =>$_POST['cron_id'])));

require_once('UKM/tv.class.php');
$id = isset($_GET['file']) ? $_GET['file'] : $_GET['cron'];
$TV = new tv($id);
$TV->videofile();

if(!$TV->id)
	die(json_encode(array('success'=>false, 'cron_id'=>$_GET['cron_id'])));

die($TV->json(array('success'=>true, 'cron_id'=>$_POST['cron_id'])));


/*
require_once('inc/config.inc.php');



if(isset($_GET['cron'])) {
	$test = new SQL("SELECT `video_file` FROM `ukm_standalone_video` WHERE `cron_id` = '#id'",
			array('id' => $_GET['cron']));
	$test = $test->run();
	if(!$test || mysql_num_rows( $test ) == 0)
	die(json_encode(array('success'=>false, 'cron_id' =>$_POST['cron_id'])));
	
	$row = mysql_fetch_assoc($test);
	$_GET['file'] = $row['video_file'];
}

$test = new SQL("SELECT `tv_id` FROM `ukm_tv_files` WHERE `tv_file` LIKE '%#file'",
			array('file' => $_GET['file']));
$test = $test->run();
if(!$test || mysql_num_rows( $test ) == 0)
	die(json_encode(array('success'=>false, 'cron_id' =>$_POST['cron_id'])));

$row = mysql_fetch_assoc($test);
$film = videoInfo($row['tv_id']);

die(json_encode(array('success' 	=> true,
					  'tv_full_url'	=>$film['full_url'],
					  'tv_url'		=>$film['url'],
					  'tv_title'	=>$film['tv_title'],
					  'cron_id'		=>$_POST['cron_id'])));
?>
*/