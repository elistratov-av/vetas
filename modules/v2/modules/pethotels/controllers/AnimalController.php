<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\AnimalModel;
use app\modules\v2\modules\pethotels\models\RequestModel;
use app\modules\v2\modules\pets\models\PetToOwnerModel;
use yii\db\Exception;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class AnimalController extends BaseController
{

    /**
     * Возвращает список всех питомцев для заданного владельца
     * @param $id_owner
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll($id_owner)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new AnimalModel();
        return [
            'result' => $model->list($id_owner)->all(),
        ];
    }

    /**
     * Создает нового питомца
     *
     * @param $id_owner
     * @param $nickname
     * @param $type
     * @param $breed
     * @param $gender_male
     * @param $processing
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate(
        $id_owner, $nickname, $id_species,
        $id_breed, $gender_male = null, $processing = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);


        $query = new Query();
        $pet = $query
            ->select([
                'p.id', 'p.name as nickname',
                'pto.id_owner'
            ])
            ->from(Pets::tableName() . ' p')
            ->leftJoin('pets_to_owner pto', 'pto.id_pet=p.id')
            ->where(['name' => $nickname,
                'id_species' => $id_species,
                'id_owner' => $id_owner
            ])->one();

        if ($pet != null) {
            return [
                'result' => false,
                'pet' => $pet,
            ];
        }

        $model_1 = new Pets();
        $id_pet = $model_1->create($nickname, $id_species, $id_breed, $gender_male);

        $model_2 = new AnimalModel();
        $query = new Query();
        $cnt = $query
            ->from('pet_hotel_animal')
            ->where(['id_pet' => $id_pet])
            ->count();
        if ($cnt > 0) {
            $model_2->edit($id_pet, $processing);
        } else {
            $model_2->create($id_pet, $processing);
        }

        $model_3 = new PetToOwnerModel();
        \Yii::$app->db->createCommand()
            ->delete(
                PetsToOwner::tableName(),
                [
                    'id_pet' => $id_pet,
                    'id_owner' => $id_owner,
                    'id_owner_type' => 1,
                ]
            )
            ->execute();
        $model_3->create($id_pet, $id_owner, 1);

        return [
            'result' => true,
            'id' => $id_pet,
        ];
    }

    /**
     * Удаляет питомца
     *
     * @param $id
     * @return array
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new AnimalModel();
        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование питомца
     *
     * @param $id
     * @param $id_owner
     * @param $nickname
     * @param $id_species
     * @param $id_breed
     * @param $gender_male
     * @param $processing
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit(
        $id, $id_owner = null, $nickname = null,
        $id_species = null, $id_breed = null,
        $gender_male = null, $processing = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!is_null($nickname) || !is_null($id_species)
            || !is_null($id_breed) || !is_null($gender_male)) {
            $model_1 = new Pets();
            $model_1->edit(
                $id, $nickname, $id_species,
                $id_breed, $gender_male
            );
        }

        if (!is_null($processing)) {
            $model_2 = new AnimalModel();
            $query = new Query();
            $cnt = $query
                ->from('pet_hotel_animal')
                ->where(['id_pet' => $id])
                ->count();
            if ($cnt > 0) {
                $model_2->edit($id, $processing);
            } else {
                $model_2->create($id, $processing);
            }
        }

        if (!is_null($id_owner)) {
            $model_3 = new PetToOwnerModel();
            \Yii::$app->db->createCommand()
                ->delete(
                    PetsToOwner::tableName(),
                    [
                        'id_pet' => $id,
                        'id_owner' => $id_owner,
                        'id_owner_type' => 1,
                    ]
                )
                ->execute();
            $model_3->create($id, $id_owner, 1);
        }

        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанного питомца
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

        $model = new AnimalModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Поиск питомца
     *
     * @param $id_owner
     * @param $nickname
     * @param $type
     * @param $breed
     * @param $gender_male
     * @param $processing
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $id_owner = null, $nickname = null, $type = null,
        $breed = null, $gender_male = null, $processing = null,
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
        $model = new AnimalModel();
        return [
            'result' => $model->search(
                $id_owner, $nickname, $type,
                $breed, $gender_male, $processing,
                $page, $limit
            ),
        ];
    }

    /**
     * Возвращает общее количество животных в разрезе видов
     *
     * @param $id_hotel
     * @param $date_from
     * @param $date_to
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCountByType(
        $id_hotel, $date_from, $date_to
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($id_hotel)) {
            return [
                'result' => [],
            ];
        }

        $model = new RequestModel();
        $requests = $model
            ->searchQuery(
                null, null, null, null,
                $id_hotel, null, $date_from, $date_to,
            )
            ->all();
        $counts = [];
        foreach ($requests as $request) {
            $animal_type = $request['animal_type'];
            if (empty($counts[$animal_type])) {
                $counts[$animal_type] = 1;
            } else {
                $counts[$animal_type] += 1;
            }
        }
        return [
            'result' => $counts,
        ];
    }

}
