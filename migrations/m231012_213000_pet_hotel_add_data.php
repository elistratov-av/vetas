<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m231012_213000_pet_hotel_add_data
 */
class m231012_213000_pet_hotel_add_data extends Migration
{
    public static $pet_hotels = [
        458 => 'Зоогостиница организации «ГБУ «Мосветстанция»»',
        523 => 'Зоогостиница организации «Донская ветеринарная лечебница»',
    ];

    public function exists($table_name, $filter)
    {
        $query = new Query();
        $rows = $query
            ->select(['id'])
            ->from($table_name)
            ->where($filter)
            ->all();
        return !empty($rows);
    }

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        foreach (self::$pet_hotels as $org_id => $pet_hotel_name) {
            if ($this->exists("organizations", ["id" => $org_id])) {
                if (!$this->exists("pet_hotel", ["name" => $pet_hotel_name])) {
                    $command = Yii::$app->db->createCommand();
                    $command
                        ->insert('public.pet_hotel', [
                            'name' => $pet_hotel_name,
                            'id_organization' => $org_id,
                        ])
                        ->execute();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $command = Yii::$app->db->createCommand();
        foreach (self::$pet_hotels as $org_id => $pet_hotel_name) {
            $command
                ->delete('public.pet_hotel', [
                    'name' => $pet_hotel_name,
                    'id_organization' => $org_id,
                ])
                ->execute();
        }
        return true;
    }
}
