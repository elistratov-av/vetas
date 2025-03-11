<?php

namespace app\commands;

use app\common\components\asurService\ASURService;
use app\common\components\asurService\TaskNumber;
use app\models\db\asur\Task;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Console;

class AsurController extends Controller
{
    /**
     * Очищение старых заявок (с удалением файлов)
     */
    public function actionClearOld()
    {
        // удаляем заявки, зависшие в незавершенном статусе больше суток

        $dayAgo = (new \DateTime())->modify('-1 day')->format('Y-m-d H:i:s');

        $rows = (new Query())
            ->from(Task::tableName())
            ->where(['<=', 'created_at', $dayAgo])
            ->andWhere(['in', 'status', [Task::STATUS_NEW, Task::STATUS_SENT, Task::STATUS_IN_PROCESS]])
            ->all();

        Console::output(Console::ansiFormat('Удаление незавершенных заявок старше суток...', [Console::FG_YELLOW]));
        if (!empty($rows)) {
            foreach ($rows as $row) {
                Console::output(Console::ansiFormat("Удаление задания #{$row['id']}"));
                \Yii::$app->db
                    ->createCommand()
                    ->delete(Task::tableName(), ['id' => $row['id']])
                    ->execute();
            }
        }

        // оставляем только одну последнюю заявку для каждого

        $sql = <<<SQL
select 
    id_owner, count(*)
from asur.task  
group by id_owner
having count(*) > 1
SQL;
        $rows = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            Console::output(Console::ansiFormat("Удаление старых заявок для пользователя {$row['id_owner']}", [Console::FG_YELLOW]));
            $tasks = Task::find()
                ->where(['id_owner' => $row['id_owner']])
                ->orderBy(['id' => SORT_DESC])
                ->offset(1)
                ->all();

            foreach ($tasks as $task) {
                Console::output(Console::ansiFormat("Удаление задания #{$task->id}"));
                try {
                    $task->delete();
                } catch (\Exception $e) {
                    Console::error(Console::ansiFormat("Ошибка удаления задания: {$e->getMessage()}", [Console::FG_RED]));
                }
            }
        }

        // удаляем завершенные заявки без файлов

        $ids = [];
        Console::output(Console::ansiFormat('Удаление завершенных заявок без файлов...', [Console::FG_YELLOW]));

        /* @var $storage \app\common\components\asurService\LocalDocumentStorage */
        $storage = \Yii::$app->asurStorage;

        $query = (new Query())
            ->from(Task::tableName())
            ->where(['status' => Task::STATUS_FINISHED])
            ->orderBy(['created_at' => SORT_ASC]);

        foreach ($query->each() as $row) {
            if (empty($row['file']) || !$storage->isFile($row['file'])) {
                $ids[] = $row['id'];
            }
        }

        if (!empty($ids)) {
            foreach ($ids as $id) {
                Console::output(Console::ansiFormat("Удаление задания #{$id}"));
                \Yii::$app->db
                    ->createCommand()
                    ->delete(Task::tableName(), ['id' => $id])
                    ->execute();
            }
        }
    }

    /**
     * Отправка сообщения на выписку из ЕГРИП по ИНН (код: 13018)
     * @throws \Exception
     */
    public function actionSendInnEgrip($inn = null)
    {
        $inn = $inn ?? ASURService::EGRIP_TEST_INN;
        $this->send(ASURService::TYPE_EGRIP, 'inn', $inn);
    }

    /**
     * Отправка сообщения на выписку из ЕГРИП по ОГРН (код: 13018)
     * @throws \Exception
     */
    public function actionSendOgrnEgrip($ogrn = null)
    {
        $ogrn = $ogrn ?? ASURService::EGRIP_TEST_OGRN;
        $this->send(ASURService::TYPE_EGRIP, 'ogrnip', $ogrn);
    }

    /**
     * Отправка сообщения на выписку из ЕГРЮЛ по ИНН (код: 13017)
     * @throws \Exception
     */
    public function actionSendInnEgrul($inn = null)
    {
        $inn = $egrul ?? ASURService::EGRUL_TEST_INN;
        $this->send(ASURService::TYPE_EGRUL, 'inn', $inn);
    }

    /**
     * Отправка сообщения на выписку из ЕГРЮЛ по ОГРН (код: 13017)
     * @throws \Exception
     */
    public function actionSendOgrnEgrul($ogrn = null)
    {
        $ogrn = $ogrn ?? ASURService::EGRUL_TEST_OGRN;
        $this->send(ASURService::TYPE_EGRUL, 'ogrn', $ogrn);
    }

    /**
     * Отправка сообщения на получение ЕЖД (Единый Жилищный Документ) (код: 10777)
     */
    public function actionSendEjd($room = null, $address_bti = null)
    {
        $serviceProperties = [
            'address1_line3' => $room ?? AsurService::EJD_ROOM,
            'unom' => $address_bti ?? AsurService::EJD_ADDRESS_BTI,
        ];

        $this->send(ASURService::TYPE_EJD, null, null, $serviceProperties);
    }

    /**
     * Отправка сообщения на запрос паспортного досье по СНИЛС (код: 10209)
     */
    public function actionSendIdDocument($testSnils = null)
    {
        $serviceProperties = [
            'snils' => $testSnils ?? ASURService::TEST_SNILS,
        ];
        $this->send(ASURService::TYPE_PASSPORT, null, null, $serviceProperties);
    }

    /**
     * @return ASURService
     */
    protected function getAsurService()
    {
        return \Yii::$app->asurService;
    }

    /**
     * @param $code
     * @param string|null $sendParam имя тэга в <ServiceProperties> для параметров запроса согласно документации АС УР
     * @param string|null $sendValue содержание тэга <$sendParams> для параметров запроса согласно документации АС УР
     * @param array|null $serviceProperties готовый массив ключ-значение согласно документации АС УР
     * @throws \Exception
     */
    protected function send($code, string $sendParam = null, string $sendValue = null, array $serviceProperties = null)
    {
        $asurService = $this->getAsurService();

        if (!$serviceProperties) {
            if (!$sendParam && !$sendValue) {
                throw new BadRequestException('Необходимы параметры sendParam и sendValue или готовый массив serviceProperties');
            }

            $serviceProperties = [
                $sendParam => $sendValue,
                'testmsg' => ''
            ];
        }

        $number = TaskNumber::getNumber();
        $messageId = (string)Uuid::uuid4();
        $taskId = (string)Uuid::uuid4();
        $taskNumber = (string)(new TaskNumber($number));
        $asurService->sendMessage([
            'Task' => [
                'MessageId' => $messageId,
                'TaskId' => $taskId,
                'TaskNumber' => $taskNumber,
                'TaskDate' => (new \DateTime())->format(DATE_RFC3339_EXTENDED),
                'Responsible' => $asurService->Responsible,
                'Department' => $asurService->Department,
                'FunctionTypeCode' => $asurService->FunctionTypeCode,
            ],
            'Data' => [
                'DocumentTypeCode' => $code,
                'Parameter' => [
                    'ServiceProperties' => $serviceProperties
                ],
                'IncludeXmlView' => true,
                'IncludeBinaryView' => true
            ]
        ]);

        Console::output(Console::ansiFormat("messageid: {$messageId}"));
        Console::output(Console::ansiFormat("taskid: {$taskId}"));
        Console::output(Console::ansiFormat("tasknumber: {$taskNumber}"));
    }
}
