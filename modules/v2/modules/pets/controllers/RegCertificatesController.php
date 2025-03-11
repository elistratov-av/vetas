<?php

namespace app\modules\v2\modules\pets\controllers;

use app\models\db\Pets;
use app\models\db\RegCertificatesHistory;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pets\models\RegCertificateModel;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class RegCertificatesController
 * @package app\modules\v2\modules\pets\controllers
 * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102772858
 */
class RegCertificatesController extends BaseController
{
    /**
     * Получение существующего регистрационного удостоверения
     *
     * @param int|int[] $id_pet
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws BadRequestHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102772860
     */
    public function actionGet($id_pet)
    {
        if (!is_array($id_pet)) {
            $id_pet = (array) $id_pet;
        }

        foreach ($id_pet as $id){
            if (!is_integer($id)) {
                throw new BadRequestHttpException('id_pets должен быть int или массивом int');
            }
            $pet = $this->findPet($id);
            $this->checkAccess($this->action->getUniqueId(), $pet, $this->actionParams);
        }

        return [
            'result' => RegCertificateModel::getRegCertificates($id_pet),
        ];
    }

    /**
     * Создание регистрационного удостоверения
     *
     * @param int $id_pet
     * @return array
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102772862
     */
    public function actionCreate(int $id_pet)
    {
        $pet = $this->findPet($id_pet);

        $this->checkAccess($this->action->getUniqueId(), $pet, $this->actionParams);

        $model = new RegCertificateModel(['pet' => $pet]);

        $result = $model->createCertificate();
        if ($result === false) {
            $this->errorResponse($model, 'Ошибка при создании удостоверения');
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Обновление регистрационного удостоверения
     *
     * @param int $id
     * @param int $id_pet
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102772865
     */
    public function actionUpdate(int $id, int $id_pet, string $reason = 'Не указано')
    {
        $pet = $this->findPet($id_pet);

        $this->checkAccess($this->action->getUniqueId(), $pet, $this->actionParams);

        $model = new RegCertificateModel(['pet' => $pet]);

        $result = $model->updateCertificate($id, $reason);
        if ($result === null) {
            throw new NotFoundHttpException('Удостоверение для животного с указанным id не найдено');
        } elseif ($result === false) {
            $this->errorResponse($model, 'Ошибка при обновлении удостоверения');
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Удаление регистрационного удостоверения
     *
     * @param int $id
     * @param int $id_pet
     * @param string $reason
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102772867
     */
    public function actionDelete(int $id, int $id_pet, string $reason = 'Не указано')
    {
        $pet = $this->findPet($id_pet);

        $this->checkAccess($this->action->getUniqueId(), $pet, $this->actionParams);

        $model = new RegCertificateModel(['pet' => $pet]);

        $result = $model->deleteCertificate($id, $reason);
        if ($result === false) {
            $this->errorResponse($model, 'Ошибка при удалении удостоверения');
        }

        return [
            'result' => true,
        ];
    }

    /**
     * Возвращает историю обновлений регистрационного удостоверения у животного
     * @param int $id_reg
     * @return array|\yii\db\ActiveRecord[]
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionHistory(int $id_pet, int $limit = 15, int $page = 1){
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return RegCertificatesHistory::find()
            ->where(['id_pet' => $id_pet])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->all();
    }

    /**
     * @param int $id
     * @return \app\models\db\Pets|null
     * @throws \yii\web\NotFoundHttpException
     */
    private function findPet(int $id)
    {
        $model = Pets::findOne(['id' => $id]);

        if ($model === null) {
            throw new NotFoundHttpException('Животное с указанным id не найдено');
        }

        return $model;
    }
}
