<!DOCTYPE html>
<html>
<head>
<style>
body {
	padding-top: 30px;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, maximum-scale=1.0">

<script>
window.fbAsyncInit = function() {
  FB.init({
    appId      : '141016739323676',
    status     : true, 
    cookie     : true,
	xfbml      : true
  });
};
(function(d){
   var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
   js = d.createElement('script'); js.id = id; js.async = true;
   js.src = "//connect.facebook.net/nb_NO/all.js";
   d.getElementsByTagName('head')[0].appendChild(js);
 }(document));
</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script src="<?= BASEURL ?>js/bootstrap.min.js"></script>

<script src="http://embed.ukm.no/jwplayer.js"></script>
<script type="text/javascript">
 
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-46216680-4']);
  _gaq.push(['_trackPageview']);
 
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
 
</script>
<link href="<?= BASEURL ?>css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?= BASEURL ?>css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
<link href="<?= BASEURL ?>css/tv.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="http://tv.ukm.no/img/favicon.ico" />
<?php
if(isset($_GET['video'])) {
	$TV = new tv($_GET['video']);
	echo $TV->meta;
	echo '<title>'.$TV->title.' @ tv.UKM.no</title>';
} else { ?>
	<title>tv.UKM.no</title>
	
	<meta property="fb:app_id" content="141016739323676"> 
	<meta property="og:type" content="website"> 
	<meta property="og:url" content="http://tv.ukm.no/">
	<meta property="og:image" content="http://tv.ukm.no/img/ukmtv_logo.png">
	<meta property="og:title" content="UKM-TV">
	<meta property="og:description" content="UKM er en åpen arena for alle ungdommer mellom 13 og 20 år, hvor man kan delta med akkurat det man vil, uansett erfaring! UKM-TV er UKMs egen TV-kanal, hvor vi har samlet videoklipp fra flere år tilbake. ">
	<meta property="video:actor" content="http://facebook.com/UKMNorge">
	<meta property="video:tag" content="UKM-TV UKM UKM Norge">
<?php
} ?>
</head>
<body>
<div class="well opacity95 center-container" id="ukmtv_container">
