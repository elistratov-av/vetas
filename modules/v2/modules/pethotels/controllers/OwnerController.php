<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\models\db\PetOwners;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\OwnerModel;
use yii\db\Exception;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class OwnerController extends BaseController
{

    /**
     * Создает нового владельца
     *
     * @param $i_fio
     * @param $o_fio
     * @param $f_fio
     * @param $phone_number
     * @param $address
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws Exception
     */
    public function actionCreate(
        $i_fio = null, $o_fio = null,
        $f_fio = null, $phone_number = null, $address = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $owner = PetOwners::findOne(['i_fio' => $i_fio,
            'o_fio' => $o_fio,
            'f_fio' => $f_fio,
            'id_fias_address' => $address,
            ]);

        if ($owner != null) {
            return [
                'result' => false,
                'owner' => $owner,
            ];
        }

        $model_1 = new PetOwners();
        $owner_id = $model_1->create($i_fio, $o_fio, $f_fio, $address);

        $model_2 = new OwnerModel();
        $query = new Query();
        $cnt = $query
            ->from('pet_hotel_animal_owner')
            ->where(['id_owner' => $owner_id])
            ->count();
        if ($cnt > 0) {
            $model_2->edit($owner_id, $phone_number, $address);
        } else {
            $model_2->create($owner_id, $phone_number, $address);
        }
        return [
            'result' => true,
            'id' => $owner_id,
        ];
    }

    /**
     * Редактирование владельца
     *
     * @param $id
     * @param $i_fio
     * @param $o_fio
     * @param $f_fio
     * @param $phone_number
     * @param $address
     * @return array
     * @throws ForbiddenHttpException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionEdit(
        $id, $i_fio = null, $o_fio = null,
        $f_fio = null, $phone_number = null, $address = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!is_null($i_fio) || !is_null($o_fio) || !is_null($f_fio)) {
            $model_1 = new PetOwners();
            $model_1->edit($id, $i_fio, $o_fio, $f_fio, $address);
        }

        if (!is_null($phone_number) || !is_null($address)) {
            $model_2 = new OwnerModel();
            $query = new Query();
            $cnt = $query
                ->from('pet_hotel_animal_owner')
                ->where(['id_owner' => $id])
                ->count();
            if ($cnt > 0) {
                $model_2->edit($id, $phone_number, $address);
            } else {
                $model_2->create($id, $phone_number, $address);
            }
        }

        return [
            'result' => true
        ];
    }

    /**
     * Удаляет владельца
     *
     * @param $id
     * @return array
     * @throws ForbiddenHttpException
     * @throws Exception
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new OwnerModel();
        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанного владельца
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new OwnerModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Поиск владельца
     *
     * @param $name
     * @param $i_fio
     * @param $o_fio
     * @param $f_fio
     * @param $phone_number
     * @param $address
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $i_fio = null, $o_fio = null, $f_fio = null,
        $fio = null, $phone_number = null, $address = null,
        $page = null, $limit = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $model = new OwnerModel();
        return [
            'result' => $model->search(
                $i_fio, $o_fio, $f_fio,
                $fio, $phone_number, $address,
                $page, $limit
            ),
        ];
    }
}
