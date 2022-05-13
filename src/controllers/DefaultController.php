<?php
/**
 * FocalPoint Me! plugin for Craft CMS 4.x
 *
 * Less clickin' to get focused
 *
 * @link      https://vaersaagod.no
 * @copyright Copyright (c) 2020 Mats Mikkel Rummelhoff
 */

namespace mmikkel\focalpointme\controllers;

use mmikkel\focalpointme\FocalpointMe;

use Craft;
use craft\elements\Asset;
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
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGetFocalPoint(): Response
    {

        $this->requireAcceptsJson();

        $assetId = $this->request->getRequiredBodyParam('assetId');

        /** @var Asset $asset */
        if (!$asset = Asset::find()->id($assetId)->kind(Asset::KIND_IMAGE)->one()) {
            throw new NotFoundHttpException();
        }

        return $this->asJson([
            'focalPoint' => $asset->getFocalPoint(),
        ]);
    }
}
