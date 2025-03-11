<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 08.10.20
 * Time: 14:24
 */

namespace app\modules\v2\modules\drugs\controllers;


use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\drugs\models\ActiveSubstancesModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class ActiveSubstancesController extends BaseController
{
    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGet(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ActiveSubstancesModel())->getActiveSub($id)
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $activeSubs = new ActiveSubstancesModel();

        return [
            'result' => $activeSubs->list($page, $limit, $filter)
        ];
    }

    /**
     * @param string $name
     * @param $name_en
     * @param null $description
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate(
        $name,
        $name_en,
        $description = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ActiveSubstancesModel();

        $activeSub = $model
            ->create(
                $name,
                $name_en,
                $description
            );

        return [
            'result' => $activeSub
        ];
    }

    /**
     * @param $id
     * @param string $name
     * @param $name_en
     * @param null $description
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit(
        $id,
        $name,
        $name_en,
        $description = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ActiveSubstancesModel())
            ->edit(
                $id,
                $name,
                $name_en,
                $description
            );

        return [
            'result' => true,
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ActiveSubstancesModel())
            ->delete($id);

        return [
            'result' => true,
        ];
    }


}