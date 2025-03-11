<?php

namespace app\modules\v2\modules\faq2\models;

use app\models\db\Faq2;
use app\models\db\Faq2Group;
use yii\web\BadRequestHttpException;

class Faq2GroupModel
{
    /**
     * @return Faq2Group[]
     * @throws \Throwable
     */
    public function all()
    {
        return Faq2Group::find()
            ->select(['id', 'code', 'title'])
            ->all();
    }

    /**
     * @param $id
     * @return Faq2Group
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $group = Faq2Group::findOne(['id' => $id]);

        if (empty($group)) {
            throw new BadRequestHttpException('Указанный элемент раздел справочной информации не найден');
        }

        return $group;
    }

    /**
     * @param $id_faq2_group
     * @return Faq2[]
     * @throws BadRequestHttpException
     */
    public function getFaq2($id_faq2_group)
    {
        $group = Faq2Group::findOne(['id' => $id_faq2_group]);

        if (empty($group)) {
            throw new BadRequestHttpException('Указанный элемент раздел справочной информации не найден');
        }

        return $group->getFaq2()->all();
    }

    /**
     * @param $code
     * @return Faq2Group
     * @throws BadRequestHttpException
     */
    public function getByCode($code)
    {
        $group = Faq2Group::findOne(['code' => $code]);

        if (empty($group)) {
            throw new BadRequestHttpException('Указанный элемент раздел справочной информации не найден');
        }

        return $group;
    }

    /**
     * Создает Faq2Group
     *
     * @param $code
     * @param $title
     * @return Faq2Group
     * @throws BadRequestHttpException
     */
    public function create($code, $title)
    {
        $group = new Faq2Group();

        $group->code = $code;
        $group->title = $title;

        if (!$group->save()) {
            $errors = $group->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании раздела справки' : implode("\n", array_values($errors)));
        }

        return $group;
    }

    /**
     * Редактирование справки (кроме удаленных)
     *
     * @param $id
     * @param $code
     * @param $title
     * @return Faq2Group
     * @throws BadRequestHttpException
     */
    public function edit($id, $code = null, $title = null)
    {
        $group = $this->get($id);

        $group->code = $code ?? $group->code;
        $group->title = $title ?? $group->title;

        if (!$group->save()) {
            $errors = $group->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании раздела справки' : implode("\n", array_values($errors)));
        }

        return $group;
    }

    /**
     * Помечает раздел справки как удаленный
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $group = $this->get($id);

        if (!empty($group)) {
            if (!$group->delete()) {
                $errors = $group->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении раздела справки' : implode("\n", array_values($errors)));
            }
        }
    }

}
