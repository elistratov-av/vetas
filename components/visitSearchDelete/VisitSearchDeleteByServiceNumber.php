<?php

namespace app\common\components\visitSearchDelete;

use app\models\db\etp\ETPMessage;
use app\models\db\GovServices;
use app\models\db\Visits;
use app\models\db\VisitsGovServices;
use app\modules\admin\helpers\VisitStatusHelper;
use yii\console\Exception;
use yii\helpers\Console;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.09.19
 * Time: 17:28
 */
class VisitSearchDeleteByServiceNumber extends \yii\base\Model
{
    public $service_number;

    /**
     * Соединение с БД
     * @return \yii\db\Connection
     */
    public function getStatDb()
    {
        return \Yii::$app->db;
    }

    /**
     * @return \yii\db\Connection
     */
    public function getMasterDb()
    {
        return \Yii::$app->db;
    }


    /**
     * @param $service_number
     * @throws Exception
     */
    public function search($service_number)
    {
        $message = $this->loadEtpMessage($service_number);
        $visit = Visits::findOne(['id' => $message->visit_id]);

        if ($message && !$visit) {
            throw new Exception("По сервисному номеру {$service_number} визит уже удален.");
        }

            $services = VisitsGovServices::find()
                ->select(['id_service'])
                ->where(['id_visit' => $message->visit_id])
                ->asArray()
                ->all();

            $serviceNames = [];
            foreach ($services as $service) {
                $serviceName = GovServices::findOne(['id' => $service]);
                $serviceNames[] .= $serviceName->name;
            }
            $serviceNamesAsStr = implode(', ', $serviceNames);
            $petSex = $message->visit->pet->sex == 'F' ? 'Ж' : 'М';
            $status = VisitStatusHelper::statusName($message->visit->status);
            $isPaid = $message->visit->is_paid == 't' ? 'Да' : 'Нет';


            Console::output(Console::ansiFormat("По сервисному номеру {$message->service_number} найден визит № {$message->visit_id}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Номер талона: {$message->visit->ticket_number}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Время: {$message->visit->time_range}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Владелец: {$message->visit->owner->fullname}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Животное: {$message->visit->pet->name} ({$petSex}, {$message->visit->pet->species->name})", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Специалист: {$message->visit->specialists->user->fullname}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Организация: {$message->visit->organization->short_name}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Услуги: {$serviceNamesAsStr}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Статус: {$status}", [
                Console::FG_GREEN, Console::BOLD
            ]));
            Console::output(Console::ansiFormat("Оплачен: {$isPaid}", [
                Console::FG_GREEN, Console::BOLD
            ]));

    }

    public function checkAndExecuteSubTransaction($sql, $visit_id)
    {
        $masterDb = $this->getMasterDb();

        try {
            $masterDb->createCommand($sql, [
                'id_visit' => $visit_id,
            ])->execute();
        } catch (\Exception $e) {
            $masterDb->transaction->rollBack();
            return $e;
        }
    }

    /**
     * @param $service_number
     * @throws \Throwable
     */
    public function delete($service_number)
    {
        $masterDb = $this->getMasterDb();
        $masterDb->beginTransaction();

        $message = $this->loadEtpMessage($service_number);

        $sql = <<<SQL
DELETE FROM visits_gov_services vg
WHERE vg.id_visit = :id_visit;
SQL;
        $this->checkAndExecuteSubTransaction($sql, $message->visit_id);

        $sql = <<<SQL
DELETE FROM visits_specialists vs
WHERE vs.id_visit = :id_visit;
SQL;
        $this->checkAndExecuteSubTransaction($sql, $message->visit_id);

        $sql = <<<SQL
DELETE FROM visits v
WHERE v.id = :id_visit;
SQL;
        $this->checkAndExecuteSubTransaction($sql, $message->visit_id);
        $masterDb->transaction->commit();
    }

    /**
     * @param $service_number
     * @return ETPMessage
     * @throws Exception
     */
    protected function loadEtpMessage($service_number)
    {
        $message = ETPMessage::findOne(['service_number' => $service_number]);
        if (!$message) {
            throw new Exception("По сервисному номеру {$service_number} визит не найден.");
        }

        return $message;
    }
}
