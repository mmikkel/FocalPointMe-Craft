<?php
/**
 * FocalPoint Me! plugin for Craft CMS 4.x
 *
 * Less clickin' to get focused
 *
 * @link      https://vaersaagod.no
 * @copyright Copyright (c) 2020 Mats Mikkel Rummelhoff
 */

namespace mmikkel\focalpointme\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class FocalpointMeAsset
 *
 * @author    Mats Mikkel Rummelhoff
 * @package   FocalpointMe
 * @since     1.0.0
 *
 */
class FocalpointMeAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->sourcePath = "@mmikkel/focalpointme/assetbundles/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'focalpointme.js',
        ];

        $this->css = [
            'focalpointme.css',
        ];

        parent::init();
    }
}
