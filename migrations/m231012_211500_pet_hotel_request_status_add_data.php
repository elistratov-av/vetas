<?php

use app\commands\migrate\Migration;

/**
 * Class m231012_211500_pet_hotel_request_status_add_data
 */
class m231012_211500_pet_hotel_request_status_add_data extends Migration
{
    public static $statuses = [
        'Бронирование',
        'Забронировано',
        'Активно',
        'Отменено',
        'Завершено',
    ];

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        foreach (self::$statuses as $status) {
            $query = new \yii\db\Query();
            $rows = $query
                ->select(['id'])
                ->from('public.pet_hotel_request_status')
                ->where(['code' => $status])
                ->all();
            if (empty($rows)) {
                $command = Yii::$app->db->createCommand();
                $command
                    ->insert('public.pet_hotel_request_status', [
                        'code' => $status,
                        'name' => 'Статус "' . $status . '"',
                    ])
                    ->execute();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $command = Yii::$app->db->createCommand();
        foreach (self::$statuses as $status) {
            $command
                ->delete('public.pet_hotel_request_status', [
                    'code' => $status,
                ])
                ->execute();
        }
        return true;
    }
}
