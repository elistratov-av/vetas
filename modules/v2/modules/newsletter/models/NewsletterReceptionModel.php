<?php

namespace app\modules\v2\modules\newsletter\models;

use app\models\db\NewsletterReception;
use app\models\db\NewsletterType;
use app\modules\admin\models\Organization;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class NewsletterReceptionModel
{
    public function get(Organization $organization): array
    {
        if (!$reception = $this->getReception($organization)) {
            $reception = new NewsletterReception();
            $reception->setAttributes([
                'type' => NewsletterType::TYPE_RECEPTION,
                'organization_id' => $organization->id,
            ]);
        }

        return $this->getResponse($reception);
    }

    public function edit(Organization $organization, $data): array
    {
        if (!$reception = $this->getReception($organization)) {
            $reception = new NewsletterReception();
            $reception->organization_id = $organization->id;
        }

        $fields = ['name', 'text'];

        foreach ($fields as $field) {
            if (!empty($data[$field])) {
                $reception->setAttribute($field, $data[$field]);
            }
        }

        $reception->type = NewsletterType::TYPE_RECEPTION;

        if (!$reception->save()) {
            $errors = $reception->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании ' : implode("\n", array_values($errors)));
        }

        return $this->getResponse($reception);
    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function delete(Organization $organization): bool
    {
        if (!$reception = $this->getReception($organization)) {
            throw new NotFoundHttpException();
        }

        if (!$reception->delete()) {
            $errors = $reception->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении' : implode("\n", array_values($errors)));
        }

        return true;
    }

    public function changeStatus(Organization $organization, $status): array
    {
        if (!$reception = $this->getReception($organization)) {
            throw new NotFoundHttpException();
        }

        $reception->status  = (bool)$status;
        $reception->save();

        return $this->getResponse($reception);
    }

    private function getReception(Organization $organization)
    {
        return NewsletterReception::find()->where(['organization_id' => $organization->id])->one();
    }

    private function getResponse(NewsletterReception $newsletterReception): array
    {
        $response = $newsletterReception->toArray();
        $response['type'] = $newsletterReception->getType()->one()->toArray();
        $response['organization'] = $newsletterReception->getOrganization()->one()->toArray(['id', 'name']);

        return $response;
    }
}
