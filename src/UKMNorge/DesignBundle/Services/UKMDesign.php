<?php

namespace App\UKMNorge\UKMDesign\Services;

use Symfony\Component\HttpKernel\Config\FileLocator;
use UKMNorge\Design\Image;
use UKMNorge\Design\Sitemap\Section;
use UKMNorge\Design\UKMDesign as UKMNorgeUKMDesign;
use UKMNorge\Design\YamlLoader;

class UKMDesign extends UKMNorgeUKMDesign {

    public function __construct(FileLocator $fileLocator, $kernel_cache_dir, $kernel_root_dir)
    {
        require_once('UKMconfig.inc.php');
        static::setCurrentSection(
            new Section(
                'tv',
                '//tv.'. UKM_HOSTNAME,
                'UKM-TV'
            )
        );

        // Opprett cache-mappe om den ikke finnes
        try {
            $fileLocator->locate($kernel_cache_dir.'/ukmdesignbundle/');
        } catch( \InvalidArgumentException $e ) {
            mkdir( $kernel_cache_dir .'/ukmdesignbundle/', 0777, true );
        }

        $yamlLoader = new YamlLoader(
            $fileLocator->locate($kernel_cache_dir.'/ukmdesignbundle/'),
            $fileLocator->locate($kernel_root_dir.'/vendor/ukmnorge/design/Resources/config/')
        );
        static::_initUKMDesign( $yamlLoader );

    }

    /**
     * Sett opp standard-data i UKMDesign
     * 
     * Basert på konfig - setter standard-data for SEO blant annet
     *
     * @return void
     */
    private static function _initUKMDesign( $yamlLoader )
    {
        static::init( $yamlLoader );
        static::getHeader()::getSeo()
            ->setImage(
                new Image(
                    static::getConfig('SEOdefaults.image.url'),
                    intval(static::getConfig('SEOdefaults.image.width')),
                    intval(static::getConfig('SEOdefaults.image.height')),
                    static::getConfig('SEOdefaults.image.type')
                )
            )
            ->setSiteName(static::getConfig('SEOdefaults.site_name'))
            ->setType('website')
            ->setTitle(static::getConfig('SEOdefaults.title'))
            ->setDescription(static::getConfig('slogan'))
            ->setAuthor(static::getConfig('SEOdefaults.author'))
            ->setFBAdmins(static::getConfig('facebook.admins'))
            ->setFBAppId(static::getConfig('facebook.app_id'))
            ->setGoogleSiteVerification(static::getConfig('google.site_verification'));
    }
}