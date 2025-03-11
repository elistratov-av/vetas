<?php

namespace app\models\db\statistic;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "statistic.mosru_organizations_stat".
 *
 * @property int $id
 * @property int $id_organization
 * @property string $date
 * @property int $total Общее кол-во записей с mos.ru
 * @property int $canceled_by_owner Кол-во записей отмененных по инициативе владельца
 * @property int $canceled_by_org Кол-во записей отмененных по инициативе организации
 * @property int $moved Кол-во перенесенных записей
 * @property int $cats_visits Кол-во созданных записей на прием с кошками
 * @property int $dogs_visits Кол-во созданных записей на прием с собаками
 * @property int $cats_finished_visits Кол-во проведенных приемов для кошек
 * @property int $dogs_finished_visits Кол-во проведенных приемов для собак
 */
class MosruOrganizationsStat extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'statistic.mosru_organizations_stat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_organization', 'date'], 'required'],
            [['id_organization', 'total', 'canceled_by_owner', 'canceled_by_org', 'moved', 'cats_visits', 'dogs_visits', 'cats_finished_visits', 'dogs_finished_visits'], 'default', 'value' => null],
            [['id_organization', 'total', 'canceled_by_owner', 'canceled_by_org', 'moved', 'cats_visits', 'dogs_visits', 'cats_finished_visits', 'dogs_finished_visits'], 'integer'],
            [['date'], 'safe'],
            [['date', 'id_organization'], 'unique', 'targetAttribute' => ['date', 'id_organization']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_organization' => 'Id Organization',
            'date' => 'Date',
            'total' => 'Total',
            'canceled_by_owner' => 'Canceled By Owner',
            'canceled_by_org' => 'Canceled By Org',
            'moved' => 'Moved',
            'cats_visits' => 'Cats Visits',
            'dogs_visits' => 'Dogs Visits',
            'cats_finished_visits' => 'Cats Finished Visits',
            'dogs_finished_visits' => 'Dogs Finished Visits',
        ];
    }
}
