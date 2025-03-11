<?php

namespace app\common\validators;

use app\models\db\DescriptionTypes;
use yii\db\Query;
use yii\validators\Validator;

class VisitDescriptionTypeValidator extends Validator
{
    /**
     * Проверка типа описания(id_description_type) на соответсвие допустимому для этой услуги
     *
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $check = (new Query())
            ->from('visits_gov_services vgs')
            ->innerJoin(
                'services_description_types sdt',
                'sdt.id_service = vgs.id_service'
            )
            ->innerJoin(
                DescriptionTypes::tableName() . ' dt',
                'dt.id = sdt.id_description_type'
            )
            ->where(['vgs.id_visit' => $model->id_visit])
            ->andWhere(['dt.id' => $model->$attribute])
            ->count();

        if (!$check) {
            $this->addError($model, $attribute, 'Тип описания не соответствует составу услуг данного приема');
            return;
        }

    }
}
