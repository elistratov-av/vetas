<?php


namespace app\models\db;


use yii\db\ActiveQuery;

/**
 * This is the model class for table "active_substances".
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 * @property string $description
 *
 */
class ActiveSubstances extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.active_substances';
    }

    /**
     * @return ActiveQuery
     */
    public function getDrugs()
    {
        return $this->hasMany(Drugs::class,['id' => 'id_tmc'])
            ->viaTable('content_of_active_substances',['id_active_substance' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getContent_of_active_substances()
    {
        return $this->hasMany(ContentOfActiveSubstances::class, ['id_active_substance' => 'id']);}
}