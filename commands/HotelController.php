<?php


namespace app\commands;


use app\models\db\PetHotelRequest;
use app\models\db\PetHotelRequestStatus;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Работа с Зоогостиницей
 */
class HotelController extends Controller
{
    /**
     * Завершение бронирований
     */
    public function actionUpdateHotelRequest() {
        $statusActive = PetHotelRequestStatus::findOne(['code' => 'Активно']);

        if ($statusActive == null) {
            Console::output(Console::ansiFormat('Error. Cant find status "Активно"', [Console::FG_RED]));
            return;
        }

        $statusFinish = PetHotelRequestStatus::findOne(['code' => 'Завершено']);

        if ($statusFinish == null) {
            Console::output(Console::ansiFormat('Error. Cant find status "Завершено"', [Console::FG_RED]));
            return;
        }

        $requests = PetHotelRequest::find()
            ->andWhere(['id_status' => $statusActive->id])
            ->andWhere(['<=', 'date_to', (new \DateTime())->format('Y-m-d 00:00:00')])
            ->all();

        foreach ($requests as $request) {
            $request->id_status = $statusFinish->id;
            if ($request->save(false)) {
                Console::output(Console::ansiFormat("Бронь с id {$request->id} успешно обновлена. Установлен статус \"Завершено\"", [Console::FG_GREEN]));
            } else {
                Console::output(Console::ansiFormat("Ошибка обновления статуса для брони с id:{$request->id}", [Console::FG_RED]));
            }
        }
    }
}