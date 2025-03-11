<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "discount".
 *
 * @property int $id
 * @property string $name Наименование скидки
 * @property int $value Значение скидки в процентах
 * @property bool $is_deleted Флаг: удалено
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $id_organization
 * @property boolean $is_system_discount
 */
class Discount extends ActiveRecord
{
    const VACCINE_STATION_DISCOUNT_NAME = 'Прививочные Пункты';
    const FLAT_DISCOUNT_NAME = 'Поквартирные обходы';
    const SHELTER_DISCOUNT_NAME = 'Приюты';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'discount';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['name'], FullTrimValidator::class],
            [['value', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['value', 'created_by', 'updated_by'], 'integer'],
            [['is_deleted', 'is_system_discount'], 'boolean'],
            [['is_system_discount'], function($attribute, $params, $validator) {
                if ($this->is_system_discount === true) {
                    $this->addError($attribute, "Нельзя создать системную запись");
                }
            }],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            ['name', function($attribute, $params, $validator) {
                if (($this->isNewRecord || $this->isAttributeChanged('name') && $this->is_deleted !== true)){
                    $check = self::find()
                        ->where([
                            'AND',
                            ['name' => $this->name],
                            ['id_organization' => $this->id_organization],
                            ['is_deleted' => false],
                        ])->exists()
                    ;
                    if ($check) {
                        $this->addError($attribute, "Название скидки должно быть уникальным в пределах организации");
                    }
                }
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование скидки',
            'value' => 'Значение скидки в процентах',
            'is_deleted' => 'Флаг: удалено',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Удаленные не могут редактироваться
     */
    public function isReadOnly()
    {
        return (
            !$this->isNewRecord && $this->is_deleted == true
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }
}
