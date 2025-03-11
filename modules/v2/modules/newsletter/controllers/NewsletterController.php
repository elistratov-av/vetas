<?php

namespace app\modules\v2\modules\newsletter\controllers;

use app\common\components\rbac\Role;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\newsletter\models\NewsletterInfoModel;
use app\modules\v2\modules\newsletter\models\NewsletterTypeModel;
use yii\web\BadRequestHttpException;

ini_set('memory_limit', '4096M');
ini_set('max_execution_time', '600');

class NewsletterController extends BaseController
{
    /**
     * Get newsletter list
     * @throws BadRequestHttpException
     */
    public function actionAll(): array
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN)
        ) {
            $newsletter = new NewsletterInfoModel();

            return [
                'result' => $newsletter->all()
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав');
    }

    /**
     * Создание рассылки
     * Обязательные поля для создания рассылки 'type', 'name', 'text',
     * Опционально 'period_from', 'period_upto', 'mailing_date', 'id_organizations', 'all_organizations'
     *
     * @param $data
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCreate($data): array
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN)
        ) {
            $newsLetter = new NewsletterInfoModel();
            $result = $newsLetter->create($data);

            return [
                'result' => $result
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав для создания рассылки');
    }

    /**
     * Редактирование рассылки
     * Обязательное передаваемое значение id
     * Возможные поля для редактирования 'name', 'text','mailing_date'
     *
     * @param $data
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionEdit($data): array
    {

        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN)
        ) {
            $editRow = new NewsletterInfoModel();
            return [
                'result' => $editRow->edit($data)
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав для редактирования рассылки');
    }

    /**
     * Удаление рассылки
     * Обязательное передаваемое значение id
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     */

    public function actionDelete($id): array
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN)
        ) {
            $deleteRow = new NewsletterInfoModel();
            return [
                'result' => $deleteRow->delete($id)
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав для удаления');
    }

    /**
     * Получение записей по фильтру
     * Обязательное передаваемое значение массив данных data
     */
    public function actionSearch(
        $status = null, $name = null,
        $mailing_from = null, array $organizations = [],
        $mailing_upto = null, $page = null, $limit = null,
        $actual_mailing_from = null, $actual_mailing_upto = null
    ): array
    {
        $model = new NewsletterInfoModel();

        return [
            'result' => $model->search($status, $name, $mailing_from, $mailing_upto, $organizations, $page, $limit, $actual_mailing_from, $actual_mailing_upto),
        ];
    }

    public function actionGetRoleUser(): array
    {
        return [
            'result' => \Yii::$app->authManager->getRolesByUser(\Yii::$app->user->getIdentity()->getId())
        ];
    }

    public function actionGet($id): array
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN)
        ) {
            $newsLetter = new NewsletterInfoModel();

            return [
                'result' => $newsLetter->get($id)
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав для просмотра записи');
    }

    public function actionGetType(): array
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_FULL)
            || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_PRIVATE_MIN)
        ) {
            $newsletter = new NewsletterTypeModel();
            return [
                'result' => $newsletter->getNewsletter()
            ];
        }

        throw new BadRequestHttpException('Недостаточно прав');
    }

}
