<?php
/**
 * FocalPoint Me! plugin for Craft CMS 4.x
 *
 * Less clickin' to get focused
 *
 * @link      https://vaersaagod.no
 * @copyright Copyright (c) 2020 Mats Mikkel Rummelhoff
 */

namespace mmikkel\focalpointme;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\controllers\ElementsController;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use craft\elements\db\ElementQuery;
use craft\events\DefineHtmlEvent;
use craft\events\PopulateElementEvent;
use craft\events\TemplateEvent;
use craft\helpers\Json;
use craft\web\Controller;
use craft\web\View;

use yii\base\Action;
use yii\base\ActionEvent;
use yii\base\Event;

use mmikkel\focalpointme\assetbundles\FocalpointMeAsset;

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

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!Craft::$app->getRequest()->getIsCpRequest() || Craft::$app->getRequest()->getIsConsoleRequest() || Craft::$app->getRequest()->getIsLoginRequest()) {
            return;
        }

        // Register asset bundle
        Event::on(
            Asset::class,
            Element::EVENT_DEFINE_META_FIELDS_HTML,
            function (DefineHtmlEvent $event) {
                if ($event->static) {
                    return;
                }
                $element = $event->sender;
                if (!$element instanceof Asset || $element->kind !== Asset::KIND_IMAGE || !$element->id) {
                    return;
                }
                // Inject the current focal point in an HTML commenet
                $focalPoint = Json::encode($element->getFocalPoint() ?? ['x' => 0.5, 'y' => 0.5]);
                $event->html .= "<!-- fp:$element->id:$focalPoint -->";
                Craft::$app->getView()->registerAssetBundle(FocalpointMeAsset::class);
            }
        );

        // Override the `focalPoint` attribute from request params when an Asset is being saved
        Event::on(
            Controller::class,
            \yii\web\Controller::EVENT_BEFORE_ACTION,
            static function (ActionEvent $event) {
                $action = $event->action;
                if (!$action instanceof Action) {
                    return;
                }
                $controller = $action->controller;
                if (!$controller instanceof ElementsController || $action->id !== 'save') {
                    return;
                }
                if (!$focalPoint = $controller->request->getBodyParam('focalPoint')) {
                    return;
                }
                Event::on(
                    AssetQuery::class,
                    ElementQuery::EVENT_AFTER_POPULATE_ELEMENT,
                    static function (PopulateElementEvent $event) use ($focalPoint) {
                        $element = $event->element;
                        if (!$element instanceof Asset) {
                            return;
                        }
                        $element->setFocalPoint($focalPoint);
                    }
                );
            }
        );
    }

}
