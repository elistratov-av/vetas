<?php


namespace app\modules\v2\modules\organization\models;


use app\models\db\OrganizationServicesWithoutMarkUp;
use yii\web\BadRequestHttpException;

class ServicesWithoutMarkUpModel
{
    /**
     * Возвращает список услуг без повышения стоимости ночью
     * @param $id_organization
     * @throws BadRequestHttpException
     * @return array
     */
    public function getByOrganization($id_organization)
    {
        $this->validateIdOrganization($id_organization);

        return OrganizationServicesWithoutMarkUp::find()
            ->where(['id_organization' => $id_organization])
            ->with('service')
            ->asArray()
            ->all();
    }

    /**
     * Сохранение списка услуг без повышения стоимости ночью
     *
     * @param $id_organization
     * @param $services
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function save($id_organization, $services)
    {
        $this->validateIdOrganization($id_organization);
        $this->validateServices($services);

        OrganizationServicesWithoutMarkUp::getDb()->beginTransaction();

        // Удаляем старые
        OrganizationServicesWithoutMarkUp::deleteAll([
            'id_organization' => $id_organization,
        ]);

        // Добавляем новые
        foreach ($services as $service) {
            $new_item = new OrganizationServicesWithoutMarkUp([
                'id_organization' => $id_organization,
                'id_service' => $service['id'],
            ]);

            if (!$new_item->save()) {
                $errors = $new_item->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении' : implode("\n", array_values($errors)));
            }
        }

        OrganizationServicesWithoutMarkUp::getDb()->transaction->commit();
    }

    /**
     * @param $services
     * @throws BadRequestHttpException
     */
    protected function validateServices($services)
    {
        if (empty($services)) {
            return;
        }

        if (!is_array($services)) {
            throw new BadRequestHttpException('Ошибочный формат services');
        }

        foreach ($services as $service) {
            if (!is_array($service) || !array_key_exists('id', $service) || !is_numeric($service['id'])) {
                throw new BadRequestHttpException('Ошибочный формат services');
            }
        }
    }

    /**
     * @param $id_organization
     * @throws BadRequestHttpException
     */
    protected function validateIdOrganization($id_organization)
    {
        if (empty($id_organization)) {
            throw new BadRequestHttpException('Передан пустой id_organization');
        }

        if (!is_numeric($id_organization)) {
            throw new BadRequestHttpException('id_organization должен быть числом');
        }
    }
}
