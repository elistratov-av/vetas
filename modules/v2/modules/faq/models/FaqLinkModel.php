<?php

namespace app\modules\v2\modules\faq\models;

use app\models\db\FaqLinks;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class FaqLinkModel
{
    /**
     * @return FaqLinks
     * @throws \Throwable
     */
    public function list(int $id_faq)
    {
        return FaqLinks::find()
            ->andWhere(['id_faq' => $id_faq])
            ->all();
    }

    /**
     * @param $id
     * @return FaqLinks
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $faq_link = $this->findFaqLink($id);

        return $faq_link;
    }

    /**
     * Создает ссылку
     *
     * @param $href
     * @param $text
     * @param int $id_faq
     * @param $target_blank
     * @return FaqLinks
     * @throws BadRequestHttpException
     */
    public function create($href = null, $text, int $id_faq, $target_blank = null)
    {
        $faq_link = new FaqLinks();

        $faq_link->href = $href;
        $faq_link->text = $text;
        $faq_link->id_faq = $id_faq;
        $faq_link->target_blank = $target_blank;

        if (!$faq_link->save()) {
            $errors = $faq_link->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании ссылки для справки' : implode("\n", array_values($errors)));
        }

        return $faq_link;
    }

    /**
     * Редактирование ссылки
     *
     * @param $id
     * @param $href
     * @param $text
     * @param $target_blank
     * @return FaqLinks
     * @throws BadRequestHttpException
     */
    public function edit($id, $href = null, $text = null, $target_blank = null)
    {
        $faq_link = $this->findFaqLink($id);

        $faq_link->href = $href ?? $faq_link->href;
        $faq_link->text = $text ?? $faq_link->text;
        $faq_link->target_blank = $target_blank ?? $faq_link->target_blank;

        if (!$faq_link->save()) {
            $errors = $faq_link->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании ссылки для справки' : implode("\n", array_values($errors)));
        }

        return $faq_link;
    }

    /**
     * Удаляет ссылку
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $faq_link = $this->findFaqLink($id);

        if (!$faq_link->delete()) {
            $errors = $faq_link->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении ссылки для справки' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Находит и возвращает ссылку с указанным id или генерирует ошибку
     * @param $id
     * @return FaqLinks
     * @throws BadRequestHttpException
     */
    protected function findFaqLink($id)
    {
        $faq_link = FaqLinks::findOne(['id' => $id]);

        if (empty($faq_link)) {
            throw new BadRequestHttpException('Ссылка не найдена');
        }

        return $faq_link;
    }
}
