<?php
/**
 * FocalPoint Me! plugin for Craft CMS 3.x
 *
 * Less clickin' to get focused
 *
 * @link      https://vaersaagod.no
 * @copyright Copyright (c) 2020 Mats Mikkel Rummelhoff
 */

namespace mmikkel\focalpointme\controllers;

use craft\elements\Asset;
use mmikkel\focalpointme\FocalpointMe;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author    Mats Mikkel Rummelhoff
 * @package   FocalpointMe
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    /**
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetAssetFocalPoint(): Response
    {
        $this->requireAcceptsJson();
        $this->requireCpRequest();
        $request = Craft::$app->getRequest();
        $assetId = $request->getRequiredParam('assetId');
        /** @var Asset $asset */
        $asset = Craft::$app->getAssets()->getAssetById((int)$assetId);
        if (!$asset) {
            throw new NotFoundHttpException();
        }
        return $this->asJson([
            'focalPoint' => $asset->focalPoint,
        ]);
    }
}
