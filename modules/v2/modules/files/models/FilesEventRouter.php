<?php


namespace app\modules\v2\modules\files\models;

use app\models\db\Files;
use app\modules\v2\modules\gosvetnadzor\models\ViolationHistoryModel;
use yii\base\Event;
use yii\base\InvalidConfigException;

class FilesEventRouter
{

    /**
     * Роутинг события добавления файла
     *
     * @param Event $event
     * @return bool
     * @throws InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function eventFileAttached($event)
    {
        /** @var Files $sender */
        $sender = $event->sender;

        if (empty($sender) || !is_a($sender, Files::class)){
            throw new InvalidConfigException('При обработке события добавления файла произошла системная ошибка');
        }

        switch($sender->entity_type){
            case 'violation':
                ViolationHistoryModel::addRecordAboutFileAttach($sender->entity_id, $sender->name);
                $event->handled = true;
                break;

        }

       return true;
    }

    /**
     * Роутинг события удаления файла
     *
     * @param $event
     * @return bool
     * @throws InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function eventFileDeleted($event)
    {
        /** @var Files $sender */
        $sender = $event->sender;

        if (empty($sender) || !is_a($sender, Files::class)){
            throw new InvalidConfigException('При обработке события удаления файла произошла системная ошибка');
        }

        switch($sender->entity_type){
            case 'violation':
                ViolationHistoryModel::addRecordAboutFileDelete($sender->entity_id, $sender->name);
                $event->handled = true;
                break;

        }

        return true;
    }
}
