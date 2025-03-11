<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\Breeds;
use app\modules\admin\models\Species;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Class SpeciesController
 * @package app\modules\admin\controllers
 */
class SpeciesController extends AdminController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Species::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('species', ['dataProvider' => $dataProvider]);
    }


    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionBreeds()
    {
        /** @var Species|null $species */
        if (!$species = Species::findOne(['id' => Yii::$app->request->get('id', 0)])) {
            throw new NotFoundHttpException("Вид животного #" . Yii::$app->request->get('id', 0) . " не найден");
        }

        $query = Breeds::find()->where(['species_id' => $species->id])->orderBy('name');
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $this->render('breeds', ['dataProvider' => $dataProvider]);
    }
}
