<?php

namespace app\models\db\efsp;

use Yii;

/**
 * This is the model class for table "efsp.fias_data".
 *
 * @property int $id
 * @property string $fias_uid ФИАС UID
 * @property string $api_url API URL с которого получили данные
 * @property string $date Дата получения результата
 * @property array $result Результат получения адреса
 */
class FiasData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'efsp.fias_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'result'], 'safe'],
            [['fias_uid'], 'string', 'max' => 42],
            [['api_url'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fias_uid' => 'Fias Uid',
            'api_url' => 'Api Url',
            'date' => 'Date',
            'result' => 'Result',
        ];
    }
}
