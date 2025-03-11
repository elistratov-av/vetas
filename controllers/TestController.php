<?php


namespace app\controllers;

use app\common\components\entity\EntityResourceFactory;
use app\modules\v1\models\EntityResource;
use app\modules\v1\models\OrganizationResource;
use yii\web\Controller;

class TestController extends Controller
{
    public function actionScheme()
    {
        $entityManager = \Yii::$container->get('entityManager');
        $val = $entityManager->getValidator();
        $res = $val->validate();
        return !$res ? $val->getErrors(): [$res];

        /*$model = OrganizationResource::findOne(1);
        var_dump($model->orgtype->name);*/
    }

    public function actionRelations()
    {
        $entity_name = 'equipments';

        /**
         * @var EntityResource $resource
         */
        $resource = EntityResourceFactory::getResource($entity_name);
        $tmc = array_pop($resource->getResourceRelationships());

        var_dump($tmc->getId());
    }


}