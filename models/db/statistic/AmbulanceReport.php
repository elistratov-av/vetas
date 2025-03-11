<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.06.19
 * Time: 16:58
 */

namespace app\models\db\statistic;


use app\models\db\ActiveRecord;

class AmbulanceReport extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'statistic.ambulance_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_crew', 'date'], 'required'],
            [[
                'id_specialist',
                'spec_name',
                'total_calls',
                'total_cancelled',
                'cancelled_by_owner',
                'cancelled_by_org',
                'total_house_calls',
                'total_commercial',
                'total_free_for_blind',
                'total_free_for_the_rest'], 'default', 'value' => null],
            [[
                'id_specialist',
                'total_calls',
                'total_cancelled',
                'cancelled_by_owner',
                'cancelled_by_org',
                'total_house_calls',
                'total_commercial',
                'total_free_for_blind',
                'total_free_for_the_rest'], 'integer'],
            [['spec_name'], 'string'],
            [['date'], 'safe'],
            [['date', 'id_crew'], 'unique', 'targetAttribute' => ['date', 'id_crew']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_specialist' => 'ID Crew',
            'spec_name' => 'Crew',
            'total_calls' => 'Total Calls',
            'total_cancelled' => 'Total Cancelled',
            'cancelled_by_owner' => 'Cancelled By Owner',
            'cancelled_by_org' => 'CancelledByOrg',
            'total_house_calls' => 'Total House Calls',
            'total_commercial' => 'Total Commercial',
            'total_free_for_blind' => 'Total Free For Blind',
            'total_free_for_the_rest' => 'Total Free For The Rest'
        ];
    }

}