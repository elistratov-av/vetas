<?php

namespace app\modules\v2\modules\organization\models;

use app\models\db\OutsideOrg;
use yii\web\BadRequestHttpException;

class OutsideOrganizationModel
{
    /**
     * @param $id
     * @return OutsideOrg
     * @throws BadRequestHttpException
     */
    public function get($id)
    {
        return $this->findModel($id);
    }

    public function create($name)
    {
        if (OutsideOrg::find()->where(['name' => $name])->one()) {
            throw new BadRequestHttpException('Уже существует организация с таким именем');
        }
        $org = (new OutsideOrg);
        $org->name = $name;
        $org->save();

        return $org;
    }

    /**
     * Удаление организации
     * @param $id
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $org = $this->findModel($id);
        $org->is_deleted = true;
        if (!$org->save()) {
            $errors = $org->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении организации' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Редавтирование организации
     * @param $id
     * @param $name
     * @throws BadRequestHttpException
     */
    public function edit($id, $name)
    {
        $org = $this->findModel($id);
        $org->name = $name;
        if (!$org->save()) {
            $errors = $org->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при педактировании организации' : implode("\n", array_values($errors)));
        }
    }

    /**
     * @param $id
     * @return OutsideOrg
     * @throws BadRequestHttpException
     */
    protected function findModel($id)
    {
        $org = OutsideOrg::findOne(['id' => $id]);
        if (empty($org)) {
            throw new BadRequestHttpException('Организация с указанным ID не найдена');
        }

        return $org;
    }
}
