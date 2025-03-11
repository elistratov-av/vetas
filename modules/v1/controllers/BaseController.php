<?php
namespace app\modules\v1\controllers;

use app\modules\v1\actions\GetRelationAction;
use app\modules\v1\actions\UpdateAction;
use app\modules\v1\actions\ViewAction;
use app\common\components\entity\EntityResourceCache;
use app\common\components\JwtHttpBearerAuth;
use app\common\components\LegacyApiQuery;
use app\modules\v1\models\ActiveDataFilter;
use app\modules\v1\models\ActiveDataProvider;
use app\modules\v1\models\EntityResource;
use yii\base\InvalidConfigException;
use yii\filters\Cors;
use yii\rest\Action;
use yii\rest\IndexAction;
use yii\web\Response;
use yii\rest\ActiveController;
use app\common\components\entity\EntityInstance;
use app\common\components\entity\EntityResourceFactory;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class BaseController extends ActiveController
{
    public $modelClass = EntityResource::class;
    public $dataProviderClass = ActiveDataProvider::class;

    public $serializer = [
        'class'     => 'app\common\components\Serializer',
        'pluralize' => false,
        'prepareMemberName' => ['app\common\components\Serializer', 'var2member']
    ];

    protected function verbs()
    {
        return [
            'index'  => ['GET', 'HEAD'],
            'view'   => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();

        $actions['create'] = [
            'class' => 'tuyakhov\jsonapi\actions\CreateAction',
            'modelClass' => $this->modelClass,
            'scenario' => 'insert'
        ];

        $actions['view'] = [
            'class' => ViewAction::class,
            'modelClass' => $this->modelClass,
        ];

        $actions['update'] = [
            'class' => UpdateAction::class,
            'modelClass' => $this->modelClass,
            'scenario' => 'update'
        ];

        $actions['index'] = [
            'class' => 'yii\rest\IndexAction',
            'modelClass' => $this->modelClass,
            'dataFilter' => [
                'class' => 'app\modules\v1\models\ActiveDataFilter',
                'searchModel' => $this->modelClass
            ],
            'prepareDataProvider' => function(IndexAction $action, $filter) {
                $data = EntityResourceCache::get();
                return empty($data) ? $this->prepareDataProvider($action, $filter) : $data;
            }
        ];

        $actions['getrel'] = [
            'class' => GetRelationAction::class,
            'modelClass' => $this->modelClass,
            'dataFilter' => [
                'class' => 'app\modules\v1\models\ActiveDataFilter',
                'searchModel' => $this->modelClass
            ]
        ];

        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        $behaviors['cors'] = [
            'class' => Cors::class,
        ];

        $behaviors['http_authenticator'] = [
            'class'  => JwtHttpBearerAuth::class,
            'except' => ['options'],
        ];

        $behaviors['contentNegotiator']['formats'] = [
            'application/json'         => Response::FORMAT_JSON,
            'application/vnd.api+json' => Response::FORMAT_JSON,
        ];

        return $behaviors;
    }

    /**
     * @param Action $action
     * @param ActiveDataFilter $filter
     * @return ActiveDataProvider|null|object
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function prepareDataProvider(Action $action, $filter) {
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;
        $query = $modelClass::find();
        $requestParams = \Yii::$app->getRequest()->getQueryParams();

        if ($filter) {
            $filter->prepareQuery($query);
        }

        LegacyApiQuery::$queryParams = $requestParams;

        $config = [
            'class' => $this->dataProviderClass,
            'query' => $query,
            'pagination' => [
                'class' => 'app\common\components\Pagination',
                'forcePageParam' => false,
                'defaultPageSize' => LegacyApiQuery::getPaginationLimit(),
                'page' => LegacyApiQuery::getPaginationOffset()
            ],
        ];

        $sortField = LegacyApiQuery::getSortField();

        if ($sortField && stripos($sortField, '.')) {
            $this->prepareRelativeSortQuery($sortField, $query);
            $config['sort'] = false;
        }
        elseif ($sortField) {
            $config['sort'] = [
                'defaultOrder' => [
                    $sortField => LegacyApiQuery::getSortDirection()
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

    /**
     * @param $sortField
     * @return void
     * @throws BadRequestHttpException
     */
    private function prepareRelativeSortQuery($sortField, ActiveQuery & $query)
    {
        list($table, $field) = preg_split('/\./', $sortField);

        $table = EntityInstance::entityNameFromTypeName($table);

        $table = strtolower($table);
        $field = strtolower($field);

        /** @var  $modelClass BaseActiveRecord */
        $modelClass = $this->modelClass;
        $src_table = $modelClass->entityInstance->getTableName();

        $once_rels = $modelClass->entityInstance->getRelationships();
        $rel_tables = ArrayHelper::getColumn($once_rels, 'link');

        if(!in_array($table, $rel_tables))
            throw new BadRequestHttpException("Текущая сущность не связана с $table");

        $via = array_filter($once_rels, function ($x) use ($table) {return $x['link'] === $table;});
        $via = array_shift($via);

        try {
            $req_resource = EntityResourceFactory::getResource($table);
            $table = $req_resource->entityInstance->getTableName();
        } catch (NotInstantiableException | InvalidConfigException $e) {
            throw new BadRequestHttpException("Сущность $table не найдена");
        }

        $resource_attrs = ArrayHelper::getColumn($req_resource->entityInstance->getAttributes(), 'name');
        $resource_attrs[] = 'id';
        EntityResourceFactory::getResource($modelClass->entityInstance->getAliasName());//костыль для возврата контекста ресурса

        if(!in_array($field, $resource_attrs))
            throw new BadRequestHttpException("У сущности $table отсуствует атрибут $field");

        $query->leftJoin($table, '[[' . $src_table . ']].[[' . $via['property'] . ']] = [[' . $table . ']].[[' . 'id' . ']]')
            ->addGroupBy(['[[' . $src_table . ']]' . '.id', $table . '.id'])
            ->orderBy(["[[$table]].[[$field]]" => LegacyApiQuery::getSortDirection()]);

        return;
    }
}
