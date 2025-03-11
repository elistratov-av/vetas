<?php

namespace app\models\db\tmc;

use app\models\db\ActiveRecord;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\models\db\VisitServiceTmc;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "tmc.balance".
 *
 * @property int $id id
 * @property int $id_tmc id ТМЦ
 * @property string $type_tmc Тип ТМЦ
 * @property int $id_organization id организации, у которой на балансе стоит ТМЦ
 * @property int $id_specialist id специалиста у которого на балансе стоит ТМЦ
 * @property int $id_production_form id формы выпуска
 * @property-read  string $count Кол-во в единицах измерения (значимый параметр)
 * @property string $expiration_date Дата истечения срока годности
 * @property string $production_date Дата изготовления
 * @property string $registration_date Дата постановки на баланс
 * @property string $inventory_number Инвентарный номер
 * @property string $price Цена
 * @property string $equipment_condition Состояние (для оборудования)
 * @property string $manufactured_number Заводской номер
 * @property int $old_id Старый ID для контроля переливки и корректировки связей. После - удалить
 * @property string $created_at Дата создания
 * @property int $created_by Автор добавления
 * @property string $updated_at Дата изменения
 * @property int $updated_by Автор последнего изменения
 * @property-read string $count_in_production_form Кол-во в формах производства (справочный параметр)
 * @property Specialists $specialist
 * @property Organizations $organization
 * @property TmcBase $tmc
 * @property ProductionForm $production_form
 * @property Dosages $dosages
 * @property Category[] $categories
 */
class Balance extends ActiveRecord
{

    /**
     * Некоторые поля нельзя сохранять/обновлять - они рассчитываются триггером
     */
    const READ_ONLY_FIELDS = [
        //'count',
        //'count_in_production_form'
    ];

