<?php


namespace app\modules\v2\modules\gosvetnadzor\models;


use app\common\validators\FullTrimValidator;
use app\models\db\ViolationAdminRights;
use app\modules\v2\common\skeletons\CommonList;
use yii\web\BadRequestHttpException;

class ViolationAdminRightsModel
{
    /**
     * Справочник АПН (только не удаленные)
     *
     * @return ViolationAdminRights[]
     */
    public function getAll()
    {
        return ViolationAdminRights::find()
            ->where(['is_deleted' => false])
            ->orderBy('short_name')
            ->all();
    }

    /**
     * Создание записи в справочнике АПН
     *
     * @param $short_name
     * @param $full_name
     * @param $description
     * @return ViolationAdminRights
     * @throws BadRequestHttpException
     */
    public function create($short_name, $full_name, $description)
    {
        $ARV = new ViolationAdminRights();

        $ARV->short_name = $short_name;
        $ARV->full_name = $full_name;
        $ARV->description = $description;

        if (!$ARV->save()) {
            $errors = $ARV->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании АПН' : implode("\n", array_unique(array_values($errors))));
        }

        return $ARV;
    }

    /**
     * Редактирование АПН
     *
     * @param $id
     * @param $short_name
     * @param $full_name
     * @param $description
     * @return ViolationAdminRights|null
     * @throws BadRequestHttpException
     */
    public function edit($id, $short_name, $full_name, $description)
    {
        $ARV = ViolationAdminRights::findOne(['id_ARV' => $id]);

        if (empty($ARV)) {
            throw new BadRequestHttpException('Указанная запись не найдена');
        }

        if ($ARV->is_deleted) {
            throw new BadRequestHttpException('Редактирование АПН не доступно. АПН было помечено удаленным ранее');
        }

        if ($ARV->isReadOnly()) {
            throw new BadRequestHttpException('Редактирование АПН не доступно. АПН используется Госветнадзором');
        }

        $ARV->short_name = $short_name;
        $ARV->full_name = $full_name;
        $ARV->description = $description;

        if (!$ARV->save()) {
            $errors = $ARV->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении АПН' : implode("\n", array_unique(array_values($errors))));
        }

        return $ARV;
    }

    /**
     * Пометка "удалленное" для АПН
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $ARV = ViolationAdminRights::findOne(['id_ARV' => $id]);

        if (empty($ARV)) {
            throw new BadRequestHttpException('Указанная запись не найдена');
        }

        $ARV->is_deleted = true;

        if (!$ARV->save()) {
            $errors = $ARV->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении АПН' : implode("\n", array_unique(array_values($errors))));
        }
    }

    /**
     * Возвращает АПН по id
     *
     * @param $id
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getById($id)
    {
        return ViolationAdminRights::find()
            ->select('*')
            ->addSelect(ViolationAdminRights::getExpressionIsReadonly())
            ->with('files')
            ->where(['id_ARV' => $id])
            ->asArray()
            ->one();
    }

    /**
     * Список АПН
     *
     * @param int $page
     * @param int $limit
     * @param null $filter
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function getList($page = 1, $limit = 10, $filter = null)
    {
        $this->validateFilter($filter);
        $fullTrimValidator = new FullTrimValidator();

        $query = ViolationAdminRights::find()
            ->select('*')
            ->addSelect(ViolationAdminRights::getExpressionIsReadonly())
            ->andWhere(['is_deleted' => false])
            ->orderBy('short_name');

        // Фильтр
        if (!empty($filter['search'])) {
            $search = $fullTrimValidator->validateValue($filter['search']);
            $query->andWhere([
                'OR',
                ['ILIKE', 'short_name', $search],
                ['ILIKE', 'full_name', $search],
            ]);
        }

        $query_count = clone $query;

        $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);

        return new CommonList(
            'violation_admin_rights',
            $query->asArray()->all(),
            $query_count->count(),
            $page,
            $limit
        );
    }

    /**
     * Проверяем фильтр
     *
     * @param $filter
     * @return bool
     * @throws BadRequestHttpException
     */
    protected function validateFilter($filter)
    {
        if (empty($filter)){
            return true;
        }

        if (array_key_exists('search', $filter) && !is_string($filter['search'])) {
            throw new BadRequestHttpException('Поле filter.search должно быть строкой');
        }

        return true;
    }
}
