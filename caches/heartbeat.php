<?php

ini_set("log_errors", 1);
ini_set('display_errors', 0);

require_once('UKMconfig.inc.php');
require_once('../tvconfig.php');
require_once('UKM/sql.class.php');

//echo "Here we are...";

// Fetch the data about the cache reporting in
$cache_id = $_POST['cache_id'];
$cache_ip = $_SERVER['REMOTE_ADDR'];
if ( $cache_id == false ) {
	$sql = new SQLins('caches');
	$sql->add('ip', $cache_ip);
	$sql->add('status', 'OK');
	$results = $sql->run();
	$cache_id = $sql->insid();
} else {
	error_log("Updating status for ip $cache_ip");
	$sql = new SQLins('caches', array('id' => $cache_id));
	$sql->add('ip', $cache_ip);
	$sql->run();
}

die(json_encode(array(
	'cache_id' => $cache_id,
	'status' => $status,
	'ip' => $cache_ip,
)));
