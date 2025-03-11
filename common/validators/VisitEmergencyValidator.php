<?php

namespace app\common\validators;

use app\models\db\OrganizationsEmergency;
use yii\base\InvalidConfigException;
use yii\db\conditions\BetweenColumnsCondition;
use yii\validators\Validator;

/**
 * Class VisitEmergencyValidator
 * @package app\common\validators
 */
class VisitEmergencyValidator extends Validator
{
    public $organization_id;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (is_null($this->organization_id)) {
            throw new InvalidConfigException('The "organization_id" property must be set.');
        }
    }

    public function validateValue($value)
    {
        $emercase = OrganizationsEmergency::find()
            ->where([
                'id_organization' => $this->organization_id,
            ])->andWhere(
                new BetweenColumnsCondition(
                    $value,
                    'BETWEEN',
                    'date_from',
                    'date_to'
                ));

        if ($emercase->exists()) {
            return [$this->message, []];
        }

        return null;
    }
}
