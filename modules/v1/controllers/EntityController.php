<?php

namespace app\modules\v1\controllers;

use app\modules\v1\actions\EntityActionTrait;
use app\common\components\entity\EntityAccessFilter;
use app\common\components\entity\EntityNestedCollection;
use Yii;
use app\common\components\entity\EntityManager;
use app\common\components\entity\EntityResourceFactory;
use app\modules\v1\models\EntityResource;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\IntegrityException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class EntityController extends BaseController
{
    use EntityActionTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors =  parent::behaviors();

//        $behaviors['entityAccess'] = [
//            'class'  => EntityAccessFilter::class,
//            'except' => ['options'],
//        ];

        return $behaviors;
    }

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
        $entity_name = $this->getEntityNameFromRequest(\Yii::$app->request);

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

        return parent::beforeAction($action);
    }

    /**
     * @param $entity
     * @param null $id
     * @return object|ActiveDataProvider
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionGetrel($entity, $id = null)
    {
        $currentResource = $id ? $this->getObject($id) : $this->modelClass;
        EntityResourceFactory::getResource($entity);

        $dataProvider = \Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $currentResource->getActiveQueryForPluralRelation($entity),
        ]);

        return $dataProvider;
    }

    /**
     * @param $id
     * @param string $entity
     * @return EntityResource|bool|mixed|null
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionAddrel($id, string $entity)
    {
        $parent_resource = $this->getObject($id);

        $reltype = $this->modelClass->getRelationType($entity);

        if ($reltype == EntityResource::RELATION_PLURAL) {
            return $this->createRelatedAction($id, $entity);
        }
        /** @var EntityNestedCollection $collection */
        $collection = $this->modelClass->entityInstance->getNestedCollection($entity);
        $resource = EntityResourceFactory::getResource($entity);

        $this->modelClass = $resource;

        if (!$model = $this->findExistModel($resource, $collection->getForeignKey())) {
            $model = $this->runAction('create');
            $data = $model['data'] ?? null;
        } else {
            $data = $model->getAttributes();
        }

        try {
            $parent_resource->attachEntityByRelation($entity, $data);
        } catch (Exception $e) {
            // ignore
        }

        return $model;
    }

    /**
     * @param $id
     * @param string $entity
     * @param $entity_id
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionAddRelById($id, string $entity, $entity_id)
    {
        $resource = $this->getObject($id);

        $entityResource = EntityResourceFactory::getResource($entity);
        if (!$entityObject = $entityResource::findOne($entity_id)) {
            throw new NotFoundHttpException("Ресурс {$entity} #{$entity_id} не найден");
        }

        try {
            $resource->attachEntityByRelation($entity, $entityObject->getAttributes());
        } catch (IntegrityException $e) {
            switch ($e->getCode()) {
                case 23505:
                    //ignore duplicate
                    break;

                default:
                    throw $e;
            }
        }

        Yii::$app->response->setStatusCode(201);
    }

    /**
     * @param $id
     * @param string $entity
     * @param $entity_id
     * @return null|static|EntityResource
     * @throws BadRequestHttpException
     * @throws IntegrityException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionUpdateRelById($id, string $entity, $entity_id)
    {
        $resource = $this->getObject($id);

        $entityResource = EntityResourceFactory::getResource($entity);
        if (!$entityObject = $entityResource::findOne($entity_id)) {
            throw new NotFoundHttpException("Ресурс {$entity} #{$entity_id} не найден");
        }

        if (!$resource->isRelatedTo($entityObject)) {
            throw new BadRequestHttpException("Ошибка в запросе");
        }

        $request = Yii::$app->getRequest();
        $entityObject->load($request->getBodyParams());
        if ($entityObject->save() === false && !$entityObject->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $entityObject;
    }

    /**
     * @param $parent_id
     * @param $entity
     * @param $id
     * @return Response
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelrel($parent_id, $entity, $id = null)
    {
        $parent_resource = $this->getObject($parent_id);

        if (is_null($id)) {
            $parent_resource->unlinkAll($entity, true);
        } else {
            $resource = EntityResourceFactory::getResource($entity);

            if (!$resource = $resource::findOne($id)) {
                throw new NotFoundHttpException("Ресурс {$entity} #{$id} не найден");
            }

            if (!$parent_resource->isRelatedTo($resource)) {
                throw new BadRequestHttpException("Ошибка в запросе");
            }

            switch ($parent_resource->getRelationType($entity)) {
                case EntityResource::RELATION_PLURAL:
                    $resource->delete();
                    break;

                case EntityResource::RELATION_MANY:
                    $parent_resource->detachEntityByRelation($entity, $id);
                    break;

                case EntityResource::RELATION_SINGLE:
                default:
                    throw new BadRequestHttpException("Ошибка в запросе");
            }
        }

        return Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * @param EntityResource $entityResource
     * @param $field
     * @return null|EntityResource
     * @throws \yii\base\InvalidConfigException
     */
    protected function findExistModel(EntityResource $entityResource, $field)
    {
        $request = Yii::$app->getRequest();
        $entityResource->load($request->getBodyParams());

        $model = $entityResource::findOne([$field => $entityResource->$field]);

        return $model;
    }


    /**
     * @param $id
     * @param $relname
     * @return EntityResource|bool
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function createRelatedAction($id, $relname)
    {
        $propname = $this->modelClass->getRelationLinkPropertyName($relname);
        $resource = EntityResourceFactory::getResource($relname);

        $attributes = $this->modelClass->getRelationAdditionalFields($relname);
        $params = Yii::$app->getRequest()->getBodyParams();
        try {
            $resource = $resource->saveRelation($id, $propname, $params, $attributes);
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);

            return $resource;
        } catch (\Exception $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }
    }

    /**
     * Создание связи много-ко-многим
     * - Если в теле запроса передается только id связываемой сущности, то проверяется, существует ли сущность и потом
     *  добавляется связь. При этом если связь между объектами уже существует - ничего не происходи
     * - Если в теле запроса передаются аттрибуты, то сначала создается новая сущность, потом добавляется связь
     *
     *
     * @param integer $id
     * @param string $entity
     * @return mixed|null|static
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\di\NotInstantiableException
     */
    protected function createManyToManyRelation($id, $entity)
    {
        $parentObject = $this->getObject($id);

        $request = Yii::$app->getRequest();

        $resource = EntityResourceFactory::getResource($entity);

        $resourceData = $request->getBodyParam($resource->formName());
        if (isset($resourceData['id'])) {
            if (!$entityObject = $resource::findOne($resourceData['id'])) {
                throw new NotFoundHttpException("Ресурс {$entity} #{$resourceData['id']} не найден");
            }
            if ($parentObject->isRelatedTo($entityObject)) {
                return $entityObject;
            }
            $data = $entityObject->getAttributes();
        } else {
            $this->modelClass = $resource;
            $entityObject = $this->runAction('create');

            // Возвращаем информацию об ошибках, если объект не создан
            if (empty($entityObject['data'])) {
                return $entityObject;
            }

            $data = $entityObject['data'];
        }

        $parentObject->attachEntityByRelation($entity, $data);

        return $entityObject;
    }
}
