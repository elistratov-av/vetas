<?php

namespace app\modules\v2\modules\files\controllers;

use app\models\db\ActiveRecord;
use app\models\db\Files;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\files\models\FilesModel;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class FileController extends BaseController
{
    /**
     * @param int $id
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionGet(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new FilesModel())->get($id),
        ];
    }

    /**
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpload()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new FilesModel())->upload(),
        ];
    }

    /**
     * @param int $id
     * @param int $entity_id
     * @param string $entity_type
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionAttach(int $id, int $entity_id, string $entity_type)
    {
        $fileModel = new FilesModel();

        /** @var ActiveRecord $model */
        $model = $fileModel->checkAttachingModel($entity_id, $entity_type);

        if ($model->isReadOnly()) {
            throw new ForbiddenHttpException('Нельзя привязать файл к архивной сущности');
        }

        $fileModel->checkAccess($model);

        return [
            'result' => $fileModel->attach($id, $entity_id, $entity_type),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionDelete(int $id)
    {
        $fileModel = new FilesModel();

        $file = $fileModel->get($id);

        /** @var Files $model */
        $model = $file->entity;

        if ($model->isReadOnly()) {
            throw new ForbiddenHttpException('Нельзя удалить файл у архивной сущности');
        }

        $fileModel->checkAccess($model);
        $fileModel->delete($id);

        return [
            'result' => true,
        ];
    }

    public function actionGetFile()
    {
        $name = $_GET['name'];
        if (!empty($name)) {
            $resource = FileResource::find()->where(['name' => $name])->one();
            return $resource->path;
//            if (!empty($resource)) {
//                // пока вернем путь к файлу
//                $filePath = "..../.." . $resource->path;
//                // Проверяем существование файла
//                if (file_exists($filePath)) {
//                    return $resource->path;
//                } else {
//                    throw new NotFoundHttpException();
//                }
//            }
//            throw new NotFoundHttpException();
        }
        // Если ни один из вышеуказанных случаев не выполнился, возвращаем 404 ошибку
        throw new NotFoundHttpException();
    }
}
