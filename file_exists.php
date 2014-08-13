<?php
if(!isset($_GET['file']))
	die('Beklager, mangler info');

require_once('UKM/tv.class.php');
$TV = new tv($_GET['file']);

if(!$TV->id)
	die('false');

die('true');/*

require_once('inc/config.inc.php');

$test = new SQL("SELECT `tv_id` FROM `ukm_tv_files` WHERE `tv_file` LIKE '%#file'",
			array('file' => $_GET['file']));
$test = $test->run();
if(!$test || mysql_num_rows( $test ) == 0)
	die('false');
die('true');
*/
?>