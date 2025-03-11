<?php

namespace app\modules\v2\modules\visit\models;

use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\events\VisitTransferedEvent;
use app\common\components\inform\SubscriptionService;
use app\common\models\VisitStatus;
use app\common\validators\VisitPetValidator;
use app\models\db\ContactTypes;
use app\models\db\GovServices;
use app\models\db\Organizations;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Shifts;
use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\models\db\VisitPets;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitsGovServices;
use app\models\db\VisitsSpecialists;
use app\models\MosruNotification;
use app\modules\soap\v2\queue\MosruStatusSender;
use app\modules\v2\modules\visit\services\VisitDurationsService;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\validators\DateValidator;
use yii\validators\InlineValidator;
use yii\web\BadRequestHttpException;

/**
 * Class VisitSaveModel
 *
 * @package app\modules\v2\modules\visit\models
 * @see     https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
 * @see     https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=96319116
 * @see     https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=91265676
 * @see     https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=96314492
 */
class VisitSaveModel extends Model
{
    use ParamsTrait, VisitTrait;

    const SCENARIO_CREATE_VISIT = 'create';
    const SCENARIO_UPDATE_VISIT = 'update';
    const SCENARIO_UPDATE_PREFERENCES = 'update_preferences';
    const SCENARIO_CREATE_SIMPLIFIED_VISIT = 'create_simplified';
    const SCENARIO_UPDATE_SIMPLIFIED_VISIT = 'update_simplified';

    /**
     * @var \app\models\db\Visits
     */
    public $visit;

    /**
     * @var int
     */
    public $id_owner;

    /**
     * @var int|array
     */
    public $id_pet;

    /**
     * @var int
     */
    public $id_organization;

    /**
     * @var int
     */
    public $vaccination_station_id;

    /**
     * @var int
     */
    public $channel;

    /**
     * @var string
     */
    public $start_dttm;

    /**
     * @var string
     */
    public $fact_start_dttm;

    /**
     * @var int
     */
    public $id_specialist;

    /**
     * @var int
     */
    public $source;

    /**
     * @var int
     */
    public $author;

    /**
     * @var array
     */
    public $services;

    /**
     * @var bool
     */
    public $type;

    /**
     * @var string
     */
    public $visit_to_address;

    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $is_veteran;

    /**
     * @var bool
     */
    public $is_disabled;

    /**
     * @var bool
     */
    public $is_blind;

    /**
     * @var bool
     */
    public $is_orphan;

    /**
     * @var bool
     */
    public $is_large_family;

    /**
     * @var bool
     */
    public $is_veteran_of_labour;

    /**
     * @var string
     */
    public $preferences_document;

    /**
     * @var string
     */
    public $variety;

    /**
     * @var integer
     */
    public $id_quarantine;

    /**
     * Для оповещения о перезаписи, задаётся в checkSpecialistChanged()
     * @var int
     */
    private $idSpecialistOld;

    /**
     * @var array
     */
    private static $shiftTypesMap = [];

    /**
     * @var array
     */
    private $allowedShiftTypes = [
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY,
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT,
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE,
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT,
        ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE,
    ];

    /**
     * @var bool
     */
    private $specialistChanged = false;

    /**
     * @var array
     */
    private $govServices = [];

    /**
     * @var bool
     */
    private $isCallToHome = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->type = $this->type ?? Visits::TYPE_VISIT;

        if (in_array($this->scenario, [self::SCENARIO_UPDATE_VISIT, self::SCENARIO_UPDATE_SIMPLIFIED_VISIT])) {
            if (!isset($this->visit)) {
                throw new InvalidConfigException();
            }
            $this->channel = $this->visit->channel;
            $this->variety = $this->visit->variety;
        }
        if ($this->scenario == self::SCENARIO_CREATE_VISIT) {
            $this->visit = new Visits([
                'status' => VisitStatus::NEW,
                'is_paid' => false,
            ]);
        }
        if ($this->scenario == self::SCENARIO_UPDATE_VISIT && in_array($this->visit->status,
                [VisitStatus::NEW, VisitStatus::TRANSFER], true)) {
            $this->visit->status = VisitStatus::CHANGED;
        }

        if ($this->scenario == self::SCENARIO_CREATE_SIMPLIFIED_VISIT) {
            $this->visit = new Visits([
                'status' => VisitStatus::IN_WORK,
                'is_paid' => false,
            ]);
        }

