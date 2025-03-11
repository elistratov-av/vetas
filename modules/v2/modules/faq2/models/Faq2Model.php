<?php

namespace app\modules\v2\modules\faq2\models;

use app\models\db\Faq2;
use app\modules\v2\common\skeletons\CommonList;
use yii\web\BadRequestHttpException;

class Faq2Model
{
    /**
     * @return Faq2[]
     * @throws \Throwable
     */
    public function all()
    {
        return Faq2::find()
            ->select(['id', 'question', 'answer'])
            ->all();
    }

    /**
     * @param $id
     * @return Faq2
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $faq = Faq2::findOne(['id' => $id]);

        if (empty($faq)) {
            throw new BadRequestHttpException('Указанный элемент справочной информации не найден');
        }

        return $faq;
    }

    /**
     * @param $question
     * @param $answer
     * @param $page
     * @param $limit
     * @return CommonList
     * @throws BadRequestHttpException
     */
    public function search(
        $question, $answer,
        $page, $limit
    )
    {
        $filter = ['and'];
        if (!is_null($question)) {
            $filter[] = ['ilike', 'question', $question];
        }
        if (!is_null($answer)) {
            $filter[] = ['ilike', 'answer', $answer];
        }
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $query = Faq2::find()
            ->andFilterWhere($filter)
            ->orderBy('question ASC');
        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        return new CommonList(
            'faq2_group', $query->all(),
            $count->count(), $page, $limit
        );
    }

    /**
     * Создает faq2
     *
     * @param $question
     * @param $answer
     * @return Faq2
     * @throws BadRequestHttpException
     */
    public function create($question, $answer)
    {
        $faq = new Faq2();

        $faq->question = $question;
        $faq->answer = $answer;

        if (!$faq->save()) {
            $errors = $faq->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании справки' : implode("\n", array_values($errors)));
        }

        return $faq;
    }

    /**
     * Редактирование справки (кроме удаленных)
     *
     * @param $id
     * @param $question
     * @param $answer
     * @return Faq2
     * @throws BadRequestHttpException
     */
    public function edit($id, $question = null, $answer = null)
    {
        $faq = $this->get($id);

        $faq->question = $question ?? $faq->question;
        $faq->answer = $answer ?? $faq->answer;

        if (!$faq->save()) {
            $errors = $faq->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании справки' : implode("\n", array_values($errors)));
        }

        return $faq;
    }

    /**
     * Помечает справку как удаленную
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $faq = $this->get($id);

        if (!empty($faq)) {
            if (!$faq->delete()) {
                $errors = $faq->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении справки' : implode("\n", array_values($errors)));
            }
        }
    }

}
