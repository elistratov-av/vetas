<?php

namespace app\common\validators;

use app\models\db\Brood;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Visits;
use yii\db\Query;
use yii\validators\Validator;

/**
 * TODO:multiple-pets-services
 */
class VisitPetValidator extends Validator
{
    /**
     * @var bool
     */
    public $isNewVisit = false;
    /**
     * @var string
     */
    public $variety = Visits::VISIT_SINGLE;

    /**
     * @param \app\models\db\Visits|\app\modules\v2\modules\visit\models\VisitSaveModel $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $petsCount = count($model->$attribute);
        if ($this->variety == Visits::VISIT_SINGLE && $petsCount != 1) {
            $this->addError($model, 'id_pet', 'В приеме с одним животным должно быть только одно животное');
            return;
        }
        if ($this->variety == Visits::VISIT_BROOD
            && ($petsCount < ($this->isNewVisit ? Brood::MIN_PETS_COUNT : 1) || $petsCount > Brood::MAX_PETS_COUNT)) {
            $this->addError($model, 'id_pet', 'В приеме с выводком должно быть от ' . Brood::MIN_PETS_COUNT . ' до ' . Brood::MAX_PETS_COUNT . ' животных');
            return;
        }
        if ($this->variety == Visits::VISIT_MULTIPLE && ($petsCount < ($this->isNewVisit ? 2 : 1) || $petsCount > 10)) {
            $this->addError($model, 'id_pet', 'В приеме с несколькими животными должно быть от 2 до 10 животных');
            return;
        }

        $id_brood = null;

        foreach ($model->$attribute as $id_pet) {
            $pet = (new Query())
                ->from(Pets::tableName())
                ->where(['id' => $id_pet])
                ->one();

            if (empty($pet)) {
                // Note: так как дублируются запросы к БД, уберем 'exist' валидатор в VisitSaveModel
                $this->addError($model, 'id_pet', 'Указанное животное не найдено');
                return;
            }

            // снято ли животное с учета
            if (!empty($pet['id_reg_expire_reason'])) {
                $this->addError($model, 'id_pet', 'Выбранное животное снято с регистрационного учета');
            }

            if ($pet['is_main'] === false) {
                $this->addError($model, 'id_pet', 'Нельзя записать на прием животное-дубль');
                return;
            }

            if ($this->variety == Visits::VISIT_BROOD) {
                if (empty($pet['id_brood'])) {
                    $this->addError($model, 'id_pet', 'Выбранное животное не принадлежит ни к какому выводку');
                    return;
                }
                if ($id_brood === null) {
                    $id_brood = $pet['id_brood'];
                } elseif ($id_brood != $pet['id_brood']) {
                    $this->addError($model, 'id_pet', 'В приеме должны быть животные из одного выводка');
                    return;
                }
            }

            // проверка владельца животного
            if (!empty($model->id_owner) && $model->type != Visits::TYPE_VISIT_VC_SHELTER) {//TODO
                // здесь не учитываем id_owner_type, записать на прием могут и владелец и представитель
                $exists = (new Query())
                    ->from(PetsToOwner::tableName())
                    ->where([
                        'id_owner' => $model->id_owner,
                        'id_pet' => $id_pet,
                    ])
                    ->exists();
                if ($exists !== true) {
                    $this->addError($model, 'id_owner', 'Указанный владелец не является владельцем животного');
                }
            }
        }
    }
}
