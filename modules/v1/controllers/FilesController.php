<?php

namespace app\modules\v1\controllers;

use app\common\components\FileService;
use app\common\controllers\ApiController;
use app\modules\v1\models\FileResource;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class FilesController extends ApiController
{
    /**
     * @return FileResource
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        $id_visit = $_POST['id_visit'] ?? null;
        $id_pet = $_POST['id_pet'] ?? null;
        $id_user = $_POST['id_user'] ?? null;
        $user_fullname = $_POST['user_fullname'] ?? null;
        try {
            /** @var FileService $fileService */
            $fileService = \Yii::$app->fileService;

            $resource = $fileService->upload(UploadedFile::getInstanceByName('file'),  $id_visit, $id_pet, $id_user, $user_fullname);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return $resource;
    }

    /**
     * @param $id
     * @return null|FileResource
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        if (!empty($id)) {
            $resource = FileResource::findOne($id);

            if (!empty($resource)) {
                return $resource;
            }
        }

        throw new NotFoundHttpException();
    }
}