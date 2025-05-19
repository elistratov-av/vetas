<?php

namespace app\modules\v2\modules\pricelist\controllers;

use app\common\models\UserModel;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pricelist\models\ServicesPricelist;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;

class ServicesController extends BaseController
{
    public function behaviors(): array
    {
        $rules = parent::behaviors();
        $rules[] = [
            'class' => AccessControl::class,
            'only' => ['create', 'edit', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'matchCallback' => function ($rule, $action) {
                        /** @var UserModel $user */
                        $user = \Yii::$app->user->identity;

                        return $user->specialist->organization->isRoot();
                    }
                ],
            ],
        ];

        return $rules;
    }

    /**
     * @return ServicesPricelist
     * @throws BadRequestHttpException
     */
    protected function getPricelist()
    {
        /** @var UserModel $user */
        $user = \Yii::$app->user->identity;
        $pricelist = new ServicesPricelist([
            'organization' => $user->specialist->organization
        ]);
        if (!$pricelist->validate(['id_organization'])) {
            throw new BadRequestHttpException($pricelist->getFirstError('id_organization'));
        }

        return $pricelist;
    }


    /**
     * @param array $filter
     * @param int $page
     * @param int $limit
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionList(array $filter = [], int $page = 1, int $limit = 10)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $this->getPricelist()->getServices($filter, $page, $limit),
        ];
    }

    /**
     * @param int $id_service
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionHistlist(int $id_service)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $this->getPricelist()->getHistList($id_service),
        ];
    }


    /**
     * @param int $id_service
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGet(int $id_service)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return $this->getPricelist()->getService($id_service);
    }

    /**
     * @param string $name
     * @param int $id_service_type
     * @param int $id_service_measure
     * @param float $price
     * @param int $duration
     * @param int $cooldown
     * @param bool $at_clinic
     * @param bool $at_home
     * @param null|string $code
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCreate(
        string  $name,
        int     $id_service_type,
        int     $id_service_measure,
        float   $price,
        int     $duration,
        int     $cooldown,
        bool    $at_clinic,
        bool    $at_home,
        ?string $code
    )
    {
        return $this->getPricelist()->create(
            $name, $id_service_type, $id_service_measure, $price, $duration, $cooldown, $at_clinic, $at_home, $code
        );
    }

    /**
     * @param int $id_service
     * @param string $name
     * @param int $id_service_type
     * @param int $id_service_measure
     * @param float $price
     * @param int $duration
     * @param int $cooldown
     * @param bool $at_clinic
     * @param bool $at_home
     * @param null|string $code
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionEdit(
        int     $id_service,
        string  $name,
        int     $id_service_type,
        int     $id_service_measure,
        float   $price,
        int     $duration,
        int     $cooldown,
        bool    $at_clinic,
        bool    $at_home,
        ?string $code
    )
    {
        return $this->getPricelist()->edit(
            $id_service, $name, $id_service_type, $id_service_measure, $price, $duration, $cooldown, $at_clinic, $at_home, $code
        );
    }

    /**
     * @param int $id_service
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionDelete(int $id_service)
    {
        return $this->getPricelist()->delete($id_service);
    }
}
