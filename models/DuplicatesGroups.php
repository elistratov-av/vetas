<?

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

class DuplicatesGroups extends ActiveRecord
{

    public static function tableName()
    {
        return 'public.duplicates_groups';
    }

    public function rules()
    {
        return [
            [['duplicates', 'type', 'status'], 'required'],
            ['duplicates', 'each', 'rule' => ['integer']],
            ['duplicates', function ($attribute, $params, $validator) {
                if (!$this->isNewRecord && !$this->isAttributeChanged('duplicates')) return;
                $this->uniqueArray($attribute, $params, $validator);
            }],
            ['type', 'in', 'range' => ['owner', 'pet', 'undefined']],
            ['status', 'in', 'range' => ['in_process', 'fail', 'complete', 'manual_control']],
            ['comment', 'string', 'max' => 64]
        ];
    }

    public function uniqueArray($attribute, $params, $validator) {
        $duplicates = is_array($this->$attribute) ? $this->$attribute : $this->$attribute->getValue();
        sort($duplicates);
        $query  = static::find()
        ->where(['status' => 'in_process'])
        ->andWhere(['type' => $this->type]);
        if (!$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }
        $duplicatesGroups = $query->all();
        foreach($duplicatesGroups as $group) {
            $duplicatesGroup = $group->duplicates->getValue();
            sort($duplicatesGroup);
            if (Json::encode($duplicatesGroup) == Json::encode($duplicates)) {
                $this->addError($attribute, 'Группа с такими элементами уже существует.');
                return false;
            }
        }
        return true;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at']
                ],
                'value' => new Expression('NOW()'),
            ]
        ];
    }
}
