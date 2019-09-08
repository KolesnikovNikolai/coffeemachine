<?php

namespace app\controllers;

use app\models\Nominal;
use app\models\Product;
use app\services\SiteService;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render(
            'index',
            (new SiteService())->getData()
        );
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionAddDeposit(int $id)
    {
        $nominal = Nominal::findOne($id);
        if ($nominal === null) {
            throw new NotFoundHttpException();
        }
        (new SiteService())->addDeposit($nominal);
        return $this->redirect(Yii::$app->homeUrl);
    }

    /**
     * @return Response
     */
    public function actionChange()
    {
        (new SiteService())->makeChange();
        return $this->redirect(Yii::$app->homeUrl);
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionBuy(int $id)
    {
        $product = Product::findOne($id);
        if ($product === null) {
            throw new NotFoundHttpException();
        }
        $service = new SiteService();
        $service->buyProduct($product);
        return $this->redirect(Yii::$app->homeUrl);
    }
}
