<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use UKMNorge\Filmer\UKMTV\Filmer;
use UKMNorge\Filmer\UKMTV\Tags\Tag;
use UKMNorge\Filmer\UKMTV\Tags\Tags;
use UKMNorge\Geografi\Kommune;

require_once('UKM/Autoloader.php');

class KommuneController extends AbstractController
{
    /**
     * Hent ut info om år fra et fylke, samt lokalarrangementer
     *
     * @param String $kommunekey
     * @return void
     */
    public function years(String $kommuneid)
    {
        $kommune = new Kommune($kommuneid);

        $sesonger = [];
        for ($sesong = 2009; $sesong < intval(date('Y') + 1); $sesong++) {
            $tags = [
                new Tag('arrangement_type', Tags::getArrangementTypeId('kommune')),
                $this->_getKommuneTag($kommune),
                new Tag('sesong', $sesong)
            ];

            if (Filmer::harTagsFilmer($tags)) {
                $sesonger[] = $sesong;
            }
        }

        return $this->render(
            'Kommune/Years.html.twig',
            [
                'kommune' => $kommune,
                'years' => $sesonger,
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
    public function year(String $kommuneid, Int $year)
    {
        $kommune = new Kommune($kommuneid);

        $filmer = Filmer::getByTags(
            [
                new Tag('arrangement_type', Tags::getArrangementTypeId('kommune')),
                $this->_getKommuneTag($kommune),
                new Tag('sesong', $year)
            ]
        );

        return $this->render(
            'Kommune/Filmer.html.twig',
            [
                'filmer' => $filmer,
                'kommune' => $kommune,
                'year' => $year,
                'ukmHostname' => UKM_HOSTNAME
            ]
        );
    }

    /**
     * Hent kommuneTag
     *
     * @param Kommune $kommune
     * @return Tag
     */
    public function _getKommuneTag(Kommune $kommune)
    {
        if (!empty($kommune->getTidligereIdList())) {
            $kommuneId = explode(',', $kommune->getTidligereIdList());
            $kommuneId[] = $kommune->getId();
        } else {
            $kommuneId = $kommune->getId();
        }

        return new Tag('kommune', $kommuneId);
    }
}
