<?php

#require_once('server_upgrade.php');

require_once('UKMconfig.inc.php');
require_once('UKM/tv_files.class.php');
require_once('UKM/innslag.class.php');
require_once('UKM/monstring.class.php');

require_once('inc/header.inc.php');
if(!isset($_GET['video']))
	require_once('inc/nav.inc.php');
else
	require_once('inc/nav-mini.inc.php');

if(isset($_GET['kat']) && file_exists('inc/'.$_GET['kat'].'.inc.php'))
	require_once('inc/'.$_GET['kat'].'.inc.php');
elseif(isset($_GET['video']))
	require_once('inc/video.inc.php');
elseif(isset($_GET['q']))
	require_once('inc/search.inc.php');
else
	require_once('inc/popular.inc.php');
	
require_once('inc/footer.inc.php');
?>
