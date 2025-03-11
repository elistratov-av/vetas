<?php

namespace app\modules\v1\actions;

use yii\db\ActiveRecordInterface;
use yii\web\NotFoundHttpException;

class UpdateAction extends \tuyakhov\jsonapi\actions\UpdateAction
{
    /**
     * @param string $id
     * @return mixed|ActiveRecordInterface|static
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if ($this->findModel !== null) {
            return call_user_func($this->findModel, $id, $this);
        }

        /* @var $modelClass ActiveRecordInterface */
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $model = $modelClass::findOne(array_combine($keys, $values));
            }
        } elseif ($id !== null) {
            $model = $modelClass::findOne(['id' => $id]);
        }

        if (isset($model)) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found: $id");
    }
}
