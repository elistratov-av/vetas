<?php

namespace app\modules\soap\validators;

use app\modules\soap\models\MosruServices;
use yii\db\ActiveRecord;
use yii\validators\Validator;

/**
 * Проверка передаваемых услуг на возможность вызова на дом
 *
 * Class VisitDateValidator
 * @package app\common\validators
 */
class CallToHomeVisitServicesValidator extends Validator
{
    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $ids = $model->$attribute;

        /** @var MosruServices[] $services */
        $services = MosruServices::find()
            ->where(['id' => $ids])
            ->andWhere(['at_home' => false])
            ->all();

        if (!empty($services)) {
            $msg = "Следующие услуги не доступны для вызова на дом:";
            foreach ($services as $service) {
                $msg .= "\n#{$service->id} {$service->name}";
            }
            $this->addError($model, $attribute, $msg);
        }
    }

}
