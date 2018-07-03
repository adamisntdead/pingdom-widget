<?php
/**
 * Pingdom plugin for Craft CMS 3.x
 *
 * A Widget To Show Stats From Pingdom On The Dashboard
 *
 * @link      https://adamisntdead.com
 * @copyright Copyright (c) 2018 Adam Kelly
 */

namespace adamisntdead\pingdom\assetbundles\pingdomwidgetwidget;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * PingdomWidgetWidgetAsset AssetBundle
 *
 *
 * @author    Adam Kelly
 * @package   Pingdom
 * @since     1.0.0
 */
class PingdomWidgetWidgetAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@adamisntdead/pingdom/assetbundles/pingdomwidgetwidget/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/PingdomWidget.js',
        ];

        $this->css = [
            'css/PingdomWidget.css',
        ];

        parent::init();
    }
}
