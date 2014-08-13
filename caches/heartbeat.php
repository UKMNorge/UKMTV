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
if ( !$cache_id ) {
	$sql = new SQLins('ukm_tv_caches_caches');
	$sql->add('ip', $cache_ip);
	$sql->add('status', 'OK');
	$results = $sql->run();
	$cache_id = $sql->insid();
} else {
	$select_sql = new SQL("SELECT id FROM `ukm_tv_caches_caches` WHERE `id`='#id'", array('id' => $cache_id));
	$res = $select_sql->run( $select_sql );
	$res = mysql_fetch_assoc( $res );
	if ( !$res ) {
		error_log("Got heartbeat from unknown id. ID=$cache_id, ip=$cache_ip.");
		$insert_new = new SQLins('ukm_tv_caches_caches');
		$insert_new->add('id', $cache_id);
		$insert_new->add('ip', $cache_ip);
		$insert_new->add('status', 'OK');
		$insert_new->run();
	}
	error_log("Updating status for ip $cache_ip");
	$sql = new SQLins('ukm_tv_caches_caches', array('id' => $cache_id));
	$sql->add('ip', $cache_ip);
	$sql->run();
}

die(json_encode(array(
	'cache_id' => $cache_id,
	'status' => $status,
	'ip' => $cache_ip,
)));
