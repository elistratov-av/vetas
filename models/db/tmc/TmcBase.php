<?php

namespace app\models\db\tmc;

use app\common\validators\FullTrimValidator;
use app\models\db\ActiveRecord;
use app\models\db\Files;
use app\models\db\Measures;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetRabiesVaccination;
use app\models\db\VisitServiceTmc;
use Yii;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "tmc.tmc".
 *
 * @property int $id id
 * @property string $type ТИП
 * @property string $name Наименование
 * @property string $basis Основание препарата
 * @property string $dealer Представительство
 * @property string $description Описание
 * @property string $excipients Вспомогательные вещества
 * @property string $form_description Описание лекарственной формы
 * @property int $id_measure Ссылка на справочник единиц измерений
 * @property double $unit Содержание активных веществ на ...
 * @property string $packaging Упаковка
 * @property string $produced Произведено
 * @property string $registered Зарегистрировано
 * @property bool $is_deleted Удалено
 * @property string $created_at Дата создания
 * @property int $created_by Автор добавления
 * @property string $updated_at Дата изменения
 * @property int $updated_by Автор последнего изменения
 * @property boolean $is_uncountable Неисчислимый расходный материал. Не списывается в приеме
 *
 * @property Files[] $files
 * @property Category[] $categories
 * @property Balance[] $balances
 * @property Measures $measure
 * @property Dosages $dosages
 * @property ProductionForm[] $production_forms
 * @property PetOtherVaccinations[] $petOtherVaccinations
 * @property PetRabiesVaccination[] $petRabiesVaccinations
 * @property VisitServiceTmc[] $visitServiceTmc
 */
class TmcBase extends ActiveRecord
{
    //('drug', 'equipment', 'exp_material', 'vaccine');
    const TYPE_VACCINE = 'vaccine';             //вакцина
    const TYPE_DRUG = 'drug';                   //лекарство
    const TYPE_EQUIPMENT = 'equipment';         //оборудование
    const TYPE_EXP_MATERIAL = 'exp_material';   //расходные материалы

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.tmc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // NAME
            [['name'], 'required'],
            [['name'], 'string'],
            [['name'], FullTrimValidator::class],

            // TYPE
            [['type'], 'required'],
            [['type'], 'string'],
            [
                ['type'], 'in',
                'range' => [
                    TmcBase::TYPE_VACCINE, TmcBase::TYPE_EQUIPMENT,
                    TmcBase::TYPE_EXP_MATERIAL, TmcBase::TYPE_DRUG
                ],
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],

            // BASIS, EXCIPIENTS, FORM_DESCRIPTION, PACKAGING
            [['basis', 'excipients', 'form_description', 'packaging'], 'string'],
            [['basis', 'excipients', 'form_description', 'packaging'], FullTrimValidator::class],

            // PRODUCED, REGISTERED
            [['produced', 'registered'], 'string', 'max' => 255],
            [['produced', 'registered'], FullTrimValidator::class],
            [['produced', 'registered'], 'required', 'when' => function($model) {
                return $model->type == TmcBase::TYPE_VACCINE || $model->type == TmcBase::TYPE_DRUG;
            }],

            // DEALER, DESCRIPTION
            [['dealer', 'description'], 'string', 'max' => 255],
            [['dealer', 'description'], FullTrimValidator::class],

            // UNIT
            [['unit'], 'number'],

            // IS_DELETED
            [['is_deleted'], 'boolean'],
            [['is_deleted'], 'default', 'value' => false],

            // IS UNCOUNTABLE
            [['is_uncountable'], 'boolean'],
            [['is_uncountable'], 'required', 'when' => function($model) {
                return $model->type == TmcBase::TYPE_EXP_MATERIAL;
            }],

            // ID_MEASURE
            [['id_measure'], 'default', 'value' => null],
            [['id_measure'], 'integer'],
            [
                'id_measure',
                'exist',
                'skipOnError' => true,
                'targetClass' => Measures::class,
                'targetAttribute' => ['id_measure' => 'id']
            ],
            [['id_measure'], 'required', 'when' => function($model) {
                return
                    $model->type == TmcBase::TYPE_VACCINE ||
                    $model->type == TmcBase::TYPE_DRUG ||
                    $model->type == TmcBase::TYPE_EXP_MATERIAL;
            }],

            // CREATED_BY, UPDATED_AT
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'type' => 'ТИП',
            'name' => 'Наименование',
            'basis' => 'Основание препарата',
            'dealer' => 'Представительство',
            'description' => 'Описание',
            'excipients' => 'Вспомогательные вещества',
            'form_description' => 'Описание лекарственной формы',
            'id_measure' => 'Стандартная единица измерения',
            'unit' => 'Содержание активных веществ на ...',
            'packaging' => 'Упаковка',
            'produced' => 'Произведено',
            'registered' => 'Зарегистрировано',
            'is_deleted' => 'Удалено',
            'is_uncountable' => 'Неисчислимый расходный материал. Не списывается в приеме',

            'created_at' => 'Дата создания',
            'created_by' => 'Автор добавления',
            'updated_at' => 'Дата изменения',
            'updated_by' => 'Автор последнего изменения',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->andWhere(['entity_type' => $this->type]);
    }

    /**
     * Недоступно для оборудования
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'id_category'])
            ->viaTable('tmc.category_to_tmc', ['id_tmc' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * Недоступно для оборудования
     *
     * @return \yii\db\ActiveQuery
     */
    public function hasCategorySlug($slug)
    {
        return (bool)$this
            ->getCategories()
            ->andWhere(['tmc.category.slug' => $slug])
            ->count();
    }

    /**
     * Балансовые записи
     * @return \yii\db\ActiveQuery
     */
    public function getBalances()
    {
        return $this->hasMany(Balance::class, ['id_tmc' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * Недоступно для оборудования
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measures::class, ['id' => 'id_measure']);
    }

    /**
     * Недоступно для оборудования и расходников
     * @return \yii\db\ActiveQuery
     */
    public function getDosages()
    {
        return $this->hasMany(Dosages::class, ['id_tmc' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmc()
    {
        return $this->hasMany(VisitServiceTmc::class, ['id_tmc' => 'id']);
    }

    /**
     * Недоступно для оборудования
     * @return \yii\db\ActiveQuery
     */
    public function getProduction_forms()
    {
        return $this->hasMany(ProductionForm::class, [
            'id_tmc' => 'id', 'type_tmc' => 'type'
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetOtherVaccinations()
    {
        return $this->hasMany(PetOtherVaccinations::class, ['id_vaccine' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetRabiesVaccinations()
    {
        return $this->hasMany(PetRabiesVaccination::class, ['id_vaccine' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * Возвращает список доступных полей для каждого типа ТМЦ
     *
     * @param $type_tmc
     * @return string[]
     * @throws InvalidConfigException
     */
    public static function getFieldsList($type_tmc)
    {
        switch ($type_tmc){
            case TmcBase::TYPE_EXP_MATERIAL:
                $tmc_fields = (new TmcExpMaterial())->fields();
                break;

            case TmcBase::TYPE_VACCINE:
                $tmc_fields = (new TmcVaccine())->fields();
                break;

            case TmcBase::TYPE_DRUG:
                $tmc_fields = (new TmcDrug())->fields();
                break;

            case TmcBase::TYPE_EQUIPMENT:
                $tmc_fields = (new TmcEquipment())->fields();
                break;

            default:
                throw new InvalidConfigException('Неизвестный тип ТМЦ');
        }

        return $tmc_fields;
    }



//    public static function instantiate($row)
//    {
//        return parent::instantiate($row); // TODO: Change the autogenerated stub
//    }

}
