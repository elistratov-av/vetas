<?php

namespace app\modules\soap\validators;

use app\modules\soap\models\MosruSpecialists;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\validators\Validator;

/**
 * Проверка наличия/совместимости передаваемых услуг для записи с mos.ru
 *
 * Class VisitDateValidator
 * @package app\common\validators
 */
class ServicesSpecialistValidator extends Validator
{
    /** @var MosruSpecialists */
    public $specialist;

    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $ids = $model->$attribute;

        $exists = (new Query())
            ->select(new Expression(1))
            ->from('services_specialists')
            ->where(['id_specialist' => $this->specialist->id_specialist])
            ->andWhere(['id_organization' => $this->specialist->id_organization])
            ->andWhere(['id_service' => $ids])
            ->having('count(*) = :count', [':count' => count($ids)])
            ->scalar()
        ;

        if (!$exists) {
            $this->addError($model, $attribute, "Выбранные услуги не оказываются данным специалистом");
        }
    }

}
