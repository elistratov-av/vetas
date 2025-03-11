<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\OrganizationTypeActiveDataProvider;
use yii\rest\Action;

class OrganizationTypeController extends EntityController
{
    public $dataProviderClass = OrganizationTypeActiveDataProvider::class;

    /**
     * @param Action $action
     * @param \app\modules\v1\models\ActiveDataFilter $filter
     * @return \app\modules\v1\models\ActiveDataProvider|object|void|null
     */
    public function prepareDataProvider(Action $action, $filter) {
        $dataProvider = parent::prepareDataProvider($action, $filter);

        // фиксы для приютов - не выводить в списке организаций приюты
        // и сами organization_type, у которых is_tech === true
        $dataProvider->query->andWhere(['=', 'organization_type.is_tech', false])->indexBy('id')
        ;

        return $dataProvider;
    }
}
