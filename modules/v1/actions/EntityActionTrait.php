<?php

namespace app\modules\v1\actions;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use app\common\components\entity\EntityResourceFactory;
use app\modules\v1\models\EntityResource;

/**
 * Trait EntityActionTrait
 * @package app\common\actions
 */
trait EntityActionTrait
{
    /**
     * @param Request $req
     * @return null
     * @throws \yii\base\InvalidConfigException
     */
    protected function getEntityNameFromRequest(Request $req) {
        $module = Yii::$app->controller->module;
        $module_id = $module->id;

        $path = $req->getPathInfo();
        preg_match('/' . $module_id . '\/([a-z-]+)/m', $path, $matches);

        $result = $matches[1] ?? null;

        return $result;
    }

    /**
     * @param $id
     * @return null|static|EntityResource
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getObject($id)
    {
        $parent_entity = $this->getEntityNameFromRequest(Yii::$app->request);
        $parent_resource = EntityResourceFactory::getResource($parent_entity);

        if (!$object = $parent_resource::findOne(['id' => $id])) {
            throw new NotFoundHttpException("Ресурс {$parent_entity} #{$id} не найден");
        }

        return $object;
    }
}
