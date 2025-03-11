<?php

namespace app\modules\v1\controllers;

use app\common\components\entity\EntityManager;
use app\common\components\entity\EntityResourceFactory;
use app\common\components\LegacyApiQuery;
use app\modules\v1\models\ActiveDataFilter;
use app\modules\v1\models\ActiveDataProvider;
use Yii;
use yii\db\Query;
use yii\web\NotFoundHttpException;

class VisitsController extends BaseController
{
    /**
     * @param $action
     * @return bool
     * @throws NotFoundHttpException
     * @throws \app\common\components\entity\EntityException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        switch ($action->id) {
            case 'service-types-description':
                $entity_name = 'description-types';

                /** @var EntityManager $entityManager */
                $entityManager = \Yii::$container->get('entityManager');

                if (is_string($this->modelClass)) {
                    if ($entityInstance = $entityManager->getEntity($entity_name)) {
                        \Yii::$container->set('entityInstance', function ($container) use ($entity_name) {
                            $manager = $container->get('entityManager');
                            $entity = $manager->getEntity($entity_name);

                            return $entity;
                        });

                        $this->modelClass = \Yii::$container->get('entityResource');
                    } else {
                        throw new NotFoundHttpException();
                    }
                }
                break;

            default:
                break;
        }

        return parent::beforeAction($action);
    }

    /**
     * @param int $id
     * @return ActiveDataProvider|mixed|object
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionServiceTypesDescription(int $id)
    {
        $visit = (new Query())->from('visits')
            ->where(['id' => $id])
            ->exists();
        if (!$visit) {
            throw new NotFoundHttpException("Не найден ресурс visit #{$id}");
        }

        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        $dataFilter = new ActiveDataFilter();
        $dataFilter->setSearchModel(EntityResourceFactory::getResource('description-types'));
        if ($dataFilter->load($requestParams)) {
            $filter = $dataFilter->build();

            if ($filter === false) {
                return $dataFilter;
            }
        }

        $query = $this->modelClass::find();
        $table = 'visits_available_description_types';
        $query->from($table)
            ->select('[[' . $table . ']].[[id]], MAX([[' . $table . ']].[[name]]) as name, MAX([[' . $table . ']].[[entity_type]]) as entity_type')
            ->groupBy('[[' . $table . ']].[[id]]')
            ->where(['[[' . $table . ']].[[id_visit]]' => $id]);

        if ($filter) {
            $filter->prepareQuery($query);
        }

        $dataProvider = null;

        LegacyApiQuery::$queryParams = $requestParams;

        $config = [
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'class' => 'app\common\components\Pagination',
                'forcePageParam' => false,
                'defaultPageSize' => LegacyApiQuery::getPaginationLimit(),
                'page' => LegacyApiQuery::getPaginationOffset()
            ],
        ];

        if (LegacyApiQuery::getSortField()) {
            $config['sort'] = [
                'defaultOrder' => [
                    LegacyApiQuery::getSortField() => LegacyApiQuery::getSortDirection()
                ]
            ];
        } else {
            $config['sort'] = [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ];
        }

        $dataProvider = \Yii::createObject($config);

        return $dataProvider;
    }
}
