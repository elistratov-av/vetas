<?php

namespace app\modules\soap\validators;

use app\modules\soap\models\MosruServices;
use yii\db\ActiveRecord;
use yii\validators\Validator;

/**
 * Проверка наличия/совместимости передаваемых услуг для записи с mos.ru
 *
 * Class VisitDateValidator
 * @package app\common\validators
 */
class VisitServicesValidator extends Validator
{
    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $ids = $model->$attribute;

        $rows = MosruServices::find()
            ->distinct()
            ->where(['id' => $ids])
            ->asArray()
            ->indexBy('id')
            ->all();

        $diff = array_diff($ids, array_keys($rows));
        if (!empty($diff)) {
            $this->addError($model, $attribute, "Не найдены услуги: " . implode(',', $diff));
        } else {
            $type = null;
            foreach ($rows as $row) {
                if (!isset($type)) {
                    $type = $row['id_service_type'];
                }

                if ($type != $row['id_service_type']) {
                    $this->addError($model, $attribute, "Передан несовместимый тип услуг");
                    break;
                }
            }
        }
    }

}
