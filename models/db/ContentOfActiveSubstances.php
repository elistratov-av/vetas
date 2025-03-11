<?php


namespace app\models\db;


use yii\db\ActiveQuery;

/**
 * This is the model class for table "content_of_active_substances".
 *
 * @property int $id
 * @property double $unit
 * @property int $id_tmc
 * @property int $id_active_substance
 * @property int $id_measure Ссылка на справочник единиц измерений
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 *
 */
class ContentOfActiveSubstances extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.content_of_active_substances';
    }

    /**
     * @return ActiveQuery
     */
    public function getDrug()
    {
        return $this->hasOne(Drugs::class,['id' => 'id_tmc']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSubstance()
    {
        return $this->hasOne(ActiveSubstances::class, ['id' => 'id_active_substance']);
    }
}