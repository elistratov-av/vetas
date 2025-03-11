<?php

namespace app\models\db\report;

use app\models\db\ActiveRecord;

/**
 * Нумерация для отчетов
 *
 * @property int $id
 * @property int $id_report id отчета
 * @property string $version Версия отчета
 * @property string $hash Хэш
 * @property integer $number Номер
 * @property string $created_at Дата создания
 */
class Number extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report.numbers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_report', 'version', 'hash', 'number'], 'required'],
            [['id_report'], 'integer'],
            [['version'], 'string', 'max' => 3],
            [['hash'], 'string', 'max' => 32],
            [['number'], 'integer'],
            [['created_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['id_report', 'version', 'hash', 'created_at', 'number'], 'unique', 'targetAttribute' => ['id_report', 'version', 'hash', 'created_at', 'number']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'id_report'  => 'Id отчета',
            'version'    => 'Версия отчета',
            'hash'       => 'Хэш',
            'number'     => 'Номер',
            'created_at' => 'Дата создания',
        ];
    }
}
