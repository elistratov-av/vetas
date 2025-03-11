<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * Class DocumentTypes
 * @package app\models\db
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $group
 */
class DocumentTypes extends ActiveRecord
{
    const TYPE_ACT_CATCH = 'TYPE_ACT_CATCH';
    const TYPE_ACT_ARRIVE_CATCH = 'TYPE_ACT_ARRIVE_CATCH';
    const TYPE_ACT_ARRIVE_COURT_DECISION = 'TYPE_ACT_ARRIVE_COURT_DECISION';
    const TYPE_ACT_ARRIVE_FOUNDLING = 'TYPE_ACT_ARRIVE_FOUNDLING';
    const TYPE_ACT_OWNERS_REFUSAL = 'TYPE_ACT_OWNERS_REFUSAL';
    const TYPE_COURT_DECISION = 'TYPE_COURT_DECISION';
    const TYPE_WORK_ORDER = 'TYPE_WORK_ORDER';
    const TYPE_ACT_RETURN_TO_OWNER = 'TYPE_ACT_RETURN_TO_OWNER';
    const TYPE_QUESTIONNAIRE = 'TYPE_QUESTIONNAIRE';
    const TYPE_CONTRACT = 'TYPE_CONTRACT';
    const TYPE_CONTRACT_GUARDIANSHIP = 'TYPE_CONTRACT_GUARDIANSHIP';
    const TYPE_ACT_DEATH = 'TYPE_ACT_DEATH';
    const TYPE_AUTOPSY_RESULTS = 'TYPE_AUTOPSY_RESULTS';
    const TYPE_REFUSE_OWNER = 'TYPE_REFUSE_OWNER';
    const TYPE_OTHER = 'TYPE_OTHER';

    const TYPES = [
        self::TYPE_ACT_CATCH => 'Акт отлова',
        self::TYPE_ACT_ARRIVE_CATCH => 'Акт приема (отлов)',
        self::TYPE_ACT_ARRIVE_COURT_DECISION => 'Акт приема (по решению суда)',
        self::TYPE_ACT_ARRIVE_FOUNDLING => 'Акт приема (подкидыш)',
        self::TYPE_ACT_OWNERS_REFUSAL => 'Акт приема (отказ владельца)',
        self::TYPE_COURT_DECISION => 'Решение суда',
        self::TYPE_WORK_ORDER => 'Заказ-наряд',
        self::TYPE_ACT_RETURN_TO_OWNER => 'Акт возврата потерявшегося животного его владельцу',
        self::TYPE_QUESTIONNAIRE => 'Анкета желающего взять животное',
        self::TYPE_CONTRACT => 'Договор передачи животного новому владельцу',
        self::TYPE_CONTRACT_GUARDIANSHIP => 'Договор о передаче животного в собственность (под опеку)',
        self::TYPE_ACT_DEATH => 'Акт смерти',
        self::TYPE_REFUSE_OWNER => 'Отказ владельца',
        self::TYPE_AUTOPSY_RESULTS => 'Результаты вскрытия',
        self::TYPE_OTHER => 'Иное',
    ];

    // где-то на фронте болтается
    const GROUP_CARD = 'GROUP_CARD';
    const GROUP_NEW_OWNER = 'GROUP_NEW_OWNER';
    const GROUP_RETURN = 'GROUP_RETURN';
    const GROUP_DEATH = 'GROUP_DEATH';
    // расширенная реализация
    const GROUP_ACTS_OF_ARRIVE = 'GROUP_ACTS_OF_ARRIVE';
    const GROUP_WORK_ORDERS = 'GROUP_WORK_ORDERS';
    const GROUP_SHELTER_GUEST_MOVEMENTS = 'GROUP_SHELTER_GUEST_MOVEMENTS';

    const GROUP_CATCHING = ShelterGuests::ARRIVAL_REASON_CATCH;
    const GROUP_COURT_DECISION = ShelterGuests::ARRIVAL_REASON_COURT_DECISION;
    const GROUP_FOUNDLING = ShelterGuests::ARRIVAL_REASON_FOUNDLING;
    const GROUP_REFUSE_OWNER = ShelterGuests::ARRIVAL_REASON_OWNER_REFUSAL;

    const GROUP_DEPARTURE_REASON_RETURNED_TO_NEW_OWNER = ShelterGuests::DEPARTURE_REASON_RETURNED_TO_NEW_OWNER;
    const GROUP_DEPARTURE_REASON_RETURNED_TO_OWNER = ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER;
    const GROUP_DEPARTURE_REASON_DEATH = ShelterGuests::DEPARTURE_REASON_DEATH;
    const GROUP_DEPARTURE_REASON_EUTHANASIA = ShelterGuests::DEPARTURE_REASON_EUTHANASIA;
    const GROUP_DEPARTURE_REASON_ESCAPE = ShelterGuests::DEPARTURE_REASON_ESCAPE;

