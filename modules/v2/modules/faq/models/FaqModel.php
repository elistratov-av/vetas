<?php

namespace app\modules\v2\modules\faq\models;

use app\models\db\Faqs;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class FaqModel
{
    /**
     * @return Faqs
     * @throws \Throwable
     */
    public function all()
    {
        return Faqs::find()
            ->select(['id', 'question', 'answer'])
            ->all();
    }

    /**
     * @param $id
     * @return Faqs
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $faq = Faqs::find()
            ->andWhere(['id' => $id])
            ->with('faq_links')
            ->with('files')
            ->asArray()
            ->one();

        if (empty($faq)) {
            throw new BadRequestHttpException('Указанный элемент справочной информации не найден');
        }

        return $faq;
    }

    /**
     * Создает справку
     *
     * @param $question
     * @param $answer
     * @return Faqs
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function create($question, $answer)
    {
        $faq = new Faqs();

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
     * @return Faqs
     * @throws BadRequestHttpException
     */
    public function edit($id, $question = null, $answer = null)
    {
        $faq = $this->findFaq($id);

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
        $faq = $this->findFaq($id);

        if (!$faq->delete()) {
            $errors = $faq->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении справки' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Находит и возвращает справку с указанным id или генерирует ошибку
     * @param $id
     * @return Faqs
     * @throws BadRequestHttpException
     */
    protected function findFaq($id)
    {
        $faq = Faqs::findOne(['id' => $id]);

        if (empty($faq)) {
            throw new BadRequestHttpException('Указанный элемент справочной информации не найден');
        }

        return $faq;
    }
}
