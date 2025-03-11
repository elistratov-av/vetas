<?php

namespace app\modules\v2\modules\discount\models;

use app\models\db\Discount;
use app\models\db\Organizations;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Class DiscountModel
 * @package app\modules\v2\modules\discount\models
 */
class DiscountModel
{
    /**
     * @var int
     */
    public $id_organization;

    /**
     * DiscountModel constructor.
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function __construct()
    {
        $user = \Yii::$app->user->getIdentity();
        $this->id_organization = $user->specialist->id_organization;
        if ($this->id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }
    }

    /**
     * Возращает список всех НЕ удаленных скидок
     *
     * @return array
     * @throws \Throwable
     */
    public function all()
    {
        return Discount::find()
            ->where(['is_deleted' => false])
            ->andWhere(['is_system_discount' => false])
            ->andWhere(['in', 'id_organization', Organizations::orgTreeIds($this->id_organization)])
            ->with(['organization' => function ($query) {
                /* @var $query \yii\db\ActiveQuery */
                $query
                    ->select([
                        'id',
                        'name',
                        'short_name',
                    ]);
            }])
            ->asArray()
            ->orderBy('name ASC')
            ->all();
    }

    /**
     * Возвращает скидку по id
     *
     * @param int $id
     * @return Discount
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $discount = $this->findDiscount($id);

        if (!in_array($discount->id_organization, Organizations::orgTreeIds($this->id_organization))) {
            throw new BadRequestHttpException('Просмотреть скидку можно только для текущей организации пользователя, либо для её дочерних');
        }

        return $discount;
    }

    /**
     * Создает скидку
     *
     * @param string $name
     * @param int    $value
     * @param int    $id_organization
     * @return Discount
     * @throws \yii\web\BadRequestHttpException
     */
    public function create($name, $value, $id_organization = null)
    {
        $discount = new Discount();

        if (!$id_organization) {
            $id_organization = $this->id_organization;
        }

        if ($id_organization !== $this->id_organization && !in_array($id_organization, Organizations::orgTreeIds($this->id_organization))) {
            throw new BadRequestHttpException('Создавать скидку можно только для текущей организации пользователя, либо для её дочерних');
        }

        $discount->name = $name;
        $discount->value = $value;
        $discount->id_organization = $id_organization;

        if (!$discount->save()) {
            $errors = $discount->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании скидки' : implode("\n", array_values($errors)));
        }

        return $discount;
    }

    /**
     * Редактирование скидки (кроме удаленных)
     *
     * @param int    $id
     * @param string $name
     * @param int    $value
     * @param int    $id_organization
     * @return Discount
     * @throws BadRequestHttpException
     */
    public function edit($id, $name = null, $value = null, $id_organization = null)
    {
        $discount = $this->findDiscount($id);

        if ($discount->getOldAttribute('is_system_discount') === true) {
            throw new BadRequestHttpException('Невозможно редактирование системной записи');
        }

        if (!in_array($discount->id_organization, Organizations::orgTreeIds($this->id_organization))) {
            throw new BadRequestHttpException('Редактировать скидку можно только для текущей организации пользователя, либо для её дочерних');
        }

        if ($discount->isReadOnly()) {
            throw new BadRequestHttpException('Удаленная скидка не подлежит редактированию');
        }

        if (!$id_organization) {
            $id_organization = $this->id_organization;
        }

        if ($id_organization !== $this->id_organization && !in_array($id_organization, Organizations::orgTreeIds($this->id_organization))) {
            throw new BadRequestHttpException('Для редактирования скидки нужно указать только текущую организацию пользователя, либо её дочернюю');
        }

        $discount->name = $name ? $name : $discount->name;
        $discount->value = $value ? $value : $discount->value;
        $discount->id_organization = $id_organization;

        if (!$discount->save()) {
            $errors = $discount->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании скидки' : implode("\n", array_values($errors)));
        }

        return $discount;
    }

    /**
     * Помечает скидку как удаленную
     *
     * @param int $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $discount = $this->findDiscount($id);

        if ($discount->isReadOnly()) {
            throw new BadRequestHttpException('Данная скидка удалена ранее');
        }

        if (!in_array($discount->id_organization, Organizations::orgTreeIds($this->id_organization))) {
            throw new BadRequestHttpException('Удалить скидку можно только для текущей организации пользователя, либо для её дочерних');
        }

        $discount->is_deleted = true;

        if (!$discount->save()) {
            $errors = $discount->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении скидки' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Находит и возвращает скидку с указанным id или генерирует ошибку
     * @param int $id
     * @return Discount
     * @throws BadRequestHttpException
     */
    protected function findDiscount($id)
    {
        $discount = Discount::findOne(['id' => $id]);

        if (empty($discount)) {
            throw new BadRequestHttpException('Указанная скидка не найдена');
        }

        return $discount;
    }
}
