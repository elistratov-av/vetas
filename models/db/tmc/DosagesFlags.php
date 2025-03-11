<?php

namespace app\models\db\tmc;

use app\models\db\ActiveRecord;

/**
 * Флаги для дозировок
 *
 * @property int $id
 * @property int|null $id_dosages ID дозировки
 * @property bool|null $for_act Флаг: для актов
 */
class DosagesFlags extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.dosages_flags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_dosages'], 'default', 'value' => null],
            [['id_dosages'], 'integer'],
            [['for_act'], 'boolean'],
            [['id_dosages'], 'exist', 'skipOnError' => true, 'targetClass' => Dosages::class, 'targetAttribute' => ['id_dosages' => 'id']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDosages()
    {
        return $this->hasOne(Dosages::class, ['id' => 'id_dosages']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_dosages' => 'Id дозировки',
            'for_act' => 'Для актов',
        ];
    }
}
