<?php

namespace app\modules\v2\modules\help\models;

use app\models\db\HelpLinks;
use yii\web\BadRequestHttpException;

class HelpLinkModel
{

    /**
     * @param int $id_help
     * @return HelpLinks[]
     */
    public function list(int $id_help)
    {
        return HelpLinks::find()
            ->andWhere(['id_help' => $id_help])
            ->all();
    }

    /**
     * @param $id
     * @return HelpLinks
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        $help_link = $this->findHelpLink($id);

        return $help_link;
    }

    /**
     * Создает ссылку
     *
     * @param $href
     * @param $text
     * @param int $id_help
     * @param $target_blank
     * @return HelpLinks
     * @throws BadRequestHttpException
     */
    public function create($href, $text, int $id_help, $target_blank = null)
    {
        $help_link = new HelpLinks();

        $help_link->href = $href;
        $help_link->text = $text;
        $help_link->id_help = $id_help;
        $help_link->target_blank = $target_blank;

        if (!$help_link->save()) {
            $errors = $help_link->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании ссылки для справки' : implode("\n", array_values($errors)));
        }

        return $help_link;
    }

    /**
     * Редактирование ссылки
     *
     * @param $id
     * @param $href
     * @param $text
     * @param $target_blank
     * @return HelpLinks
     * @throws BadRequestHttpException
     */
    public function edit($id, $href = null, $text = null, $target_blank = null)
    {
        $help_link = $this->findHelpLink($id);

        $help_link->href = $href ?? $help_link->href;
        $help_link->text = $text ?? $help_link->text;
        $help_link->target_blank = $target_blank ?? $help_link->target_blank;

        if (!$help_link->save()) {
            $errors = $help_link->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании ссылки для справки' : implode("\n", array_values($errors)));
        }

        return $help_link;
    }

    /**
     * Удаляет ссылку
     *
     * @param $id
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $help_link = $this->findHelpLink($id);

        if (!$help_link->delete()) {
            $errors = $help_link->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении ссылки для справки' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Находит и возвращает ссылку с указанным id или генерирует ошибку
     * @param $id
     * @return HelpLinks
     * @throws BadRequestHttpException
     */
    protected function findHelpLink($id)
    {
        $help_link = HelpLinks::findOne(['id' => $id]);

        if (empty($help_link)) {
            throw new BadRequestHttpException('Ссылка не найдена');
        }

        return $help_link;
    }
}
