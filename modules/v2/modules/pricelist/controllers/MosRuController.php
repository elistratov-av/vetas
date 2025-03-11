<?php

namespace app\modules\v2\modules\pricelist\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pricelist\models\MosRuPricelist;
use yii\web\BadRequestHttpException;

class MosRuController extends BaseController
{
    /**
     * @param int $id_organization
     * @param int $id_specialist
     * @return MosRuPricelist
     * @throws BadRequestHttpException
     */
    protected function getPricelist(int $id_organization, int $id_specialist)
    {
        $pricelist = new MosRuPricelist(['id_organization' => $id_organization, 'id_specialist' => $id_specialist]);
        if (!$pricelist->validate(['id_organization'])) {
            throw new BadRequestHttpException($pricelist->getFirstError('id_organization'));
        }

        if (!$pricelist->validate(['id_specialist'])) {
            throw new BadRequestHttpException($pricelist->getFirstError('id_specialist'));
        }

        return $pricelist;
    }

    /**
     * @param int $id_organization
     * @param int $id_specialist
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet(int $id_organization, int $id_specialist)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $this->getPricelist($id_organization, $id_specialist)->getServices(),
        ];
    }

    /**
     * @param int   $id_organization
     * @param int   $id_specialist
     * @param array $mosru_services
     * @return \stdClass
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSave(int $id_organization, int $id_specialist, array $mosru_services)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return $this->getPricelist($id_organization, $id_specialist)->save($mosru_services);
    }
}
