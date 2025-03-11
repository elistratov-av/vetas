<?php

namespace app\modules\v2\modules\services\controllers;

use app\common\models\UserModel;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\services\models\Pricelist;
use yii\web\BadRequestHttpException;

class ServicesController extends BaseController
{
    /**
     * @return Pricelist
     * @throws BadRequestHttpException
     */
    protected function getPricelist()
    {
        /** @var UserModel $user */
        $user = \Yii::$app->user->identity;
        $pricelist = new Pricelist([
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
     * @return \app\modules\v2\common\skeletons\CommonList
     * @throws BadRequestHttpException
     */
    public function actionList(array $filter = [], int $page = 1, int $limit = 10)
    {
        return $this->getPricelist()->getServices($filter, $page, $limit);
    }

    /**
     * @param array $filter
     * @return \app\modules\v2\common\skeletons\CommonList
     * @throws BadRequestHttpException
     */
    public function actionTypes(array $filter = [])
    {
        return $this->getPricelist()->getServiceTypes($filter);
    }
}
