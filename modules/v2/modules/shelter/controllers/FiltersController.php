<?php

namespace app\modules\v2\modules\shelter\controllers;

use app\models\db\Areas;
use app\models\db\Aviary;
use app\models\db\Organizations;
use app\models\db\PetRefColor;
use app\models\db\Pets;
use app\models\db\ShelterGuests;
use app\models\db\Species;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\shelter\models\ShelterSearchModel;
use yii\base\InvalidConfigException;
use yii\db\Query;

class FiltersController extends BaseController
{
    public function actionAreaList($filtered = null)
    {
        $query = (new Query())
            ->select('ar.id, ar.name, ar.short_name')
            ->from(Areas::tableName() . ' ar')
            ->leftJoin(['o' => Organizations::tableName()], 'ar.id = o.id_area')
            // ->rightJoin(['o' => Organizations::tableName()], 'ar.id = o.id_area')
            // ->rightJoin(['sg' => ShelterGuests::tableName()], 'o.id = sg.id_organization')
            ->where(['not', ['ar.id' => null]])
            // ->andWhere(['sg.id_organization' => $this->id_organization])
            ->groupBy('ar.id')
            ->orderBy('ar.id');

        // для получения списка в фильтрах Приютов
        if ($filtered) {
            $organization_ids = $this->getOrganizationIds();
            $query->andFilterWhere(['o.id' => $organization_ids]);
        }

        $result = [];
        foreach ($query->all() as $item) {
            $result[] = [
                'value' => $item['id'],
                'label' => !empty($item['short_name'])
                    ? $item['short_name']
                    : $this->parseStringToShortName($item['name']),
            ];
        }

        return $result;
    }

    public function actionAviaryList()
    {
        return (new Query())
            ->select('av.id as value, av.title as label, av.description as description')
            ->from(Aviary::tableName() . ' av')
            ->groupBy('av.id')
            ->orderBy('av.id')
            ->all()
        ;
    }

    public function actionShelterList($filtered = null)
    {
        $query = (new Query())
            ->select('o.id, o.name, o.short_name')
            ->from(Organizations::tableName() . ' o')
            // ->rightJoin(['sg' => ShelterGuests::tableName()], 'o.id = sg.id_organization')
            ->where(['not', ['o.id' => null]])
            // ->andWhere(['sg.id_organization' => $this->id_organization])
            ->groupBy('o.id')
            ->orderBy('o.id');

        // для получения списка в фильтрах Приютов
        if ($filtered) {
            $organization_ids = $this->getOrganizationIds();
            $query->andFilterWhere(['o.id' => $organization_ids]);
        }

        $result = [];
        foreach ($query->all() as $item) {
            $result[] = [
                'value' => $item['id'],
                'label' => $item['short_name'],
            ];
        }

        return $result;
    }

    public function actionSpeciesList()
    {
        $result = [];
        foreach (Species::findAll(['name' => ['кошки', 'собаки']]) as $model) {
            $result[] = [
                'value' => $model->id,
                'label' => $model->name,
            ];
        }

        return $result;
    }

    public function actionColorList()
    {
        $result = [];
        foreach (PetRefColor::find()->all() as $model) {
            $result[] = [
                'value' => $model->id,
                'label' => $model->title,
            ];
        }

        return $result;
    }

    public function actionStatusList()
    {
        $result = [];
        foreach (ShelterGuests::STATUSES as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value,
            ];
        }

        return $result;
    }

    public function actionGenderList()
    {
        $result = [];
        foreach (Pets::GENDER_TYPES as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => ucfirst($value),
            ];
        }

        return $result;
    }

    protected function getUserOrganizationsIds()
    {
        /** @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        if ($user->specialist === null || empty($user->specialist->id_organization)) {
            throw new InvalidConfigException();
        }

        $this->id_organization = $user->specialist->id_organization;
        /** @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        if ($user->specialist === null || empty($user->specialist->id_organization)) {
            throw new InvalidConfigException();
        }

        $this->id_organization = $user->specialist->id_organization;
    }

    protected function parseStringToShortName($string)
    {
        $result = '';
        $token = strtok($string, ' -');
        do {
            $result .= mb_strtoupper(mb_substr($token, 0, 1));
        } while ($token = strtok(' -'));

        return $result;
    }

    protected function getOrganizationIds()
    {
        if ($organization = \Yii::$app->user->getIdentity()->organization) {
            $organization_ids = array_keys([$organization->id => $organization->id]
                + $organization->getNestedOrganizations(true));

            $organization_ids = (new ShelterSearchModel())->prepareSubQuery([], $organization_ids)
                ->select('id_organization')->groupBy('id_organization')->column();
        }

        return $organization_ids ?? [];
    }
}
