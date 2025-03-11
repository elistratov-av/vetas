<?php

namespace app\modules\v2\modules\found\controllers;

use app\models\db\found_pet\Ad;
use app\modules\foundPet\queue\JobHandler;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\found\models\SearchModel;
use yii\web\BadRequestHttpException;

/**
 * Class ModerationController
 * @package app\modules\v2\modules\found\controllers
 */
class ModerationController extends BaseController
{
    /**
     * @param int $id
     * @return array
     */
    public function actionGetAd(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new SearchModel(['id' => $id]);
        $result = $model
            ->findAd();

        if ($result === null) {
            throw new BadRequestHttpException('Объявление не найдено');
        }

        if ($result === false) {
            $this->errorResponse($model);
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * @param int $id
     * @return array
     */
    public function actionGetAdPhotos(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new SearchModel(['id' => $id]);
        $result = $model
            ->findAdPhotos();

        if ($result === null) {
            throw new BadRequestHttpException('Объявление не найдено');
        }

        if ($result === false) {
            $this->errorResponse($model);
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     * @return array
     */
    public function actionListAds($page = 1, $limit = 10, $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new SearchModel($filter);

        $result = $model->findAds($page, $limit);

        if ($result === false) {
            $this->errorResponse($model);
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Одобрение объявление возможно только если объявление находится в статусе "На модерации", в карточке доступна
     * кнопка "Одобрить". Больше ни при каких статусах одобрить объявление модератор не может.
     *
     * @param int $id
     * @return array
     */
    public function actionApprove(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = $this->findModel($id);

        if ($model->is_active === false || $model->verify_status !== null) {
            throw new BadRequestHttpException('Одобрить можно только активное объявление в статусе "На модерации"');
        }

        $model->verify_status = true;
        $model->verify_at = date('Y-m-d H:i:s');
        $model->id_specialist = \Yii::$app->user->getIdentity()->specialist->id;

        $result = $model->save(true, ['verify_status', 'verify_at', 'id_specialist', 'updated_at']);

        return [
            'result' => $result,
        ];
    }

    /**
     * Закрытие объявления модератором из карточки возможно, если доступна кнопка "Закрыть" в верхней части карточки
     * (Объявление в статусе "На модерации"). Больше ни при каких статусах закрыть объявление модератор не может.
     *
     * @param int         $id
     * @param string|null $closed_reason
     * @return array
     */
    public function actionClose(int $id, string $closed_reason = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = $this->findModel($id);

        if ($model->is_active === false || $model->verify_status !== null) {
            throw new BadRequestHttpException('Закрыть можно только активное объявление в статусе "На модерации"');
        }

        $model->is_active = false;
        $model->processed = true;
        $model->active_till = null;
        $model->verify_status = false;
        $model->verify_at = date('Y-m-d H:i:s');
        $model->closed_at = date('Y-m-d H:i:s');
        $model->closed_reason = empty($closed_reason)
            ? 'Объявление удалено по причине несоответствия правилам предоставления электронного сервиса'
            : $closed_reason;
        $model->closed_by = Ad::CLOSED_BY_MODERATOR;
        $model->id_specialist = \Yii::$app->user->getIdentity()->specialist->id;

        $result = $model->save(true, ['is_active', 'active_till', 'verify_status', 'verify_at', 'closed_at', 'closed_reason', 'closed_by', 'id_specialist', 'updated_at']);

        if ($result === true) {
            $this->getJobHandler()->adModerationCloseSuccess($model);
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * @param int $id
     * @return \app\models\db\found_pet\Ad
     * @throws \yii\web\BadRequestHttpException
     */
    private function findModel(int $id)
    {
        $model = Ad::findOne(['id' => $id]);

        if ($model === null) {
            throw new BadRequestHttpException('Объявление не найдено');
        }

        return $model;
    }

    /**
     * @return \app\modules\foundPet\queue\JobHandler
     */
    private function getJobHandler()
    {
        return new JobHandler();
    }
}
