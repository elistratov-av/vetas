<?php


namespace app\modules\animalid\models\db;



use yii\db\ActiveRecord;

/**
 * @property string $type
 * @property string $field
 * @property string $value
 * @property string $dir
 * @property integer $rule_num
 */
class FilterModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'animalid.filter';
    }

    public function rules()
    {
        return [
            [['type'], 'safe'],
            [['field', 'value', 'type', 'value', 'rule_num'], 'required'],
            [['type', 'field', 'value', 'dir'], 'string', 'max' => 255],
            [['rule_num'], 'integer'],
        ];
    }
}
