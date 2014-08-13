<?php
namespace UKMNorge\UKMTVguiBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class FylkeService {

    public function __construct( $container ) {
    
    }

    public function name_to_id( $fylke ) {
        switch( $fylke ) {
            case 'ostfold':        return  1; 
            case 'akershus':       return  2; 
            case 'oslo':           return  3; 
            case 'hedmark':        return  4; 
            case 'oppland':        return  5; 
            case 'buskerud':       return  6; 
            case 'vestfold':       return  7; 
            case 'telemark':       return  8; 
            case 'aust-agder':     return  9; 
            case 'vest-agder':     return  10;
            case 'rogaland':       return  11;
            case 'hordaland':      return  12;
            case 'sognogfjordane': return  14;
            case 'moreogromsdal':  return  15;
            case 'sor-trondelag':  return  16;
            case 'nord-trondelag': return  17;
            case 'nordland':       return  18;
            case 'troms':          return  19;
            case 'finnmark':       return  20;
            case 'testfylke':      return  21;
            case 'svalbard':       return  30;
            case 'internasjonalt': return  31;
            case 'gjester':        return  32;
        }
    }
    
    public function id_to_name( $id ) {
        $id = (int) $id;
        switch( $id ) {
            case 1:  return 'ostfold'; 
            case 2:  return  'akershus';
            case 3:  return  'oslo';
            case 4:  return  'hedmark';
            case 5:  return  'oppland';
            case 6:  return  'buskerud';
            case 7:  return  'vestfold';
            case 8:  return  'telemark';
            case 9:  return  'aust-agder';
            case 10: return  'vest-agder';
            case 11: return  'rogaland';
            case 12: return  'hordaland';
            case 14: return  'sognogfjordane';
            case 15: return  'moreogromsdal';
            case 16: return  'sor-trondelag';
            case 17: return  'nord-trondelag';
            case 18: return  'nordland';
            case 19: return  'troms';
            case 20: return  'finnmark';
            case 21: return  'testfylke';
            case 30: return  'svalbard';
            case 31: return  'internasjonalt';
            case 32: return  'gjester';
        }
    }
    
}