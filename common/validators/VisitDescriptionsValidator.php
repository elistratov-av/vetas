<?php

namespace app\common\validators;

use app\common\models\VisitStatus;
use app\models\db\DescriptionTypes;
use app\models\db\VisitDescriptions;
use yii\db\Query;
use yii\validators\Validator;

class VisitDescriptionsValidator extends Validator
{
    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $new = $model->getAttribute($attribute);

        if ($new == VisitStatus::FINISHED) {
            $non_exists_types = (new Query())
                ->select('dt.name')
                ->from('visits_gov_services vgs')
                ->innerJoin(
                    'services_description_types sdt',
                    'sdt.id_service = vgs.id_service'
                )
                ->innerJoin(
                    DescriptionTypes::tableName() . ' dt',
                    'dt.id = sdt.id_description_type'
                )
                ->leftJoin(
                    VisitDescriptions::tableName() . ' vd',
                    'vd.id_visit = vgs.id_visit AND vd.id_description_type = dt.id'
                )
                ->where(['vgs.id_visit' => $model->id])
                ->andWhere(['sdt.required' => true])
                ->andWhere(['vd.id' => null])
                ->groupBy('dt.id')
                ->column();

            if ($non_exists_types) {
                $this->addError($model, $attribute, 'Не заполнено описание: ' . implode(', ', $non_exists_types));
            }
        }

    }

}
