<?php

namespace App\UKMNorge\UKMDesign\Services;

use UKMNorge\Design\Sitemap\Section;
use UKMNorge\Design\UKMDesign as UKMNorgeUKMDesign;

class UKMDesign extends UKMNorgeUKMDesign {
    public function __construct()
    {
        require_once('UKMconfig.inc.php');
        static::setCurrentSection(
            new Section(
                'delta',
                'https://delta.'. UKM_HOSTNAME,
                'Delta på UKM'
            )
        );
    }
}