    const GROUPS = [
        self::GROUP_CARD => 'Карточка учета животного',
        self::GROUP_NEW_OWNER => 'Передача новому владельцу',
        self::GROUP_RETURN => 'Возврат',
        self::GROUP_DEATH => 'Смерть',
        self::GROUP_ACTS_OF_ARRIVE => 'Акты прибытия',
        self::GROUP_WORK_ORDERS => 'Заказ-наряды',
        self::GROUP_CATCHING => 'Отлов',
        self::GROUP_SHELTER_GUEST_MOVEMENTS => 'Документы о движении животного приюта',
        self::GROUP_COURT_DECISION => 'Решение суда',
        self::GROUP_FOUNDLING => 'Подкидыш',
        self::GROUP_REFUSE_OWNER => 'Отказ владельца',
        self::GROUP_DEPARTURE_REASON_RETURNED_TO_NEW_OWNER => 'Передача новому владельцу',
        self::GROUP_DEPARTURE_REASON_RETURNED_TO_OWNER => 'Возврат прежнему владельцу',
        self::GROUP_DEPARTURE_REASON_DEATH => 'Падёж',
        self::GROUP_DEPARTURE_REASON_EUTHANASIA => 'Эвтаназия',
        self::GROUP_DEPARTURE_REASON_ESCAPE => 'Побег',
    ];

    const DOCUMENTS_BY_GROUPS = [
        self::GROUP_ACTS_OF_ARRIVE => [
            self::TYPE_ACT_ARRIVE_CATCH,
            self::TYPE_ACT_ARRIVE_COURT_DECISION,
            self::TYPE_ACT_ARRIVE_FOUNDLING,
            self::TYPE_ACT_OWNERS_REFUSAL,
        ],
        self::GROUP_WORK_ORDERS => [
            self::TYPE_WORK_ORDER,
        ],
        self::GROUP_SHELTER_GUEST_MOVEMENTS => [
            self::TYPE_ACT_ARRIVE_CATCH,
            self::TYPE_ACT_ARRIVE_COURT_DECISION,
            self::TYPE_ACT_ARRIVE_FOUNDLING,
            self::TYPE_ACT_CATCH,
            self::TYPE_WORK_ORDER,
            self::TYPE_REFUSE_OWNER,
            self::TYPE_OTHER,
        ],
        self::GROUP_CATCHING => [
            self::TYPE_ACT_CATCH,
            self::TYPE_ACT_ARRIVE_CATCH,
            self::TYPE_WORK_ORDER,
            self::TYPE_OTHER,
        ],
        self::GROUP_COURT_DECISION => [
            self::TYPE_ACT_ARRIVE_COURT_DECISION,
            self::TYPE_COURT_DECISION,
            self::TYPE_OTHER,
        ],
        self::GROUP_FOUNDLING => [
            self::TYPE_ACT_ARRIVE_FOUNDLING,
            self::TYPE_OTHER,
        ],
        self::GROUP_REFUSE_OWNER => [
            self::TYPE_REFUSE_OWNER,
            self::TYPE_OTHER,
        ],
        self::GROUP_DEPARTURE_REASON_RETURNED_TO_NEW_OWNER => [
            self::TYPE_QUESTIONNAIRE,
            self::TYPE_CONTRACT,
            self::TYPE_CONTRACT_GUARDIANSHIP,
        ],
        self::GROUP_DEPARTURE_REASON_RETURNED_TO_OWNER => [
            self::TYPE_ACT_RETURN_TO_OWNER,
        ],
        self::GROUP_DEPARTURE_REASON_DEATH => [
            self::TYPE_ACT_DEATH,
            self::TYPE_AUTOPSY_RESULTS,
        ],
        self::GROUP_DEPARTURE_REASON_EUTHANASIA => [
            self::TYPE_ACT_DEATH,
        ],
        self::GROUP_DEPARTURE_REASON_ESCAPE => [
            self::TYPE_OTHER,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_types';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'type', 'group'], 'required'],
            [['id'], 'integer'],
            [['name', 'type', 'group'], 'string'],
            [['name', 'type', 'group'], 'filter', 'filter' => 'trim'],
            [['name', 'type', 'group'], 'filter', 'filter' => 'strip_tags'],
            [['name', 'type', 'group'], FullTrimValidator::class],
            ['type', 'in', 'range' => array_keys(self::TYPES)],
            ['group', 'in', 'range' => array_keys(self::GROUPS)],
        ];
    }

    public static function updateTypes()
    {
        $document_types = static::find()->indexBy('id')->all();
        $pointers = [];
        foreach ($document_types as $document_type) {
            $pointers["{$document_type->group}::{$document_type->type}"] = $document_type->id;
        }

        foreach (static::DOCUMENTS_BY_GROUPS as $group => $types) {
            foreach ($types as $type) {
                $key = "$group::$type";
                if (isset($pointers[$key])) {
                    $document_type = $document_types[$pointers[$key]];
                    if ($document_type->name !== static::TYPES[$type]) {
                        $document_type->updateAttributes(['name' => static::TYPES[$type]]);
                    }
                    unset($pointers[$key]);
                } else {
                    $model = new static();
                    $model->setAttributes([
                        'name' => static::TYPES[$type],
                        'type' => $type,
                        'group' => $group,
                    ]);
                    $model->save(false);
                }
            }
        }

        // старые не удаляем, т.к. могут существовать документы со старой комбинацией
        // static::deleteAll(['id' => $pointers]);
    }
}
