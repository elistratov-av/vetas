<?php

namespace app\modules\v2\modules\specialist\models;

use app\common\components\rbac\Role;
use app\models\db\Organizations;
use app\models\db\ShiftType;
use yii\base\InvalidConfigException;
use yii\db\Expression;

/**
 * Class AmbulanceDatelistModel
 * @package app\modules\v2\modules\specialist\models
 */
class AmbulanceDatelistModel extends DatelistModel
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->shiftType = ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE;
        $this->id_shift_type = ShiftType::getTypesIdWithAmbulance();

        if (empty($this->id_shift_type)) {
            throw new InvalidConfigException('Не назначен тип смены для приемов ВПД');
        }

        if (!empty($this->id_organization) && is_int($this->id_organization)) {
            // получим все id организаций сети по переданному id одной из организаций сети
            $this->id_organization = Organizations::orgTreeIds($this->id_organization);
        }

        $this->date_from = $this->date_from ?? date('Y-m-d');
        $this->days_count = $this->days_count ?? 14;
        $this->page = $this->page ?? 1;
        $this->limit = $this->limit ?? 10;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['date_from', 'required'],
            [['days_count', 'page', 'limit'], 'integer'],
            ['date_from', 'validateDateFrom'],
        ];
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateDateFrom($attribute, $params, $validator): void
    {
        $dateTimeFrom = \DateTime::createFromFormat('Y-m-d', $this->date_from);
        if ($dateTimeFrom === false) {
            $this->addError($attribute, 'Переданная дата невалидна');
            return;
        }

        $tempDate = clone $dateTimeFrom;
        $dateTimeTo = $tempDate->modify("+ {$this->days_count} days");
        if ($dateTimeFrom === false) {
            $this->addError($attribute, 'Переданная дата невалидна');
            return;
        }

        $this->dateTimeFrom = $dateTimeFrom;
        $this->dateTimeTo = $dateTimeTo;
    }

    /**
     * @param Expression $dateExpression
     * @return mixed
     * @throws \yii\db\Exception
     */
    protected function findTimeSheets($dateExpression)
    {
        $params = [
            'id_shift_type' => $this->id_shift_type,
            'workday_shift_type' => $this->workdayIdShiftType(),
            'range_start' => $this->dateTimeFrom->format('Y-m-d 00:00:00'),
            'range_end' => $this->dateTimeTo->format('Y-m-d 00:00:00'),
            'role_ambulance' => Role::ROLE_VET_SPECIALIST_GOS_AMB,
        ];

        $sql = <<<SQL
SELECT "timesheets"."id",
       "timesheets"."id_specialist",
       lower("timesheets"."date") AS from,
       upper("timesheets"."date") AS to,
       FALSE AS manual_shift,
       MAX("shifts"."id") AS "id_shift",
       MAX("shifts"."id_type") AS "id_shift_type",
       MAX("child"."id") AS "ambulance_shift_exists"
FROM "public"."timesheets"
         INNER JOIN "shifts"
                    ON "shifts"."id" = "timesheets"."id_shift"
                        AND "shifts"."id_type" = :workday_shift_type
         INNER JOIN (SELECT "tsh"."id",
                           "tsh"."parent_id",
                           "sh"."id_type"
                    FROM "timesheets" "tsh"
                             INNER JOIN "auth_assignment" "auth"
                                        ON "auth"."id_specialist" = "tsh"."id_specialist"
                                            AND "auth"."item_name" = :role_ambulance
                             INNER JOIN "shifts" "sh"
                                        ON "sh"."id" = "tsh"."id_shift"
                                            AND "sh"."id_type" = :id_shift_type
SQL;

        if (!empty($this->id_organization)) {
            $sql .= ' AND "sh"."id_organization" IN (' . implode(', ', $this->id_organization) . ')';
        }

        $sql .= <<<SQL
             ) "child"
                   ON "child"."parent_id" = "timesheets"."id"
WHERE "timesheets"."parent_id" IS NULL
  AND ("timesheets"."date" && tsrange(:range_start, :range_end, '[)'))
GROUP BY "timesheets"."id";
SQL;

        $command = \Yii::$app->db->createCommand($sql, $params);

        return $command->queryAll();
    }
}
