<?php

error_reporting(E_ALL);
ini_set('display_errors',true);

require_once('UKM/sql.class.php');

$sql = new SQL("SELECT * FROM `ukm_tv_files`");
$res = $sql->run();

while( $r = SQL::fetch( $res ) ) {
    $tv_id = $r['tv_id'];
    
    $tags = $r['tv_tags'];
    $all_tags = explode('|', $tags);    
    
    foreach( $all_tags as $tag ) {
        if( empty( $tag ) ) 
            continue;
            
        $tag_data = explode('_', $tag );
        
        switch( $tag_data[0] ) {
            case 'b':   $type = 'innslag';      break;
            case 'p':   $type = 'person';       break;
            case 'k':   $type = 'kommune';      break;
            case 'f':   $type = 'fylke';        break;
            case 's':   $type = 'sesong';       break;
            case 'pl':   $type = 'monstring';   break;
            case 'p':   $type = 'person';       break;
            case 't':
                $type = 'type';
                    switch( $tag_data[1] ) {
                        case 'land':    $tag_data[1] = 3;   break;
                        case 'fylke':   $tag_data[1] = 2;   break;
                        case 'kommune':   $tag_data[1] = 1;   break;
                    }
                break;
            default:
                die('Mangler støtte for '. $tag_data[0]);
        }
        $SQLins = new SQLins('ukm_tv_tags');
        $SQLins->add('tv_id', $tv_id);
        $SQLins->add('type', $type);
        $SQLins->add('foreign_id', $tag_data[1]);
        $SQLins->add('id', $tv_id.$type.$tag_data[1] );
        $SQLins->run();
    }
}

echo 'done';