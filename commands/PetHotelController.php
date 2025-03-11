<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.09.19
 * Time: 17:18
 */

namespace app\commands;

use app\models\db\PetHotelRequest;
use app\models\db\PetHotelRequestStatus;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class PetHotelController extends Controller
{
    // Отменить просроченые брони заявок в зоогостиницу
    public function actionCancelOverdueBookedRequests() {
        $age = date('Y-m-d H:i:s' ,(strtotime(date('Y-m-d 00:00:00')) - 1));

        $bookedStatuses = PetHotelRequestStatus::find()
            ->where(['in', 'code', ['Забронировано', 'Бронирование']])
            ->all();

        if (empty($bookedStatuses)) {
            $this->error("Ошибка, не найдены статусы бронирования");
            return false;
        }

        $cancelStatus = PetHotelRequestStatus::find()
            ->where(['code' => 'Отменено'])
            ->one();

        if (empty($cancelStatus)) {
            $this->error("Ошибка, не найден статус отмены");
            return false;
        }

        $query = PetHotelRequest::find()
            ->alias('ph')
            ->select(['ph.id'])
            ->where([
                'and',
                ['in','ph.id_status', ArrayHelper::map($bookedStatuses, 'code', 'id')],
                ['and', ['<', 'ph.date_from', $age]],
            ]);

        $requests = $query->asArray()->all();

        $updated['requests'] = 0;

        try {
            \Yii::$app->db->transaction(function () use ($requests, $cancelStatus, &$updated) {
                $requestIds = array_map(function ($re) { return $re['id']; }, $requests);

                $updated['requests'] = PetHotelRequest::updateAll([
                    'id_status' => $cancelStatus->id,
                ], ['IN', 'id', $requestIds]);
            });
        } catch (\Throwable $e) {
            $this->error("Ошибка при сохранении: {$e}");
            return false;
        }

        Console::output(join(PHP_EOL, [
            "Заявки: {$updated['requests']}",
            "Забронированые заявки завершены",
        ]));

        return true;
    }
}