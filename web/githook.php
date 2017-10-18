<?php
error_reporting(E_ALL);

if(!isset($_POST['payload'])) {
    die('No payload');
}

$payload = json_decode( $_POST['payload'] );
$repo = $payload->repository->name;
if( empty($repo) || $repo != 'UKMTV' ) { 
    die('Invalid payload');
}

$exec = "/home/ukmtv/private_shell/github-pull.sh /home/ukmtv/public_html/";

error_log('GITHUB: '. $exec);
$output = shell_exec($exec);

echo($output);
?>
