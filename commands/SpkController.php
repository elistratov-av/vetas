<?php


namespace app\commands;

use app\common\spk\SubscriptionGrabber;
use yii\console\Controller;

/**
 * Сбор данных из ИС ПК
 * @package app\commands
 */
class SpkController extends Controller
{

    /**
     * Обновляем в БД данные по подпискам из сервиса ИС ПК
     * @param bool $clear_out_dated Почистить старую информацию, если успешно завершилось обновление
     * @param int $limit Кол-во подписок в каждом запросе (входной параметр API ИС ПК)
     */
    public function actionGrabAllSubscribers(bool $clear_out_dated = true, int $limit = 1000)
    {
        $test = new SubscriptionGrabber();
        $test->updateAll($clear_out_dated, $limit);
    }


}