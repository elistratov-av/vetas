<?php

namespace app\modules\v1\actions;

use app\common\components\entity\EntityResourceCache;
use app\modules\v1\models\ActiveDataFilter;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class GetRelationAction extends EntityAction
{
    /** @var callable a PHP callable that will be called to prepare a data provider that */
    public $prepareDataProvider;

    /** @var ActiveDataFilter|null */
    public $dataFilter;

    protected $query;

    /**
     * @param $entity
     * @param integer $id
     * @return mixed|null|object|ActiveDataProvider|\yii\data\DataFilter
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\NotFoundHttpException
     */
    public function run($entity, $id)
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        $data = EntityResourceCache::get();
        if (!empty($data)) {
            return $data;
        }

        $currentResource = $this->getObject($id);

        $this->query = $currentResource->getActiveQueryForPluralRelation($entity);

        return $this->prepareDataProvider();
    }

    /**
     * @return mixed|null|object|ActiveDataProvider|\yii\data\DataFilter
     * @throws \yii\base\InvalidConfigException
     */
    protected function prepareDataProvider()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        $filter = null;
        if ($this->dataFilter !== null) {
            $this->dataFilter = Yii::createObject($this->dataFilter);
            if ($this->dataFilter->load($requestParams)) {
                /** @var ActiveDataFilter $filter */
                $filter = $this->dataFilter->build();
                if ($filter === false) {
                    return $this->dataFilter;
                }
            }
        }

        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this, $filter);
        }

        /** @var ActiveQuery $query */
        $query = $this->query;
        if (!empty($filter)) {
            $filter->prepareQuery($query);
        }

        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }
}
