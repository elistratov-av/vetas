<?php

namespace app\models\db\tmc;

use Yii;
use app\models\db\Species;
use yii\base\InvalidConfigException;


/**
 * This is the model class for table "tmc.tmc_to_species".
 *
 * @property int $id
 * @property int $id_tmc
 * @property int $id_species
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $type_tmc
 */
class TmcToSpecies extends \yii\db\ActiveRecord
{

    const ALLOWED_TYPES_TMC = [
        TmcBase::TYPE_DRUG, TmcBase::TYPE_VACCINE
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.tmc_to_species';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_tmc', 'id_species', 'type_tmc'], 'required'],
            [['id_tmc', 'id_species', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_tmc', 'id_species', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'], 'in',
                'range' => self::ALLOWED_TYPES_TMC,
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['id_tmc', 'id_species'], 'unique', 'targetAttribute' => ['id_tmc', 'id_species']],
            [['id_species'], 'exist', 'skipOnError' => true, 'targetClass' => Species::class, 'targetAttribute' => ['id_species' => 'id']],
            [['id_tmc', 'type_tmc'], 'exist', 'skipOnError' => true, 'targetClass' => TmcBase::class, 'targetAttribute' => ['id_tmc' => 'id', 'type_tmc' => 'type']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_tmc' => 'Id Tmc',
            'id_species' => 'Id Species',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'type_tmc' => 'Type Tmc',
        ];
    }

    /**
     * Привязывает виды животных к ТМЦ пачкой.
     * ВСЕ РАНЕЕ УСТАНОВЛЕННЫЕ СВЯЗИ ТМЦ-ВИДЫ ЖИВОТНЫХ - УДАЛЯЮТСЯ
     * @param $type_tmc
     * @param $id_tmc
     * @param $species_ids
     * @throws InvalidConfigException
     */
    public static function bathLinkSpeciesToTmc($type_tmc, $id_tmc, $species_ids = [])
    {
        if (!in_array($type_tmc, self::ALLOWED_TYPES_TMC)) {
            throw new InvalidConfigException('У данного типа ТМЦ не может быть связи с видом животного');
        }

        TmcToSpecies::deleteAll([
            'id_tmc' => $id_tmc,
            'type_tmc' => $type_tmc,
        ]);

        // Это был запрос на очистку - новых нет
        if (empty($species_ids)) {
            return;
        }

        // Новые связи
        foreach ($species_ids as $species_id) {

            if (!is_int($species_id)){
                throw new InvalidConfigException('ID видов животных должны быть числовыми значениями');
            }

            $link = new TmcToSpecies([
                'id_tmc' => $id_tmc,
                'type_tmc' => $type_tmc,
                'id_species' => $species_id,
            ]);

            if (!$link->save()) {
                $errors = $link->getErrorSummary(true);
                throw new InvalidConfigException(empty($errors) ? 'Ошибка при сохранении связи ТМЦ с видом животного' : implode("\n", array_values($errors)));
            }
        }
    }
}
