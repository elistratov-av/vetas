<?php


namespace app\modules\animalid\models\db;



use yii\db\ActiveRecord;

/**
 * @property string $type
 * @property string $field
 * @property string $value_from
 * @property string $value_to
 * @property string $dir
 * @property integer $rule_num
 */
class ConverterModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'animalid.converter';
    }

    public function rules()
    {
        return [
            [['type'], 'safe'],
            [['field', 'value_from', 'value_to', 'type', 'rule_num'], 'required'],
            [['type', 'field', 'value_from', 'value_to', 'dir'], 'string', 'max' => 255],
            [['rule_num'], 'integer'],
        ];
    }
}
