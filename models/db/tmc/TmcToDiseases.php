<?php

namespace app\models\db\tmc;

use Yii;
use app\models\db\ActiveRecord;
use app\models\db\Diseases;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "tmc.tmc_to_diseases".
 *
 * @property int $id
 * @property int $id_tmc
 * @property int $id_disease
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $type_tmc
 */
class TmcToDiseases extends ActiveRecord
{
    const ALLOWED_TYPES_TMC = [
        TmcBase::TYPE_DRUG, TmcBase::TYPE_VACCINE
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.tmc_to_diseases';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_tmc', 'id_disease', 'type_tmc'], 'required'],
            [['id_tmc', 'id_disease', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_tmc', 'id_disease', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'], 'in',
                'range' => self::ALLOWED_TYPES_TMC,
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['id_tmc', 'id_disease'], 'unique', 'targetAttribute' => ['id_tmc', 'id_disease']],
            [['id_disease'], 'exist', 'skipOnError' => true, 'targetClass' => Diseases::class, 'targetAttribute' => ['id_disease' => 'id']],
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
            'id_disease' => 'Id Disease',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'type_tmc' => 'Type Tmc',
        ];
    }

    /**
     * Привязывает заболевания к ТМЦ пачкой.
     * ВСЕ РАНЕЕ УСТАНОВЛЕННЫЕ СВЯЗИ ТМЦ-ЗАБОЛЕВАНИЯ - УДАЛЯЮТСЯ
     * @param $type_tmc
     * @param $id_tmc
     * @param $diseases_ids
     * @throws InvalidConfigException
     */
    public static function bathLinkDiseasesToTmc($type_tmc, $id_tmc, $diseases_ids = [])
    {
        if (!in_array($type_tmc, self::ALLOWED_TYPES_TMC)) {
            throw new InvalidConfigException('У данного типа ТМЦ не может быть связи с видом животного');
        }

        TmcToDiseases::deleteAll([
            'id_tmc' => $id_tmc,
            'type_tmc' => $type_tmc,
        ]);

        // Это был запрос на очистку - новых нет
        if (empty($diseases_ids)) {
            return;
        }

        // Новые связи
        foreach ($diseases_ids as $id_disease) {

            if (!is_int($id_disease)){
                throw new InvalidConfigException('ID заболеваний должны быть числовыми значениями');
            }

            $link = new TmcToDiseases([
                'id_tmc' => $id_tmc,
                'type_tmc' => $type_tmc,
                'id_disease' => $id_disease,
            ]);

            if (!$link->save()) {
                $errors = $link->getErrorSummary(true);
                throw new InvalidConfigException(empty($errors) ? 'Ошибка при сохранении связи ТМЦ с заболеванием' : implode("\n", array_values($errors)));
            }
        }
    }
}
