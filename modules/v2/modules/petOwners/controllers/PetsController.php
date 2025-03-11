<?php

namespace app\modules\v2\modules\petOwners\controllers;

use app\common\validators\FullTrimValidator;
use app\models\db\PetOwners;
use app\models\db\Pets;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;

/**
 * Class PetsController
 * @package app\modules\v2\modules\petOwners\controllers
 */
class PetsController extends BaseController
{
    /**
     * Список животных у владельца
     *
     * @param int   $id_owner
     * @param array $filter
     * @param array $orderBy
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769543
     */
    public function actionList($id_owner, $filter = null, $orderBy = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $filter = $this->validateFilter($filter);
        $this->validateOrderBy($orderBy);

        $owner = $this->findPetOwner($id_owner);

        $query = Pets::find()
            ->select([
                'pets.id',
                'pets.name',
                'pets.id_reg_expire_reason',
                'pets.sex',
                'pets.id_species',
                'pets.id_breed',
                'pets.reg_expire_date',
                'pets.birthday',
                'pets.is_main',
                'pets.id_main_pet',
                'pets.duble_validation',
                'pets.id_relocate',
                'pets_to_owner.id_owner_type',
                'pets.id_brood'
            ])
            ->with('species')
            ->with('breeds')
            ->with('reg_expire_reason')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('brood')
            ->leftJoin('pets_to_owner', 'pets.id=pets_to_owner.id_pet')
            ->andWhere(['pets_to_owner.id_owner' => $id_owner]);

        if ($owner->is_main === true) {
            $query->andWhere([
                'or',
                ['pets.is_main' => true],
                ['pets.is_main' => null]
            ]);
        } elseif ($owner->is_main === false) {
            $query->andWhere([
                'or',
                ['pets.is_main' => false],
                ['pets.is_main' => null]
            ]);
        }


        if (!empty($filter['id_species'])) {
            $query->andWhere(['pets.id_species' => $filter['id_species']]);
        }

        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'pets.name', $filter['name'] . '%', false]);
        }

        if (isset($filter['reg_expire'])) {
            if ($filter['reg_expire'] === true) {
                $query->andWhere(['NOT', ['pets.id_reg_expire_reason' => null]]);
            } elseif ($filter['reg_expire'] === false) {
                $query->andWhere(['pets.id_reg_expire_reason' => null]);
            }
        }

        if ($orderBy['reg_expire_date'] === 'DESC') {
            $query->orderBy([new Expression("pets.reg_expire_date DESC NULLS LAST")]);
        } elseif ($orderBy['reg_expire_date'] === 'ASC') {
            $query->orderBy([new Expression("pets.reg_expire_date ASC NULLS FIRST")]);
        } else {
            // Оставляем дефолтное поведение, чтобы не затрагивать функционал не относящийся к задаче
            $query->orderBy(['reg_expire_date' => SORT_ASC]);
        }

        return [
            'result' => $query->asArray()->all(),
        ];
    }

    /**
     * Список животных снятых с учета у владельца
     *
     * @param int   $id_owner
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769543
     */
    public function actionListRetire($id_owner, $filter = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $filter = $this->validateFilter($filter);

        $owner = $this->findPetOwner($id_owner);

        $query = Pets::find()
            ->select([
                'pets.id',
                'pets.name',
                'pets.id_reg_expire_reason',
                'pets.sex',
                'pets.id_species',
                'pets.id_breed',
                'pets.reg_expire_date',
                'pets.birthday',
                'pets.is_main',
                'pets.id_main_pet',
                'pets.duble_validation',
                'pets.id_relocate',
                'pets_to_owner.id_owner_type',
                'pets.id_brood'
            ])
            ->with('species')
            ->with('breeds')
            ->with('reg_expire_reason')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('brood')
            ->leftJoin('pets_to_owner', 'pets.id=pets_to_owner.id_pet')
            ->andWhere(['pets_to_owner.id_owner' => $id_owner]);

        if ($owner->is_main === true) {
            $query->andWhere([
                'or',
                ['pets.is_main' => true],
                ['pets.is_main' => null]
            ]);
        } elseif ($owner->is_main === false) {
            $query->andWhere([
                'or',
                ['pets.is_main' => false],
                ['pets.is_main' => null]
            ]);
        }


        if (!empty($filter['id_species'])) {
            $query->andWhere(['pets.id_species' => $filter['id_species']]);
        }

        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'pets.name', $filter['name'] . '%', false]);
        }

        return [
            'result' => $query->andWhere(['NOT', ['pets.id_reg_expire_reason' => null]])->orderBy(['reg_expire_date' => SORT_ASC])->asArray()->all(),
        ];
    }

    /**
     * Список животных не снятых с учета у владельца
     *
     * @param int   $id_owner
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769543
     */
    public function actionListNotRetire($id_owner, $filter = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $filter = $this->validateFilter($filter);

        $owner = $this->findPetOwner($id_owner);

        $query = Pets::find()
            ->select([
                'pets.id',
                'pets.name',
                'pets.id_reg_expire_reason',
                'pets.sex',
                'pets.id_species',
                'pets.id_breed',
                'pets.reg_expire_date',
                'pets.birthday',
                'pets.is_main',
                'pets.id_main_pet',
                'pets.duble_validation',
                'pets.id_relocate',
                'pets_to_owner.id_owner_type',
                'pets.id_brood'
            ])
            ->with('species')
            ->with('breeds')
            ->with('reg_expire_reason')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('brood')
            ->leftJoin('pets_to_owner', 'pets.id=pets_to_owner.id_pet')
            ->andWhere(['pets_to_owner.id_owner' => $id_owner]);

        if ($owner->is_main === true) {
            $query->andWhere([
                'or',
                ['pets.is_main' => true],
                ['pets.is_main' => null]
            ]);
        } elseif ($owner->is_main === false) {
            $query->andWhere([
                'or',
                ['pets.is_main' => false],
                ['pets.is_main' => null]
            ]);
        }


        if (!empty($filter['id_species'])) {
            $query->andWhere(['pets.id_species' => $filter['id_species']]);
        }

        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'pets.name', $filter['name'] . '%', false]);
        }

        return [
            'result' => $query->andWhere(['pets.id_reg_expire_reason' => null])->asArray()->all(),
        ];
    }

    /**
     * Проверяет входной параметр filter на соответствие типов
     *
     * @param array $filter
     * @throws BadRequestHttpException
     */
    protected function validateFilter($filter)
    {
        if (!empty($filter['id_species']) && !is_numeric($filter['id_species'])) {
            throw new BadRequestHttpException('Фильтр id_species должен быть числом');
        }

        if (!empty($filter['name']) && !is_string($filter['name'])) {
            throw new BadRequestHttpException('Фильтр name должен быть строкой');
        }

        if (!empty($filter['reg_expire']) && !is_bool($filter['reg_expire'])) {
            throw new BadRequestHttpException('Фильтр reg_expire должен быть булевым значением');
        }

        if (!empty($filter['name']) && is_string($filter['name'])) {
            $fullTrim = new FullTrimValidator();
            $filter['name'] = $fullTrim->validateValue($filter['name']);
        }

        return $filter;
    }

    protected function validateOrderBy($orderBy): void
    {
        if (!$orderBy) {
            return;
        }
        foreach($orderBy as $fieldName => $direction) {
            if (!in_array($direction, ['ASC', 'DESC'])) {
                throw new BadRequestHttpException('Передан не корректный параметр напарвления orderBy');
            }
            if (!in_array($fieldName, ['reg_expire_date'])) {
                throw new BadRequestHttpException('Не корректное имя поля для orderBy');
            }
        }
    }

    /**
     * @param int $id_owner
     * @return \app\models\db\PetOwners|null
     */
    private function findPetOwner($id_owner)
    {
        $owner = PetOwners::findOne(['id' => $id_owner]);

        if ($owner === null) {
            throw new BadRequestHttpException('Владелец не найден');
        }

        return $owner;
    }
}
