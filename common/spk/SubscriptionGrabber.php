<?php

namespace app\common\spk;

use app\common\components\inform\SpkService;
use app\models\db\spk\SpkSubscription;
use app\models\db\spk\SpkUpdateSubscriptionTask;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use yii\db\Expression;

/**
 * Получение списка подписанных на наш сервис из ИС ПК
 * Подписка через нашу систему реализована преимущественно в app\common\components\inform
 * Однако, подписываться могут и черех другие системы/приложения
 * Поэтому приходиться забирать список подписок напрямую из ИС ПК
 *
 * @package app\common\spk
 */
class SubscriptionGrabber
{
    /**
     * Защита от зацикливаия в результате глюка сервиса или ошибки в этом коде
     */
    const MAX_REQUESTS = 10000;
    /**
     * Считаем запросы для защиты от зацикливаия в результате глюка сервиса или ошибки в этом коде
     * @var int
     */
    protected $request_counter = 0;

    /**
     * @var SpkUpdateSubscriptionTask
     */
    protected $current_task;

    /**
     * @var int Текущий offset
     */
    protected $current_offset = 0;

    /**
     * @var int Лимит кол-ва подписок в запросе
     */
    protected $limit;

    /**
     * @return SpkService
     */
    protected function getSpk()
    {
        return \Yii::$app->spkService;
    }

    public function updateAll($clear_outdated_data = false, int $limit_in_each_request = 1000)
    {
        $this->limit = $limit_in_each_request;
        /*
         * Создаем задание
         */
        $this->taskCreate();


        /*
         * До тех пор пока есть данные ИЛИ не привысили кол-вл запросов
         * получаем данные из ИС ПК
         */
        try {
            do {
                $this->taskSaveProgress(); // Сохраняем текущий offset

                // Сохраняем
                $transaction = SpkSubscription::getDb()->beginTransaction();
                $result = $this->getNextPartDataFromSpkAndStoreInDb();
                if (!empty($transaction)) {
                    $transaction->commit();
                }

            } while ($result === true);
        } catch (\Throwable $e) {

            /*
             * Откатываем и сохраняем ошибку ...
             */
            if (!empty($transaction)) {
                $transaction->rollBack();
            }
            $this->taskSaveStatusError($e->getMessage());

            return false;
        }

        /*
         * Обновление завершено
         */
        $this->taskSaveStatusDone();

        /*
         * Удаляем старье, если надо
         */
        if ($clear_outdated_data) {
            $this->clearOutdatedData();
        }

        return true;
    }

    /**
     * Удаляет иные задания и данные по ним
     * @throws \yii\db\Exception
     */
    protected function clearOutdatedData()
    {
        $transaction = SpkSubscription::getDb()->beginTransaction();

        $current_task_id = $this->getTaskId();
        SpkUpdateSubscriptionTask::updateAll([
            'status' => SpkUpdateSubscriptionTask::STATUS_OUTDATED,
        ],[
            'NOT IN', 'id', $current_task_id
        ]);
        SpkSubscription::deleteAll([
            'NOT IN', 'id_task', $current_task_id
        ]);

        if (!empty($transaction)) {
            $transaction->commit();
        }
    }

    /**
     * Запрашиваем и сохраняем в БД подписки
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \app\common\components\inform\InformException
     */
    protected function getNextPartDataFromSpkAndStoreInDb()
    {
        /*
         * Запрашиваем данные
         * Плюсуем кол-во запросов всего
         * Проверяем, что не привысили кол-во запросов
         */
        $data = $this->getSpk()->listAllSubscribers($this->current_offset, $this->limit);
        $this->request_counter++;

        if ($this->request_counter >= self::MAX_REQUESTS) {
            throw new Exception('Превышено кол-во запросов: ' . $this->request_counter);
        }

        /*
         * Данные закончились
         */
        if (empty($data)) {
            return false;
        }

        /*
         * Сохраняем у себя результаты
         */
        $this->_storeSubscriptions($data);

        /*
         * Нам пришло меньше запрошенного, те данные закончились
         */
        if (count($data) < $this->limit) {
            return false;
        }

        /*
         * Добавляем к offset для следующего шага
         */
        $this->current_offset = $this->current_offset + $this->limit;

        return true;
    }

    /**
     * Сохраняем подписки в БД
     *
     * @param $data
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function _storeSubscriptions($data)
    {
        $current_task_id = $this->getTaskId();

        foreach ($data as $sub) {
            $date_created = empty($sub->created) ? null : date('Y-m-d H:i:s', $sub->created);
            $date_expiration = empty($sub->expiration) ? null : date('Y-m-d H:i:s', $sub->expiration);

            $item = new SpkSubscription([
                'id_task' => $current_task_id,
                'ext_id' => $sub->id,
                'stream' => $sub->stream,
                'email' => $sub->email,
                'msisdn' => $sub->msisdn,
                'service' => $sub->service,
                'options' => $sub->options,
                'expiration' => $date_expiration,
                'created' => $date_created,
                'day_time' => $sub->day_time,
            ]);

            if (!$item->save()) {
                $errors = $item->getErrorSummary(true);
                throw new Exception(empty($errors) ? 'Ошибка при сохранении подписки' : implode("\n", array_values($errors)));
            }
        }

        return true;
    }

    /**
     * Возвращает AR текущего задания
     *
     * @return SpkUpdateSubscriptionTask
     * @throws InvalidConfigException
     */
    protected function getTask()
    {
        if (empty($this->current_task)) {
            throw new InvalidConfigException('Нет задания на обновление!');
        }

        return $this->current_task;
    }

    /**
     * Возращает ID текущего задания
     *
     * @throws InvalidConfigException
     * @return int
     */
    protected function getTaskId()
    {
        $current_task_id = $this->getTask()->id;
        if (empty($current_task_id)) {
            throw new InvalidConfigException('Не удалось получить id текущего задания!');
        }
        return $current_task_id;
    }


    /**
     * Создаем и сохраняет в БД новое задание
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function taskCreate()
    {
        $this->current_task = new SpkUpdateSubscriptionTask([
            'status' => SpkUpdateSubscriptionTask::STATUS_PROGRESS,
            'current_offset' => 0,
        ]);
        $this->_taskSave();
    }

    /**
     * Сохраняет текущий проресс задания в БД
     *
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function taskSaveProgress()
    {
        $this->getTask()->current_offset = $this->current_offset;
        return $this->_taskSave(false, ['current_offset']);
    }

    /**
     * Сохраняем ошибку
     *
     * @param $msg
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function taskSaveStatusError($msg)
    {
        $msg = empty($msg) ? 'Произошла неизвестная ошибка' : $msg;

        $this->getTask()->error_msg = $msg;
        $this->getTask()->date_end = new Expression('NOW()');
        $this->getTask()->status = SpkUpdateSubscriptionTask::STATUS_FAIL;

        $this->_taskSave(true, ['status', 'date_end', 'error_msg']);
    }

    /**
     * Проставляем заданию статус завершено и сохраняем
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function taskSaveStatusDone()
    {
        $this->getTask()->status = SpkUpdateSubscriptionTask::STATUS_DONE;
        $this->getTask()->date_end = new Expression('NOW()');

        return $this->_taskSave(true, ['status', 'date_end']);
    }

    /**
     * Сохраняет задание
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function _taskSave($runValidation = true, $attributeNames = null)
    {
        if (!$this->getTask()->save($runValidation, $attributeNames)) {
            $errors = $this->getTask()->getErrorSummary(true);
            throw new Exception(empty($errors) ? 'Ошибка при сохранении задания' : implode("\n", array_values($errors)));
        }
        return true;
    }
}