<?php
/**
 * FocalPoint Me! plugin for Craft CMS 3.x
 *
 * Less clickin' to get focused
 *
 * @link      https://vaersaagod.no
 * @copyright Copyright (c) 2020 Mats Mikkel Rummelhoff
 */

namespace mmikkel\focalpointme;

use Craft;
use craft\base\Plugin;
use craft\events\TemplateEvent;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;

use mmikkel\focalpointme\assetbundles\FocalpointMeAsset;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class FocalpointMe
 *
 * @author    Mats Mikkel Rummelhoff
 * @package   FocalpointMe
 * @since     1.0.0
 *
 */
class FocalpointMe extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var FocalpointMe
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function () {
                $this->doIt();
            }
        );

        Craft::info(
            Craft::t(
                'focal-point-me',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
    protected function doIt()
    {

        $request = Craft::$app->getRequest();
        $user = Craft::$app->getUser()->getIdentity();

        if (!$request->getIsCpRequest() || $request->getIsSiteRequest() || $request->getIsConsoleRequest() || !$user || !$user->can('accessCp')) {
            return;
        }

        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            function (TemplateEvent $event) {
                try {
                    Craft::$app->getView()->registerAssetBundle(FocalpointMeAsset::class);
                } catch (InvalidConfigException $e) {
                    Craft::error(
                        'Error registering AssetBundle - '.$e->getMessage(),
                        __METHOD__
                    );
                }
            }
        );
    }

}
