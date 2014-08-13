<?php

require_once('UKMconfig.inc.php');
require_once('UKM/sql.class.php');

if( !isset( $_POST['cache_id'] ) || !isset( $_POST['cron_id'] ) ) {
    header( 'HTTP/1.1 450 BAD REQUEST' );
    die( json_encode( array('success' => false) ) );
}

$SQLins = new SQLins('ukm_tv_caches_caches_with_files');
$SQLins->add('cache_id', $_POST['cache_id']);
$SQLins->add('cron_id', $_POST['cron_id']);
$SQLins->run();

die( json_encode( array('success' => true) ) );