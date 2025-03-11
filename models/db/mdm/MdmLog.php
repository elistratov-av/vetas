<?php

namespace app\models\db\mdm;

use app\models\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "mdm.log".
 *
 * @property int $id
 * @property string $data
 * @property string $created_at
 * @property string $updated_at
 */
class MdmLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mdm.log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data' => 'Json data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!empty($this->data) && is_string($this->data)) {
            try {
                $this->data = Json::decode($this->data);
            } catch (\Throwable $e) {
                $this->data = (string)$this->data;
            }
        }

        return parent::beforeSave($insert);
    }
}
