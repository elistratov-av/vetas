<?php

namespace app\common\validators;

use app\models\db\ContactTypes;
use yii\validators\Validator;

/**
 * При добавлении контакта организации передается "name": "72222333222". Хранить необходимо в формате "+72222333222"
 * Class FilterOrganizationPhoneValidator
 * @package app\common\validators
 */
class FilterOrganizationPhoneValidator extends Validator
{
    /**
     * @param \app\models\db\Contacts $model
     * @param string          $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if (empty($model->$attribute) || !is_string($model->$attribute)) {
            return;
        }
        if (empty($model->id_contact_type)) {
            return;
        }
        if ($model->entity_type != ContactTypes::ENTITY_TYPE_ORGANIZATION) {
            return;
        }
        $phoneContactTypes = ContactTypes::typeOptions(ContactTypes::ENTITY_TYPE_ORGANIZATION, ContactTypes::TYPE_PHONE);
        if (!array_key_exists($model->id_contact_type, $phoneContactTypes)) {
            return;
        }

        $value = trim($model->$attribute);

        if (preg_match('#^\d{11}$#', $value) === 1) {
            $value = '+' . $value;
        }

        $model->$attribute = $value;
    }
}
