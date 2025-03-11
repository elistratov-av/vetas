<?php

namespace app\modules\adminv\controllers\audit;

use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use app\modules\admin\models\Contacts;
use app\modules\adminv\controllers\AdminController;
use app\modules\adminv\models\search\PetOwnersSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Class OwnersController
 * @package app\modules\adminv\controllers
 */
class PetOwnersController extends AdminController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PetOwnersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProfile($id)
    {
        if (!$model = PetOwners::find()->where(['id' => $id])->one()) {
            throw new NotFoundHttpException('Владелец не найден');
        }

        $query = Contacts::find()->andWhere([
            'entity_id' => $id,
            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $contactTypes = ContactTypes::typeOptions(ContactTypes::ENTITY_TYPE_PET_OWNER);

        return $this->render('profile', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'contactTypes' => $contactTypes,
        ]);
    }
}
