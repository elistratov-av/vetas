<?php

namespace app\modules\v2\modules\files\models;

use app\common\components\media\MediaException;
use app\models\db\ActiveRecord;
use app\models\db\Files;
use app\models\db\subscription\SubscriptionLog;
use app\modules\v2\modules\files\components\FileManager;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;
use Yii;

class FilesModel
{
    const EVENT_FILE_ATTACHED = 'EVENT_FILE_ATTACHED';
    const EVENT_FILE_UPLOADED = 'EVENT_FILE_UPLOADED';
    const EVENT_FILE_DELETED = 'EVENT_FILE_DELETED';

    /**
     * @var FileManager $file_manager
     */
    public $file_manager;
    /**
     * @var Files $file
     */
    public $file;

    /**
     * FilesModel constructor.
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct()
    {
        $this->file_manager = new FileManager();
    }

    /**
     * @param int $id
     * @return Files|null
     * @throws NotFoundHttpException
     */
    public function get(int $id)
    {
        return $this->findFile($id);
    }

    /**
     * @param int $id
     * @param string $entity_type
     * @return Files|null
     * @throws \yii\web\NotFoundHttpException
     */
    public function findFile($id)
    {
        $file = Files::findOne($id);

        if (!$file) {
            throw new NotFoundHttpException('Файл не найден');
        }

        $this->file = $file;

        return $file;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function upload()
    {
        try {
            $uploaded_file = UploadedFile::getInstanceByName('file');

            if (!$uploaded_file) {
                throw new BadRequestHttpException('Не верный формат запроса. Необходимо поле file');
            }

            $file = $this->file_manager->upload($uploaded_file);
        } catch (MediaException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        /*
         * Событие - загрузка
         */
        Yii::$app->trigger(
            self::EVENT_FILE_UPLOADED,
            new Event([
                'sender' => $file,
            ])
        );

        return $file;
    }

    /**
     * @param int $id
     * @param int $entity_id
     * @param string $entity_type
     * @return Files
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function attach(int $id, int $entity_id, string $entity_type)
    {
        $file = $this->findFile($id);

        if (!$file->entity_id) {
            $file->entity_id = $entity_id;
        } else {
            throw new BadRequestHttpException("Файл уже привязан к ресурсу {$file->entity_type} #{$file->entity_id}");
        }

        $this->checkEntityType($entity_type);

        $file->entity_type = $entity_type;

        $this->file_manager->attach($file);

        if ($file->hasErrors()) {
            throw new ServerErrorHttpException(implode("\r\n", $file->getErrorSummary(true)));
        }

        /*
         * Событие - прикрепление файла
         */
        Yii::$app->trigger(
            self::EVENT_FILE_ATTACHED,
            new Event([
                'sender' => $file
            ])
        );

        return $file;
    }

    /**
     * @param string $entity_type
     * @throws BadRequestHttpException
     */
    public function checkEntityType(string $entity_type): void
    {
        if (!array_key_exists($entity_type, Files::$entity_types)) {
            throw new BadRequestHttpException("Нет такого entity_type {$entity_type}");
        }
    }

    /**
     * @param int $id
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function delete(int $id)
    {
        /** @var Files $file */
        $file = $this->findFile($id);

        $this->file_manager->delete($file);

        try {
            $this->file->delete();
        } catch (\Throwable $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }

        /*
         * Событие - удаление файла
         */
        Yii::$app->trigger(
            self::EVENT_FILE_DELETED,
            new Event([
                'sender' => $file
            ])
        );
    }

    /**
     * @param int $id
     * @param int $entity_id
     * @param string $entity_type
     * @return \app\models\db\ActiveRecord
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function checkAttachingModel(int $entity_id, string $entity_type)
    {
        $this->checkEntityType($entity_type);

        /** @var ActiveRecord $model */
        $className = Files::modelClassFromEntityType($entity_type);
        $model = call_user_func([$className, 'findOne'], $entity_id);

        if (!$model) {
            throw new NotFoundHttpException("Экземпляр сущности $entity_type #$entity_id не найден");
        }

        if (!($model instanceof ActiveRecord)) {
            throw new ServerErrorHttpException('Модель должна иметь тип ActiveRecord');
        }

        return $model;
    }

    /**
     * @param \app\models\db\ActiveRecord $model
     * @param array $params
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function checkAccess($model, $params = [])
    {
        list($authItems, $modelToCheck) = $this->findAuthItems($model);

        if (empty($authItems)) {
            return true;
        }

        $params['model'] = $modelToCheck;

        foreach ($authItems as $authItem) {
            $result = \Yii::$app->user->can($authItem, $params);
            if ($result === true) {
                return true;
            }
        }

        throw new ForbiddenHttpException('Недостаточно прав доступа');
    }

    /**
     * @param $file_token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getSubscriptionLogFilesByToken($file_token)
    {
        /** @var SubscriptionLog[] $logs */
        $logs = SubscriptionLog::find()->where(['attached_files_token' => $file_token])->all();

        // Находим запись к которой фактически прикреплены файлы и сбрасываем индексы
        $log = array_values(array_filter($logs, function (SubscriptionLog $log) {
            return count($log->files) > 0;
        }));
        if (count($log) === 0) {
            throw new BadRequestHttpException('Токен не валиден');
        }
        return $log[0]->files;
    }

    /**
     * @param \app\models\db\ActiveRecord $model
     * @return array
     */
    private function findAuthItems($model)
    {
        $map = require \Yii::getAlias('@modules/v2/common/rbac/files_rules_map.php');

        $className = call_user_func([$model, 'className']);

        $items = ArrayHelper::getValue($map, $className);

        if (!isset($items['parent'])) {
            return [$items, $model];
        }

        $authItems = $items['permissions'];

        if (isset($items['via'])) {
            $pivotFK = $items['via']['key'];
            $pivotModel = call_user_func([$items['via']['class'], 'findOne'], $model->$pivotFK);
            if ($pivotModel === null) {
                return  [];
            }
            $foreignKey = $pivotModel->{$items['key']};
        } else {
            $foreignKey = $model->{$items['key']};
        }

        $modelToCheck = call_user_func([$items['parent'], 'findOne'], $foreignKey);

        return [$authItems, $modelToCheck];
    }
}