    const EQUIPMENT_CONDITION_WORK = 'W';
    const EQUIPMENT_CONDITION_NOT_WORK = 'N';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.balance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // TYPE
            [['type_tmc'], 'required'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'],
                'in',
                'range'       => [
                    TmcBase::TYPE_VACCINE,
                    TmcBase::TYPE_EQUIPMENT,
                    TmcBase::TYPE_EXP_MATERIAL,
                    TmcBase::TYPE_DRUG
                ],
                'strict'      => true,
                'skipOnEmpty' => false,
                'skipOnError' => false
            ],
            [['id_tmc', 'id_organization', 'registration_date', 'inventory_number'], 'required'],
            [['id_tmc', 'id_organization', 'id_specialist', 'id_production_form', 'old_id', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_tmc', 'id_organization', 'id_specialist', 'id_production_form', 'old_id', 'created_by', 'updated_by'], 'integer'],

            [['expiration_date', 'registration_date', 'production_date'], 'date'],
            [['created_at', 'updated_at'], 'safe'],
            [['inventory_number'], 'string', 'max' => 255],

            // PRICE
            [['price'], 'number'],
            [
                ['price'],
                'required',
                'when' => function ($model) {
                    return in_array(
                        $model->type_tmc, [TmcBase::TYPE_DRUG, TmcBase::TYPE_VACCINE, TmcBase::TYPE_EXP_MATERIAL]
                    );
                }
            ],
            // EQUIPMENT_CONDITION, MANUFACTURED_NUMBER
            [
                ['equipment_condition', 'manufactured_number'],
                'required',
                'when' => function ($model) {
                    return $model->type_tmc == TmcBase::TYPE_EQUIPMENT;
                }
            ],
            [['manufactured_number'], 'string', 'max' => 255],
            [['equipment_condition'], 'string', 'max' => 1],
            [
                ['equipment_condition'],
                'in',
                'range'       => [
                    Balance::EQUIPMENT_CONDITION_WORK,
                    Balance::EQUIPMENT_CONDITION_NOT_WORK,
                ],
                'strict'      => true,
                'skipOnEmpty' => true,
                'skipOnError' => false
            ],

            // Только одна строка на каждого
            // Если отличается ценой - то несколько строк
            [
                ['inventory_number'],
                'unique',
                'targetAttribute' => [
                    'type_tmc',
                    'id_organization',
                    'id_specialist',
                    'inventory_number',
                    'price'
                ]
            ],
            // Связи
            [
                ['id_production_form'],
                'required',
                'when' => function ($model) {
                    return in_array(
                        $model->type_tmc, [TmcBase::TYPE_DRUG, TmcBase::TYPE_VACCINE, TmcBase::TYPE_EXP_MATERIAL]
                    );
                }
            ],
            [
                ['id_production_form'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => ProductionForm::class,
                'targetAttribute' => ['id_tmc' => 'id_tmc', 'type_tmc' => 'type_tmc', 'id_production_form' => 'id']
            ],
            [
                ['id_tmc'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => TmcBase::class,
                'targetAttribute' => ['id_tmc' => 'id', 'type_tmc' => 'type']
            ],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => Specialists::class, 'targetAttribute' => ['id_specialist' => 'id']],
            // дата истечения срока годности не может быть меньше даты изготовления и меньше даты постановки на баланс
            ['expiration_date', 'compare', 'compareAttribute' => 'production_date', 'operator' => '>', 'enableClientValidation' => false],
            ['expiration_date', 'compare', 'compareAttribute' => 'registration_date', 'operator' => '>', 'enableClientValidation' => false],

            // дата изготовления не может быть  больше даты постановки на баланс
            ['production_date', 'compare', 'compareAttribute' => 'registration_date', 'operator' => '<=', 'enableClientValidation' => false,
                'when' => function($model) {
                    return in_array(
                        $model->type_tmc, [TmcBase::TYPE_DRUG, TmcBase::TYPE_VACCINE, TmcBase::TYPE_EXP_MATERIAL]
                    );
            }],
            [
                'production_date',
                'compare', 'compareValue' => date("Y-m-d"), 'operator' => '<=',
                'message' => 'Дата постановки на баланс не может быть в будущем'
            ],
            [
                'registration_date',
                'compare', 'compareValue' => date("Y-m-d"), 'operator' => '<=',
                'message' => 'Дата изготовления не может быть в будущем'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                       => 'id',
            'id_tmc'                   => 'id ТМЦ',
            'type_tmc'                 => 'Тип ТМЦ',
            'id_organization'          => 'id организации, у которой на балансе стоит ТМЦ',
            'id_specialist'            => 'id специалиста у которого на балансе стоит ТМЦ',
            'id_production_form'       => 'id формы выпуска',
            'count'                    => 'Кол-во в единицах измерения (значимый параметр)',
            'expiration_date'          => 'Дата истечения срока годности',
            'registration_date'        => 'Дата постановки на баланс',
            'production_date'          => 'Дата изготовления',
            'inventory_number'         => 'Инвентарный номер',
            'price'                    => 'Цена',
            'equipment_condition'      => 'Состояние (для оборудования)',
            'manufactured_number'      => 'Заводской номер',
            'old_id'                   => 'Старый ID для контроля переливки и корректировки связей. После - удалить',
            'created_at'               => 'Дата создания',
            'created_by'               => 'Автор добавления',
            'updated_at'               => 'Дата изменения',
            'updated_by'               => 'Автор последнего изменения',
            'count_in_production_form' => 'Кол-во в формах производства (справочный параметр)',
        ];
    }

    /**
     * @param $type_tmc
     * @return string[]
     */
    public static function getFieldsList($type_tmc)
    {
        switch ($type_tmc) {
            case TmcBase::TYPE_VACCINE:
            case TmcBase::TYPE_DRUG:
            case TmcBase::TYPE_EXP_MATERIAL:
                $tmc_fields = [
                    'id',
                    'id_tmc',
                    'type_tmc',
                    'id_organization',
                    'id_specialist',
                    'id_production_form',
                    'count',
                    'expiration_date',
                    'production_date',
                    'registration_date',
                    'inventory_number',
                    'price',
                    //'equipment_condition',
                    //'manufactured_number',
                    'count_in_production_form',
                    'old_id',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ];
                break;

            case TmcBase::TYPE_EQUIPMENT:
                $tmc_fields = [
                    'id',
                    'id_tmc',
                    'type_tmc',
                    'id_organization',
                    'id_specialist',
                    //'id_production_form',
                    //'count',
                    //'expiration_date',
                    'registration_date',
                    'production_date',
                    'inventory_number',
                    //'price',
                    'equipment_condition',
                    'manufactured_number',
                    //'count_in_production_form',
                    'old_id',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',

                ];
                break;

            default:
                throw new InvalidConfigException('Неизвестный тип ТМЦ');
        }

        return $tmc_fields;
    }

    /**
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {

        if (empty($attributeNames)) {
            $attributeNames = array_keys($this->getAttributes());
        }
        // Некоторые поля нельзя сохранять/обновлять - они рассчитываются триггером
        $attributeNames = array_diff($attributeNames, self::READ_ONLY_FIELDS);

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'id_specialist']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmc()
    {
        return $this->hasOne(TmcBase::class, ['id' => 'id_tmc', 'type' => 'type_tmc']);
    }

    /**
     * Недоступно для оборудования и расходников
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDosages()
    {
        return $this->hasMany(Dosages::class, ['id_tmc' => 'id_tmc', 'type_tmc' => 'type_tmc']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduction_form()
    {
        return $this->hasOne(ProductionForm::class, [
            'id_tmc'   => 'id_tmc',
            'type_tmc' => 'type_tmc',
            'id'       => 'id_production_form'
        ]);
    }

    /**
     * Недоступно для оборудования
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'id_category'])
            ->viaTable('tmc.category_to_tmc', ['id_tmc' => 'id_tmc', 'type_tmc' => 'type_tmc']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmc(){
        return $this->hasOne(VisitServiceTmc::class, ['id_balance_tmc'=>'id']);
    }
}
