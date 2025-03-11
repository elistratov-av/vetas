<?php


namespace app\modules\v2\modules\pets\controllers;

use app\modules\v1\models\FileResource;
use app\modules\v2\modules\pets\models\IdentModel;
use app\modules\v2\modules\BaseController;
use Exception;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

/**
 * Class IdentController
 * @package app\modules\v2\modules\pets\controllers
 *
 */
class IdentController extends BaseController
{

    /**
     * Список типов идентфикации
     *
     * @return array
     */
    public function actionTypes()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new IdentModel)->getIdentTypes()
        ];
    }


    /**
     * Сохраняет значения идентификационных меток
     *
     * @param $id_pet
     * @param $idents
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionSave($id_pet, $idents)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new IdentModel)->save($id_pet, $idents);

        return [
            'result' => true,
        ];
    }

    public function actionAttachApplication($pet_id) {
        if (FileResource::find()->where(['entity_id' => $pet_id])->andWhere(['entity_type' => 'reg_application'])->count()) throw new Exception('Заявка на регистрацию уже загружена!', 400);
        $fileService = Yii::$app->fileService;
        $resource = $fileService->upload(UploadedFile::getInstanceByName('file'));
        $resource->entity_id = $pet_id;
        $resource->entity_type = 'reg_application';
        $resource->save();
        return $resource;
    }

    public function actionDeleteApplication($pet_id) {
        $resources = FileResource::find()->where(['entity_id' => $pet_id])->andWhere(['entity_type' => 'reg_application'])->all();
        if (count($resources)>0) {
            foreach ($resources as $resource) {
                $resource->delete();
            }
        }
    }
}
