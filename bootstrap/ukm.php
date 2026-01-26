<?php

/**
 * Initialize UKMNorge libraries
 * These are included via PHP's include_path in php.ini
 */

if (!function_exists('ukm_initialized')) {
    function ukm_initialized() {
        return true;
    }

    // Set up include path for UKM libraries
    set_include_path(get_include_path() . PATH_SEPARATOR . '/etc/php-includes');

    // Load UKM Autoloader and config
    require_once('UKM/Autoloader.php');
    require_once('UKMconfig.inc.php');
}