        self::mapShiftTypes();
    }

    /**
     * @return bool
     */
    public function createVisit(): bool
    {
        return $this->save();
    }

    /**
     * @return bool
     */
    public function updateVisit(): bool
    {
        return $this->save();
    }

    /**
     * @return bool
     */
    public function updateVisitPreferences()
    {
        if (!$this->validate()) {
            return false;
        }

        $attributes = $this->getAttributes([
            'is_veteran',
            'is_disabled',
            'is_blind',
            'is_orphan',
            'is_large_family',
            'is_veteran_of_labour',
            'preferences_document'
        ]);

        if ($this->visit->load($attributes, '') && $this->visit->save()) {
            return true;
        }

        $this->addErrors($this->visit->getErrors());

        return false;
    }

    /**
     * @param bool $validate Выполнять ли валидацию
     *
     * @return bool
     */
    private function save(bool $validate = true): bool
    {
        if ($validate && !$this->validate()) {
            return false;
        }

        $this->isCallToHome = $this->isCallToHome($this->type);

        // проверяем наличие экстренной ситуации для приемов, не относящихся к вызовам на дом и вызовам НВП
        if (!$this->isCallToHome && !$this->isAmbulanceVisit($this->type) && $this->checkVisitForEmergency($this->id_organization,
                $this->start_dttm ?? date('Y-m-d H:i:s'))) {
            return false;
        }


        if (!$this->prepareModel()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $oldAttributes = $this->visit->getOldAttributes();

            if (!$this->visit->save()) {
                $this->addErrors($this->visit->getErrors());
                $transaction->rollBack();

                return false;
            }
            if (!$this->linkPets()) {
                $transaction->rollBack();

                return false;
            }
            if ($this->scenario == self::SCENARIO_CREATE_VISIT ||
                $this->scenario == self::SCENARIO_CREATE_SIMPLIFIED_VISIT ||
                $this->specialistChanged
            ) {
                if (!$this->linkSpecialist()) {
                    $transaction->rollBack();

                    return false;
                }
            }
            if (!$this->linkServicesAndParams()) {
                $transaction->rollBack();

                return false;
            }

            if ($this->scenario == self::SCENARIO_UPDATE_VISIT && $this->visit->isMosRu()) {
                //MosruNotification::visitChangeInClinic($this->visit->id);
                (new MosruStatusSender())->visitChangeInClinic($this->visit->id);//
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($this->scenario === self::SCENARIO_UPDATE_VISIT
            && $this->visit->status === VisitStatus::CHANGED
            && $oldAttributes['status'] === VisitStatus::TRANSFER) {

                if ($contacts = SubscriptionService::getOwnerSubscriptions($this->visit->owner, [ ContactTypes::TYPE_EMAIL ])) {
                    $newvet = Specialists::find()->where(['id' => $this->id_specialist])->one()->fullname;
                    \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new VisitTransferedEvent([
                        'contacts' => $contacts,
                        'olddate' => new \DateTime($oldAttributes['start_dttm']),
                        'oldvet' =>
                            $this->idSpecialistOld
                            ? Specialists::find()->where(['id' => $this->idSpecialistOld])->one()->fullname
                            : $newvet,
                        'newvet' => $newvet,
                        'visit' => $this->visit,
                    ]));
                }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'visit',
                'validateVisit',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_UPDATE_PREFERENCES,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'variety',
                'default',
                'value' => Visits::VISIT_SINGLE,
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'variety',
                'in',
                'range' => Visits::varieties(),
                'skipOnEmpty' => false,
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                ['id_owner', 'id_pet'],
                'required',
                'skipOnEmpty' => true,
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'id_organization',
                'required',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->type != Visits::TYPE_AMBULANCE;
                },
            ],
            [
                ['id_owner', 'id_organization', 'id_specialist'],
                'integer',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            ['id_owner', 'exist', 'targetClass' => PetOwners::class, 'targetAttribute' => 'id'],
            [
                'id_pet',
                function ($attribute, $params, $validator) {
                    if (!is_array($this->$attribute)) {
                        $this->$attribute = [$this->$attribute];
                    }
                },
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'id_pet',
                VisitPetValidator::class,
                'isNewVisit' => in_array($this->scenario, [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ]),
                'variety' => $this->variety,
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            ['id_organization', 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
            [
                ['source', 'author'],
                'integer',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ]
            ],
            [
                'source',
                'exist',
                'targetClass' => Visits::class,
                'targetAttribute' => ['source' => 'id'],
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'author',
                'exist',
                'targetClass' => Specialists::class,
                'targetAttribute' => 'id',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'channel',
                'integer',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ]
            ],
            [
                'channel',
                'required',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->type != Visits::TYPE_AMBULANCE;
                },
            ],
            [
                'channel',
                'default',
                'value' => function ($model, $attribute) {
                    return array_search(ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE, self::$shiftTypesMap);
                },
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->type == Visits::TYPE_AMBULANCE;
                },
            ],
            [
                'channel',
                'in',
                'range' => function ($model, $attribute) {
                    return array_keys(self::$shiftTypesMap);
                },
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'start_dttm',
                DateValidator::class,
                'format' => 'php:Y-m-d H:i:s',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'fact_start_dttm',
                DateValidator::class,
                'format' => 'php:Y-m-d H:i:s',
                'on' => [
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'start_dttm',
                'validateStartDttm',
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_VISIT],
            ],
            [
                'id_specialist',
                'exist',
                'targetClass' => Specialists::class,
                'targetAttribute' => 'id',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'id_specialist',
                'validateSpecialist',
                'skipOnEmpty' => false,
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_VISIT],
            ],
            [
                'services',
                'validateServices',
                'skipOnEmpty' => false,
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_VISIT],
            ],
            ['type', 'default', 'value' => Visits::TYPE_VISIT, 'on' => [self::SCENARIO_CREATE_VISIT]],
            [
                'type',
                'in',
                'range' => Visits::types(),
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                ['id_quarantine', 'vaccination_station_id'],
                'validateSimplifiedVisit',
                'on' => [
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                ['visit_to_address', 'description'],
                'string',
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_VISIT]
            ],
            [
                'visit_to_address',
                'required',
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_VISIT],
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->type == Visits::TYPE_AMBULANCE || $model->type == Visits::TYPE_AT_HOME;
                },
            ],
            [
                ['is_veteran', 'is_disabled', 'is_blind', 'is_orphan', 'is_large_family', 'is_veteran_of_labour'],
                'boolean',
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_UPDATE_PREFERENCES,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ]
            ],
            [
                ['is_veteran', 'is_disabled', 'is_orphan', 'is_large_family', 'is_veteran_of_labour'],
                'default',
                'value' => false,
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                'is_blind',
                'default',
                'value' => function ($model, $attribute) {
                    /* @var $model $this */
                    $pet = Pets::findOne(['id' => $model->id_pet]);

                    return ($pet !== null) ? $pet->guide_dog : false;
                },
                'on' => [
                    self::SCENARIO_CREATE_VISIT,
                    self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                ],
            ],
            [
                ['is_veteran', 'is_disabled', 'is_blind', 'is_orphan', 'is_large_family', 'is_veteran_of_labour'],
                'required',
                'on' => self::SCENARIO_UPDATE_PREFERENCES
            ],
            [
                ['is_veteran', 'is_disabled', 'is_blind', 'is_orphan', 'is_large_family', 'is_veteran_of_labour'],
                'validatePreferences',
                'on' => self::SCENARIO_UPDATE_PREFERENCES
            ],
            [
                'preferences_document',
                'string',
                'max' => 255,
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_PREFERENCES]
            ],
            [
                'preferences_document',
                'required',
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->is_veteran === true || $model->is_disabled === true || $model->is_blind === true ||
                        $model->is_orphan || $model->is_large_family || $model->is_veteran_of_labour;
                },
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_PREFERENCES],
                'message' => 'Нужно заполнить номер документа, подтверждающего льготу',
            ],
            [
                'preferences_document',
                'filter',
                'filter' => function ($value) {
                    return null;
                },
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->is_veteran === false && $model->is_disabled === false && $model->is_blind === false &&
                        $model->is_orphan === false && $model->is_large_family === false && $model->is_veteran_of_labour === false;
                },
                'on' => [self::SCENARIO_CREATE_VISIT, self::SCENARIO_UPDATE_PREFERENCES],
            ],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @param $validator
     */
    public function validateSimplifiedVisit($attribute, $params, $validator) {
        if ($this->visit->type === Visits::TYPE_VISIT_VC && !$this->visit->vaccination_station_id) {
            $this->addError($attribute, "Отсутствует обязательный параметр 'vaccination_station_id'");
        }
        if ($this->visit->type === Visits::TYPE_VISIT_VC_DETOUR && !$this->visit->id_quarantine) {
            $this->addError($attribute, "Отсутствует обязательный параметр 'id_quarantine'");
        }
    }

    /**
     * @param string          $attribute
     * @param array           $params
     * @param InlineValidator $validator
     */
    public function validatePreferences($attribute, $params, $validator)
    {
        if ($this->visit->is_paid === true &&
            $this->visit->oldAttributes[$attribute] !== $this->$attribute) {
            $this->addError($attribute, 'Невозможно изменять льготные флаги после оплаты приема.');
        }
    }

    /**
     * @param string          $attribute is the name of the attribute to be validated
     * @param array           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     *
     * @see \yii\validators\InlineValidator
     */
    public function validateVisit($attribute, $params, $validator): void
    {
        if (($this->scenario == self::SCENARIO_CREATE_VISIT || $this->scenario == self::SCENARIO_UPDATE_VISIT)
            && !in_array($this->visit->status, [VisitStatus::NEW, VisitStatus::CHANGED, VisitStatus::TRANSFER])) {
            $this->addError($attribute, 'Недопустимый статус приема');
        }

        if ($this->scenario == self::SCENARIO_UPDATE_PREFERENCES
            && !in_array($this->visit->status,
                [VisitStatus::NEW, VisitStatus::CHANGED, VisitStatus::TRANSFER, VisitStatus::IN_WORK])) {
            $this->addError($attribute, 'Недопустимый статус приема');
        }

        if (in_array($this->scenario,
                [self::SCENARIO_CREATE_SIMPLIFIED_VISIT, self::SCENARIO_UPDATE_SIMPLIFIED_VISIT])
            && $this->visit->status !== VisitStatus::IN_WORK) {
            $this->addError($attribute, 'Недопустимый статус приема');
        }
    }

    /**
     * @param string          $attribute is the name of the attribute to be validated
     * @param array           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     *
     * @see \yii\validators\InlineValidator
     */
    public function validateStartDttm($attribute, $params, $validator)
    {
        if ($this->hasErrors()) {
            // не будем проводить дальнейшую валидацию
            return;
        }

        $channel = ($this->scenario === self::SCENARIO_UPDATE_VISIT) ? $this->visit->channel : $this->channel;
        $type = ArrayHelper::getValue(self::$shiftTypesMap, $channel);
        if ($type == ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE) {
            // у живой очереди - NULL
            $this->start_dttm = null;
        } else {
            if (empty($this->start_dttm)) {
                $this->addError($attribute, 'Параметр start_dttm обязателен');
            }
        }
    }

    /**
     * @param string          $attribute is the name of the attribute to be validated
     * @param array           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     *
     * @see \yii\validators\InlineValidator
     */
    public function validateSpecialist($attribute, $params, $validator)
    {
        if ($this->hasErrors()) {
            // не будем проводить дальнейшую валидацию
            return;
        }

        if ($this->scenario == self::SCENARIO_CREATE_VISIT && !empty($this->$attribute)) {
            if ($this->isSpecialistExpelled()) {
                $this->addError($attribute, 'Специалист был уволен');

                return;
            }
        }

        if (in_array($this->scenario, [self::SCENARIO_UPDATE_VISIT, self::SCENARIO_UPDATE_SIMPLIFIED_VISIT])) {
            $this->checkSpecialistChanged();
        }

        if (empty($this->$attribute)) {
            return;
        }

        $specialist = Specialists::findOne(['id' => $this->$attribute]);
        if (empty($specialist->id_organization)) {
            $this->addError(
                $attribute,
                'Некорректный параметр ' . $attribute . ':'
                . ' специалисту ID ' . $this->$attribute . ' не назначена организация'
            );

            return;
        }

        if (!empty($this->id_organization)) {
            if ($specialist->id_organization != $this->id_organization) {
                $this->addError(
                    $attribute,
                    'Некорректный параметр ' . $attribute . ':'
                    . ' организация указанного специалиста ID ' . $this->$attribute . '(' . $specialist->id_organization . ')'
                    . ' не соответствует организации проведения приема (' . $this->id_organization . ')'
                );
            }
        } else {
            if ($this->type == Visits::TYPE_AMBULANCE) {
                $this->id_organization = $specialist->id_organization;
            }
        }
    }

    /**
     * @return bool
     */
    private function isSpecialistExpelled()
    {
        $specialist = Specialists::findOne(['id' => $this->id_specialist]);
        $date = empty($this->start_dttm) ? null : substr($this->start_dttm, 0, 10);

        return $specialist->isExpelledAtDate($date);
    }

    /**
     * Проверяем для редактируемого приема
     */
    private function checkSpecialistChanged()
    {
        $oldSpecialists = $this->visit->getVisitsSpecialists()
            ->asArray()
            ->all();

        if (empty($this->id_specialist)) {
            $this->specialistChanged = !empty($oldSpecialists);
        } elseif (empty($oldSpecialists)) {
            $this->specialistChanged = !empty($this->id_specialist);
        } else {
            // так и осталось hasMany - поэтому берем первого
            $oldIds = ArrayHelper::getColumn($oldSpecialists, 'id_specialist');
            $id_specialist = array_shift($oldIds);

            // Сохраняем для оповещения о перезаписи
            $this->idSpecialistOld = $id_specialist;

            $this->specialistChanged = ($id_specialist != $this->id_specialist);
        }
    }

    /**
     * @param string          $attribute is the name of the attribute to be validated
     * @param array           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     *
     * @see \yii\validators\InlineValidator
     */
    public function validateServices($attribute, $params, $validator)
    {
        if ($this->hasErrors()) {
            // не будем проводить дальнейшую валидацию
            return;
        }

        if ($this->$attribute === null || $this->$attribute === []) {
            // 'в ЖО может и не быть услуг'
            $type = ArrayHelper::getValue(self::$shiftTypesMap, $this->channel);
            if ($type == ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE) {
                $this->$attribute = $this->$attribute ?? [];
                // проверяем ЖО на экстренную ситуацию
                $this->checkVisitForEmergency($this->id_organization, date('Y-m-d H:i:s'));

                return;
            }
            $this->addError($attribute, 'Параметр ' . $attribute . ' не может быть пустым');

            return;
        }

        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Передан некорректный параметр ' . $attribute);

            return;
        }

        foreach ($this->$attribute as $data) {
            if (!is_array($data) || !isset($data['id_service']) || empty($data['id_service'])) {
                $this->addError($attribute, 'Передан некорректный параметр ' . $attribute);

                return;
            }
        }

        $servicesIds = ArrayHelper::getColumn($this->$attribute, 'id_service');

        /* @var $govServices \app\models\db\GovServices[] */
        $govServices = GovServices::find()
            ->where(['in', 'id', $servicesIds])
            ->indexBy('id')
            ->all();

        if (empty($govServices)) {
            $this->addError(
                $attribute,
                'Некорректный параметр ' . $attribute . ':'
                . ' указанные услуги (' . implode(', ', $servicesIds) . ') не существуют'
            );

            return;
        }

        foreach ($this->$attribute as &$data) {
            $id_service = $data['id_service'];
            if (!array_key_exists($id_service, $govServices)) {
                $this->addError(
                    $attribute,
                    'Некорректный параметр ' . $attribute . ':'
                    . ' указанная услуга ID ' . $id_service . ' не существуeт'
                );

                return;
            }

            $govService = $govServices[$id_service];

            // count
            if (isset($data['count'])) {
                // если не передан - по умолчанию будем считать 1
                if ($data['count'] < 1) {
                    $this->addError(
                        $attribute,
                        'Некорректный параметр ' . $attribute . ':'
                        . ' для услуги ID ' . $id_service . ' параметр count должен быть не менее 1'
                    );

                    return;
                }

                /**
                 * пока закоментировано зза ненадобностью...
                 * $serviceForType = $this->getServiceForTypeByGovService(
                 * GovServices::findOne($id_service)
                 * );
                 *
                 * // если ALL и  count != 1
                 * if ($serviceForType === GovServices::FOR_ALL && $data['count'] != 1) {
                 * $this->addError(
                 * $attribute,
                 * 'Некорректный параметр ' . $attribute . ':'
                 * . ' для услуги ID ' . $id_service . ' параметр count должен быть равен 1'
                 * );
                 *
                 * return;
                 * }
                 *
                 * // если count_flag == false &&  count < переданого количества животных в услуге
                 * $countServicePets = !empty($data['id_pet']) ? count(array_unique((array)$data['id_pet'])) : count(array_unique((array)$this->id_pet));
                 * $countFlag = GovServices::findCountFlag($id_service);
                 * if (!$countFlag && $data['count'] != $countServicePets) {
                 * $this->addError(
                 * $attribute,
                 * 'Некорректный параметр ' . $attribute . ':'
                 * . ' для услуги ID ' . $id_service . ' параметр count должен быть равен количеству животных в услуге'
                 * );
                 *
                 * return;
                 * }
                 *
                 * // Для всех остальных count должно быть равно или больше количества животных в услуге
                 * if ($data['count'] < $countServicePets) {
                 * $this->addError(
                 * $attribute,
                 * 'Некорректный параметр ' . $attribute . ':'
                 * . ' для услуги ID ' . $this->id_service . ' параметр count должен быть равен или больше количества животных в услуге'
                 * );
                 *
                 * return;
                 * }
                 */
            }

            if (array_key_exists('id_pet', $data)) {
                foreach ((array)$data['id_pet'] as $idPet) {
                    if ($this->id_pet && !in_array($idPet, (array)$this->id_pet)) {
                        $this->addError(
                            $attribute,
                            'Некорректный параметр ' . $attribute . ':'
                            . ' не все значение списка животных (' . $idPet . ') соответвуют общему списку животных приема'
                        );
                    }
                }
            }


            if ($this->variety == Visits::VISIT_SINGLE && !isset($data['id_pet'])) {
                $data['id_pet'] = is_array($this->id_pet) ? $this->id_pet[0] : $this->id_pet;
            }
            // UPD: НЕ проверяем специализацию
            // @see https://jira.altarix.ru/browse/VETAIS-1364
            // Убрать ограничения на специализацию врачей. Регистратура должна иметь возможность записать на любого врача
            // и на любую услугу в рамках каналов записи: живая очередь, запись по телефону, направление.
            //Любой врач любой специализации должен иметь возможность взять прием в работу

            // проверяем входящие параметры услуги
            if (!$this->validateVisitServiceParams($attribute, ArrayHelper::getValue($data, 'params'), $id_service,
                $govService)) {
                return;
            }
        }

        $this->govServices = $govServices;
    }

    /**
     * 0. Заполняет модель приёма (и возвращает false при ошибках)
     * 1. Подсчитывает и задаёт временнЫе характеристики приёма (длительность, перерыв)
     * 2. Проверяет доступность указанного времени
     * 3. Задаёт в приёме признак "визит к специалисту"
     *
     * @return bool
     */
    private function prepareModel()
    {
        if ($this->hasErrors()) {
            return false;
        }

        $attributes = $this->getAttributes($this->activeAttributes());

        if (!$this->visit->load($attributes, '')) {
            $this->addErrors($this->visit->getErrors());

            return false;
        }

        // рассчитываем duration и cooldown услуг
        // Не меняем для приемов, взятых в работу
        // https://jira.altarix.ru/browse/VETAIS-899
        if (!in_array($this->scenario, [
            self::SCENARIO_CREATE_SIMPLIFIED_VISIT,
            self::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
        ])) {
            if (($this->visit->status === VisitStatus::IN_WORK && !$this->visit->isAttributeChanged('status')) === false) {
                // https://jira.altarix.ru/browse/VETAIS-1849
                // Rumenko, 12:24 2019-05-15
                // 1. При переносе приемов с каналом записи "мос.ру", не пересчитывает продолжительность приема.
                // 2. Не ограничиваем возможность редактирования набора услуг приемов с мос.ру, т.к. в этом нет смысла, потому что пересчета времени всё равно не будет.
                $mosruType = array_search(ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT, self::$shiftTypesMap);
                if ($this->visit->channel !== $mosruType) {

                    [$duration, $cooldown] = VisitDurationsService::calculated(
                        $this->variety,
                        $this->services,
                        $this->govServices,
                        count($this->id_pet)
                    );
                    $this->visit->duration = $duration;
                    $this->visit->cooldown = $cooldown;
                }
            }
        }


        if ($this->visit->type !== Visits::TYPE_AMBULANCE) {
            // не проверяем доступность для приемов НВП (https://jira.altarix.ru/browse/VETAIS-1900)
            if ($this->scenario === self::SCENARIO_CREATE_VISIT
                || $this->scenario === self::SCENARIO_CREATE_SIMPLIFIED_VISIT
                || $this->scenario === self::SCENARIO_UPDATE_SIMPLIFIED_VISIT
                || ($this->scenario === self::SCENARIO_UPDATE_VISIT
                    && $this->visit->status !== VisitStatus::IN_WORK
                    && ($this->visit->isAttributeChanged('start_dttm')
                        || $this->visit->isAttributeChanged('duration')
                        || $this->visit->isAttributeChanged('cooldown')
                        || $this->specialistChanged))) {
                // проверяем доступность временного диапазона для новых приемов
                // и приемов, не находящихся в работе (если изменились атрибуты, влияющие на начало и продолжительность)
                if (!$this->checkTimerangeAvailability($this->visit)) {
                    return false;
                }
            }
        }

        // флаг для определения в TicketNumberBehavior для определения того, что запись к специалисту
        $this->visit->setFlagVisitToSpecialist($this->id_specialist !== null);

        return true;
    }

    /**
     * Для живой очереди - просто проверить, что у него есть в расписании НА СЕГОДНЯ живая очередь
     * Для остальных:
     * Проверяем, по данным start_dttm и duration что записываем в свободное время
     * Проверяем, что время записи и канал записи соответсвует расписанию спеца
     * ...надо проверить что у врача есть диапазон с указанным типом и запрошенное время попадет в этот интервал полностью
     *
     * @param \app\models\db\Visits $model
     *
     * @return bool
     */
    private function checkTimerangeAvailability($model)
    {
        $livequeueType = array_search(ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE, self::$shiftTypesMap);
        $workdayType = array_search(ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY, self::$shiftTypesMap);

        if (empty($this->id_specialist) && $model->channel != $livequeueType) {
            $this->addError('visit',
                'Параметр id_specialist обязателен для данного канала записи (' . $model->channel . ')');

            return false;
        }

        /*
         * У ПП по-факту работает с приемом не id_specialist, а author (входной параметр user_specialist_id)
         * Поэтому проверяем расписание у него
         */
        if ($this->visit->type === Visits::TYPE_VISIT_VC) {
            $id_specialist = $this->author;
        } else {
            $id_specialist = $this->id_specialist;
        }

        $queryT = (new Query())
            ->select('ts.*')
            ->from(Timesheets::tableName() . ' ts')
            ->innerJoin(
                Shifts::tableName() . ' sh',
                '[[ts]].[[id_shift]] = [[sh]].[[id]] AND [[sh]].[[id_type]] = :id_shift_type',
                ['id_shift_type' => $model->channel]
            );

        if (!empty($id_specialist)) {
            $queryT->andWhere(['id_specialist' => $id_specialist]);
        }
        /*
         * Тамшит для ПП может быть вообще от другой организации
         */
        if ($this->visit->type !== Visits::TYPE_VISIT_VC && !empty($model->id_organization)) {
            $queryT->andWhere(['=', '[[sh]].[[id_organization]]', $model->id_organization]);
        }

        $today = empty($model->start_dttm) ? date('Y-m-d') : substr($model->start_dttm, 0, 10);
        // TODO: возможно нужны другие начало и конец дня?
        $todayFrom = $today . ' 00:00:00';
        $todayTo = $today . ' 23:59:59';

        if ($model->channel == $livequeueType) {
            // Для живой очереди - просто проверить, что у него есть в расписании НА СЕГОДНЯ  живая очередь
            $queryT->andWhere(new Expression("(date && tsrange('{$todayFrom}', '{$todayTo}', '[)'))"));
            $exists = $queryT->exists();
            if ($exists === false) {
                $this->addError('visit',
                    'У выбранного специалиста в расписании отсутствует "живая очередь" на сегодня');
            }

            return $exists;
        }

        // сначала можно просто проверить у специалиста наличие приемов, пересекающихся по времени с текущим приемом
        // если прием пересекается с существующими приемами - уже нет смысла проверять расписания
        // (не учитываем при этом приемы из живой очереди,
        // не учитываем при этом приемы НВП - https://jira.altarix.ru/browse/VETAIS-1900)

        $minutes = $model->duration + $model->cooldown;
        $endTime = date_create_from_format('Y-m-d H:i:s', $model->start_dttm)
            ->modify('+' . $minutes . ' minutes')
            ->format('Y-m-d H:i:s');

        $queryV = (new Query())
            ->select('v.*, vs.id_specialist')
            ->from(Visits::tableName() . ' v')
            ->innerJoin(
                VisitsSpecialists::tableName() . ' vs',
                '[[vs]].[[id_visit]] = [[v]].[[id]] AND [[vs]].[[id_specialist]] = :id_specialist',
                ['id_specialist' => $id_specialist]
            )
            ->where(['!=', 'channel', $livequeueType])
            ->andWhere(['!=', 'type', Visits::TYPE_AMBULANCE])
            ->andWhere(['not in', 'status', [VisitStatus::CANCELED, VisitStatus::FINISHED, VisitStatus::TRANSFER]])
            ->andWhere(new Expression("(start_dttm <@ tsrange('{$todayFrom}', '{$todayTo}', '[)'))"))
            ->andWhere(new Expression("(time_range && tsrange('{$model->start_dttm}', '{$endTime}', '[)'))"));

        if (!$model->isNewRecord) {
            // не учитываем текущий прием
            $queryV->andWhere(['!=', 'id', $model->id]);
        }

        $exists = $queryV->exists();
        if ($exists === true) {
            $this->addError('visit',
                'У выбранного специалиста уже есть приемы, пересекающиеся с выбранным временем приема');

            return false;
        }

        // а вот теперь проверим расписания

        if ($model->channel != $workdayType) {
            $queryT->andWhere(['not', ['parent_id' => null]]);
        }
        $queryT->andWhere(new Expression("(date @> tsrange('{$model->start_dttm}', '{$endTime}', '[)'))"));

        $exists = $queryT->exists();

        if ($this->isCallToHome === true) {
            $callToHomeType = array_search(ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME, self::$shiftTypesMap);
            $queryTH = (new Query())
                ->select('ts.*')
                ->from(Timesheets::tableName() . ' ts')
                ->innerJoin(
                    Shifts::tableName() . ' sh',
                    '[[ts]].[[id_shift]] = [[sh]].[[id]] AND [[sh]].[[id_type]] = :id_shift_type',
                    ['id_shift_type' => $callToHomeType]
                )
                ->andWhere(['id_specialist' => $id_specialist])
                ->andWhere(['not', ['parent_id' => null]])
                ->andWhere(new Expression("(date @> tsrange('{$model->start_dttm}', '{$endTime}', '[)'))"));
            $exists = /**$exists && */ $queryTH->exists(); // hot fix for call to home
        }

//        if ($exists === false) {
//            $this->addError('visit',
//                'У выбранного специалиста в расписании отсутствуют свободные интервалы для заданного времени приема');
//
//            return false;
//        }

        // проверим перерывы, пересекающиеся со временем приема
        // TODO - придумать что-нибудь получше
        $queryB = (new Query())
            ->select('ts.*')
            ->addSelect('sh.id_type')
            ->from(Timesheets::tableName() . ' ts')
            ->innerJoin(Shifts::tableName() . ' sh', '[[ts]].[[id_shift]] = [[sh]].[[id]]')
            ->where(['id_specialist' => $id_specialist])
            ->andWhere(
                [
                    'id_type' => (new Query())
                        ->select('id')
                        ->from(ShiftType::tableName())
                        ->where(['idle' => true])
                ]
            )
            ->andWhere(['not', ['parent_id' => null]])
            ->andWhere(new Expression("(date && tsrange('{$model->start_dttm}', '{$endTime}', '[)'))"));

        $exists = $queryB->exists();
        if ($exists === true) {
            $this->addError('visit',
                'У выбранного специалиста в расписании есть перерывы, пересекающиеся с выбранным временем приема');
        }

        return !$exists;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    private function linkPets()
    {
        if (in_array($this->scenario, [self::SCENARIO_UPDATE_VISIT, self::SCENARIO_UPDATE_SIMPLIFIED_VISIT, self::SCENARIO_CREATE_SIMPLIFIED_VISIT])) {
            Yii::$app->db
                ->createCommand()
                ->delete(VisitPets::tableName(), ['id_visit' => $this->visit->id])
                ->execute();
        }

        foreach ($this->id_pet as $id_pet) {
            $link = new VisitPets([
                'id_visit' => $this->visit->id,
                'id_pet' => $id_pet,
            ]);
            if (!$link->save()) {
                $this->addError('id_pet', 'Ошибка при сохранении связи животного с приемом');

                return false;
            }
        }

        return true;
    }

    /**
     * Удаляем из visit_service_param_values и visits_gov_services существующие, для которых не переданы id
     * Новые значения сохраняем, существующие обновляем
     */
    private function linkServicesAndParams()
    {

        if ($this->scenario === self::SCENARIO_UPDATE_VISIT) {
            $servicesToUpdate = empty($this->services) ? [] : ArrayHelper::getColumn($this->services, 'id', false);
            $servicesToUpdate = array_filter($servicesToUpdate);
            $q = (new Query())
                ->select('id')
                ->from(VisitsGovServices::tableName())
                ->where(['id_visit' => $this->visit->id]);
            if (!empty($servicesToUpdate)) {
                $q->andWhere(['not in', 'id', $servicesToUpdate]);
            }
            $servicesToDelete = $q->column();
            if (!empty($servicesToDelete)) {
                // удаляем услуги и параметры для этих услуг (visit_service_param_values каскадно удаляются - constraint в БД)
                Yii::$app->db
                    ->createCommand()
                    ->delete(VisitsGovServices::tableName(), ['id' => $servicesToDelete])
                    ->execute();
            }
            if (!empty($servicesToUpdate)) {
                // проверим, не нужно ли для оставшихся услуг удалить часть параметров
                $params = [];
                foreach ($this->services as $data) {
                    if (!empty($data['id']) && !empty($data['params']) && is_array($data['params'])) {
                        $params = array_merge($params, $data['params']);
                    }
                }
                $paramsToUpdate = empty($params) ? [] : ArrayHelper::getColumn($params, 'id', false);
                $paramsToUpdate = array_filter($paramsToUpdate);
                $condition = empty($paramsToUpdate)
                    ? ['id_visitservice' => $servicesToUpdate]
                    : [
                        'and',
                        ['id_visitservice' => $servicesToUpdate],
                        ['not in', 'id', $paramsToUpdate],
                    ];
                Yii::$app->db
                    ->createCommand()
                    ->delete(VisitServiceParamValues::tableName(), $condition)
                    ->execute();
            }
        }

        foreach ($this->services as $serviceData) {
            $idPets = null;
            $govService = GovServices::findOne($serviceData['id_service']);
            $serviceForType = $this->getServiceForTypeByGovService($govService);

            $countVisitPets = count(array_unique((array)$this->id_pet));
            $countServicePets = (array_key_exists('id_pet', $serviceData)) && $serviceData['id_pet'] ?
                count(array_unique((array)$serviceData['id_pet'])) : $countVisitPets;

            /*
             * В текущей реализации для услуг на голову фронт должен передавать услугу индивидуально для каждой головы
             * Исключение, если количество услуг == количеству животных, но далее по логике всё равно разобъём их на индивидуальные
             */
            if ($serviceForType === GovServices::FOR_HEAD && $countServicePets > 1) {
                if ($countServicePets !== $serviceData['count']) {
                    throw new BadRequestHttpException('Техническая ошибка: неверный формат для услуг на голову с пользовательским значением параметра count');
                } else {
                    $serviceData['count'] = 1;
                }
            }

            /*
                - если услуга с признаком ALL , то услуга должна быть на  партию с количеством 1 (выбранные животные, независимо все или нет из прима, в услуге приходят нам в массиве)
                - в противном случае создаём услугу на каждое животное
             */
            if (
                array_key_exists('id_pet', $serviceData) &&
                $serviceData['id_pet'] && (
                    $serviceForType === GovServices::FOR_NULL ||
                    $serviceForType === GovServices::FOR_HEAD
                )
            ) {
                $idPets = (array)$serviceData['id_pet'];
            }

            if ($idPets === null) {
                $idPets = [null];
            }


            foreach ($idPets as $idPet) {
                $this->saveVisitParamsByGeneral(
                    $this->saveGorService($idPet, $serviceData)
                );
            }
        }

        return true;
    }

    /**
     * Возвращает возможные значения типа для услуги при ее создании
     *
     * @param $govService
     *
     * @return null|string Возможны значения: GovServices::FOR_NULL, GovServices::FOR_HEAD, GovServices::FOR_ALL
     */
    private function getServiceForTypeByGovService($govService)
    {
        switch ($this->variety) {
            case Visits::VISIT_BROOD:
                return $govService->for_broods;
            case Visits::VISIT_MULTIPLE:
                return $govService->for_multiple;
        }

        return null;
    }

    /**
     * @param array $serviceParams
     *
     * @return VisitsGovServices|null
     */
    private function saveGorService(?int $idPet, array $serviceParams): ?VisitsGovServices
    {
        $attributes = [
            'id_visit' => $this->visit->id,
            'id_service' => $serviceParams['id_service'],
            'id_pet' => $idPet,
            'count' => $serviceParams['count'],
        ];
        $visitGovService = isset($serviceParams['id'])
            ? VisitsGovServices::findOne(['id' => $serviceParams['id']])
            : new VisitsGovServices();
        if ($visitGovService === null) {
            // если не найден - создадим
            $visitGovService = new VisitsGovServices();
        }
        if ($visitGovService->load($attributes, '') && $visitGovService->save()) {
            // для услуг с признаком for_brood или for_multiple равному ALL может не приходить idPet
            if ($idPet && !$visitGovService->getPets()->where(['id' => $idPet])->exists()) {
                $pet = Pets::find()->where(['id' => $idPet])->one();
                $visitGovService->link('pets', $pet);
            }

            if (!empty($serviceParams['params'])) {
                foreach ($serviceParams['params'] as $param) {
                    $paramModel = new VisitServiceParamModel([
                        'id' => isset($param['id']) ? $param['id'] : null,
                        'id_param' => $param['id_param'],
                        'value' => $param['value'],
                        'idService' => $serviceParams['id_service'],
                    ]);
                    if (
                        !$paramModel->validate() ||
                        !$paramModel->save(
                            $visitGovService->id_visit,
                            $visitGovService->id,
                            $visitGovService->id_pet
                        )
                    ) {
                        $this->addErrors($paramModel->getErrors());

                        return null;
                    }
                }
            }
        } else {
            $this->addErrors($visitGovService->getErrors());

            return null;
        }

        return $visitGovService;
    }

    /**
     * @param VisitsGovServices $visitGovService
     */
    private function saveVisitParamsByGeneral(?VisitsGovServices $visitGovService)
    {
        if (!$visitGovService) {
            return;
        }

        (new VisitParamsModel())
            ->saveVisitParamsByGeneral($visitGovService);
    }


    /**
     * Если указан id_specialist записываем связь в visits_specialists.
     * Если это редактирование - проверяем не изменился ли. Если отличаются - удаляем старый
     */
    private function linkSpecialist()
    {
        if (in_array($this->scenario, [self::SCENARIO_UPDATE_VISIT, self::SCENARIO_UPDATE_SIMPLIFIED_VISIT])) {
            Yii::$app->db
                ->createCommand()
                ->delete(VisitsSpecialists::tableName(), ['id_visit' => $this->visit->id])
                ->execute();
        }

        if ($this->id_specialist === null) {
            // живая очередь
            return true;
        }

        $link = new VisitsSpecialists([
            'id_visit' => $this->visit->id,
            'id_specialist' => $this->id_specialist,
        ]);

        $result = $link->save();
        if ($result === false) {
            $this->addError('id_specialist', 'Ошибка при сохранении связи специалиста с приемом');
        }

        return $result;
    }

    /**
     * Типы каналов записи
     */
    private static function mapShiftTypes()
    {
        $rows = (new Query())
            ->select(['id', 'type'])
            ->from(ShiftType::tableName())
            ->orderBy(['id' => SORT_ASC])
            ->all();

        self::$shiftTypesMap = ArrayHelper::map($rows, 'id', 'type');
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'preferences_document' => 'Номер документа, подтверждающего льготу',
        ];
    }
}
