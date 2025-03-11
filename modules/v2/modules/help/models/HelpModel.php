<?php

namespace app\modules\v2\modules\help\models;

use app\models\db\Faqs;
use app\models\db\Help;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class HelpModel
{
    /**
     * @param $search_text
     * @return Help[]
     * @throws \Throwable
     */
    public function all($search_text)
    {
        return Help::find()
            ->select(['id', 'caption', 'text'])
            ->orFilterWhere(['ILIKE', 'caption', $search_text])
            ->orFilterWhere(['ILIKE', 'text', $search_text])
            ->all();
    }

    /**
     * @param $id
     * @return Help
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $help = Help::find()
            ->andWhere(['id' => $id])
            ->with('help_links')
            ->with('files')
            ->asArray()
            ->one();

        if (empty($help)) {
            throw new BadRequestHttpException('Указанный элемент справочной информации не найден');
        }

        return $help;
    }

    /**
     * Создает справку
     *
     * @param $caption
     * @param $text
     * @return Help
     * @throws BadRequestHttpException
     */
    public function create($caption, $text)
    {
        $help = new Help();

        $help->caption = $caption;
        $help->text = $text;

        if (!$help->save()) {
            $errors = $help->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании справки' : implode("\n", array_values($errors)));
        }

        return $help;
    }

    /**
     * Редактирование справки
     *
     * @param $id
     * @param $caption
     * @param $text
     * @return Help
     * @throws BadRequestHttpException
     */
    public function edit($id, $caption = null, $text = null)
    {
        $help = $this->findHelp($id);

        $help->caption = $caption ?? $help->caption;
        $help->text = $text ?? $help->text;

        if (!$help->save()) {
            $errors = $help->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании справки' : implode("\n", array_values($errors)));
        }

        return $help;
    }

    /**
     * Удадяет справку
     *
     * @param $id
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $faq = $this->findHelp($id);

        if (!$faq->delete()) {
            $errors = $faq->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении справки' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Находит и возвращает справку с указанным id или генерирует ошибку
     * @param $id
     * @return Help
     * @throws BadRequestHttpException
     */
    protected function findHelp($id)
    {
        $help = Help::findOne(['id' => $id]);

        if (empty($help)) {
            throw new BadRequestHttpException('Указанный элемент справочной информации не найден');
        }

        return $help;
    }
}
