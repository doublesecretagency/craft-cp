<?php
/**
 * CP Helper for Double Secret Agency
 *
 * Internal tools for the Craft CMS control panel.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2023 Double Secret Agency
 */

namespace doublesecretagency\cp\web\twig;

use craft\helpers\UrlHelper;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Twig extension
 * @since 1.0.0
 */
class Extension extends AbstractExtension implements GlobalsInterface
{

    /**
     * @inheritdoc
     */
    public function getGlobals(): array
    {
        // Initialize globals
        $globals = [];

        // Set related URLs
        $globals['assetsUrl']    = UrlHelper::siteUrl('assets');
        $globals['resourcesUrl'] = UrlHelper::siteUrl('resources');

        // Return globals
        return $globals;
    }

}
