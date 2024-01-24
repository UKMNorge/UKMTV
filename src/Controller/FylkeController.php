<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Filmer;
use UKMNorge\Filmer\UKMTV\Tags\Tag;
use UKMNorge\Filmer\UKMTV\Tags\Tags;
use UKMNorge\Geografi\Fylker;

require_once('UKM/Autoloader.php');
require_once('UKMconfig.inc.php');


class FylkeController extends AbstractController
{
    /**
     * List ut alle fylker
     *
     * @return 
     */
    public function fylker()
    {
        return $this->render('Fylke/Fylker.html.twig', ['fylker' => Fylker::getAllInkludertDeaktiverte(), 'ukmHostname' => UKM_HOSTNAME]);
    }

    /**
     * Hent ut info om år fra et fylke, samt lokalarrangementer
     *
     * @param String $fylkekey
     * @return void
     */
    public function fylke(String $fylkekey)
    {   
        $fylke = Fylker::getByLink($fylkekey);

        $sesonger = [];
        for ($sesong = 2009; $sesong < intval(date('Y') + 1); $sesong++) {
            $tags = [
                new Tag('arrangement_type', Tags::getArrangementTypeId('fylke')),
                $this->_getFylkeTag($fylke),
                new Tag('sesong', $sesong)
            ];

            if (Filmer::harTagsFilmer($tags)) {
                $sesonger[] = $sesong;
            }
        }

        $kommuner = [];
        foreach( $fylke->getKommuner()->getAll() as $kommune ) {
            
            if( !empty($kommune->getTidligereIdList())) {
                $kommuneId = explode(',', $kommune->getTidligereIdList());
                $kommuneId[] = $kommune->getId();
            } else {
                $kommuneId = $kommune->getId();
            }
            
            $tags = [
                new Tag('arrangement_type', Tags::getArrangementTypeId('kommune') ),
                new Tag('kommune', $kommuneId)
            ];
            
            if( Filmer::harTagsFilmer($tags)) {
                $kommuner[] = $kommune;
            }
        }

        return $this->render(
            'Fylke/Fylke.html.twig',
            [
                'fylke' => $fylke,
                'sesonger' => $sesonger,
                'kommuner' => $kommuner,
                'ukmHostname' => UKM_HOSTNAME
            ]
        );
    }

    /**
     * List ut alle filmer for et gitt år
     *
     * @param Int $year
     * @return Response ?
     */
    public function year(String $fylkekey, Int $year)
    {
        $fylke = Fylker::getByLink($fylkekey);

        $filmer = Filmer::getByTags(
            [
                new Tag('arrangement_type', Tags::getArrangementTypeId('fylke')),
                $this->_getFylkeTag($fylke),
                new Tag('sesong', $year)
            ]
        );

        return $this->render(
            'Fylke/Filmer.html.twig',
            [
                'filmer' => $filmer,
                'fylke' => $fylke,
                'year' => $year,
                'ukmHostname' => UKM_HOSTNAME
            ]
        );
    }

    /**
     * Hent tag for gitt fylke
     *
     * @param Fylke $fylke
     * @return Tag
     */
    private function _getFylkeTag($fylke) {
        if( $fylke->harOvertatt() ) {
            $fylkeId = array_keys($fylke->getOvertattFor());
            $fylkeId[] = $fylke->getId();
        } else {
            $fylkeId = $fylke->getId();
        }

        return new Tag('fylke', $fylkeId);
    }
}
