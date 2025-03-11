<?php

namespace app\models\db\tmc;

use Yii;
use app\models\db\ActiveRecord;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "tmc.category_to_tmc".
 *
 * @property int $id
 * @property int $id_category
 * @property int $id_tmc
 * @property string $type_tmc
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class CategoryToTmc extends ActiveRecord
{

    const ALLOWED_TYPES_TMC = [
        TmcBase::TYPE_DRUG, TmcBase::TYPE_EXP_MATERIAL, TmcBase::TYPE_VACCINE
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.category_to_tmc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_category', 'id_tmc', 'type_tmc'], 'required'],
            [['id_category', 'id_tmc', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_category', 'id_tmc', 'created_by', 'updated_by'], 'integer'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'], 'in',
                'range' => self::ALLOWED_TYPES_TMC,
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['created_at', 'updated_at'], 'safe'],
            [['id_category', 'id_tmc', 'type_tmc'], 'unique', 'targetAttribute' => ['id_category', 'id_tmc', 'type_tmc']],
            [['id_category'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['id_category' => 'id']],
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
            'id_category' => 'Id Category',
            'id_tmc' => 'Id Tmc',
            'type_tmc' => 'Type Tmc',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Привязывает категории к ТМЦ пачкой.
     * ВСЕ РАНЕЕ УСТАНОВЛЕННЫЕ СВЯЗИ ТМЦ-КАТЕГРИЯ - УДАЛЯЮТСЯ
     * @param $type_tmc
     * @param $id_tmc
     * @param $category_ids
     * @throws InvalidConfigException
     */
    public static function bathLinkCategoriesToTmc($type_tmc, $id_tmc, $category_ids = [])
    {
        if (!in_array($type_tmc, self::ALLOWED_TYPES_TMC)) {
            throw new InvalidConfigException('У данного типа ТМЦ не может быть категорий');
        }

        CategoryToTmc::deleteAll([
            'id_tmc' => $id_tmc,
            'type_tmc' => $type_tmc,
        ]);

        // Это был запрос на очистку - новых нет
        if (empty($category_ids)) {
            return;
        }

        // Новые связи
        foreach ($category_ids as $category_id) {

            if (!is_int($category_id)){
                throw new InvalidConfigException('ID категорий должны быть числовыми значениями');
            }

            $link = new CategoryToTmc([
                'id_tmc' => $id_tmc,
                'type_tmc' => $type_tmc,
                'id_category' => $category_id,
            ]);

            if (!$link->save()) {
                $errors = $link->getErrorSummary(true);
                throw new InvalidConfigException(empty($errors) ? 'Ошибка при сохранении связи категория-ТМЦ' : implode("\n", array_values($errors)));
            }
        }
    }

}
