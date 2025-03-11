<?php

namespace app\modules\v2\modules\newsletter\controllers;

use app\modules\v2\modules\BaseController;
use yii\web\BadRequestHttpException;
use app\common\components\rbac\Role;
use app\modules\v2\modules\newsletter\models\NewsletterReceptionModel;

class ReceptionController extends BaseController
{
    /**
     * Редактирование шаблона Напоминание о предстоящем приеме
     *
     * @param $data
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionEdit($data): array
    {
        $organization = \Yii::$app->user->getIdentity()->getOrganization();

        if (!empty($organization) && (\Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN))) {
            $reception = new NewsletterReceptionModel();

            return [
                'result' => $reception->edit($organization, $data)
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав.');
    }

    /**
     * Получить шаблон
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function actionGet(): array
    {
        $organization = \Yii::$app->user->getIdentity()->getOrganization();

        if (!empty($organization) && (\Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN))) {
            $reception = new NewsletterReceptionModel();

            return [
                'result' => $reception->get($organization)
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав.');
    }

    public function actionDelete(): array
    {
        $organization = \Yii::$app->user->getIdentity()->getOrganization();

        if (!empty($organization) && (\Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN))) {
            $reception = new NewsletterReceptionModel();

            return [
                'result' => $reception->delete($organization)
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав.');
    }

    public function actionChangeStatus($status): array
    {
        $organization = \Yii::$app->user->getIdentity()->getOrganization();

        if (!empty($organization) && (\Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
                || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN))) {
            $reception = new NewsletterReceptionModel();

            return [
                'result' => $reception->changeStatus($organization, $status)
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав.');
    }
}
