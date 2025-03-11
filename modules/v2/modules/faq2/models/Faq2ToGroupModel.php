<?php

namespace app\modules\v2\modules\faq2\models;

use app\models\db\Faq2ToGroup;
use yii\web\BadRequestHttpException;

class Faq2ToGroupModel
{
    /**
     * @return Faq2ToGroup[]
     * @throws \Throwable
     */
    public function list(int $id_group)
    {
        return Faq2ToGroup::find()
            ->andWhere(['id_faq2_group' => $id_group])
            ->all();
    }

    /**
     * @param $id
     * @return Faq2ToGroup
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $mapping = $this->findOne($id);

        return $mapping;
    }

    /**
     * Создает сопоставление
     *
     * @param int $id_faq2
     * @param int $id_faq2_group
     * @return bool
     * @throws BadRequestHttpException
     */
    public function create(int $id_faq2, int $id_faq2_group)
    {
        $mapping = Faq2ToGroup::findOne([
            'id_faq2' => $id_faq2,
            'id_faq2_group' => $id_faq2_group,
        ]);

        if (empty($mapping)) {
            $mapping = new Faq2ToGroup();

            $mapping->id_faq2 = $id_faq2;
            $mapping->id_faq2_group = $id_faq2_group;

            if (!$mapping->save()) {
                $errors = $mapping->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании ссылки для справки' : implode("\n", array_values($errors)));
            }

        }

        return true;
    }

    /**
     * Удаляет сопоставление
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $mapping = $this->findOne($id);

        if (!$mapping->delete()) {
            $errors = $mapping->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении сопоставление для справки' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Удаляет сопоставление
     *
     * @param $id_faq2_group
     * @param $id_faq2
     * @return int
     */
    public function delete2($id_faq2_group, $id_faq2)
    {
        return Faq2ToGroup::deleteAll([
                "id_faq2" => $id_faq2,
                "id_faq2_group" => $id_faq2_group,
            ]) > 0;
    }

    /**
     * Находит и возвращает сопоставление с указанным id или генерирует ошибку
     * @param $id
     * @return Faq2ToGroup
     * @throws BadRequestHttpException
     */
    protected function findOne($id)
    {
        $mapping = Faq2ToGroup::findOne(['id' => $id]);

        if (empty($mapping)) {
            throw new BadRequestHttpException('Сопоставление не найдено');
        }

        return $mapping;
    }

}
