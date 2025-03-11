<?php

namespace app\models\db\elk;

use app\models\db\ActiveRecord;

/**
 * This is the model class for table "elk.log".
 *
 * @property int $id
 * @property string $xml
 * @property string $created_at
 * @property string $updated_at
 */
class ElkLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'elk.log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['xml'], 'string'],
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
            'xml' => 'Xml',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
