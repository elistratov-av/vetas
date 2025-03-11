<?php

namespace app\modules\v2\modules\gosvetnadzor\controllers;

use app\common\components\FileService;
use app\models\db\Files;
use app\models\db\OwnerFeedback;
use app\models\db\Violation;
use app\modules\soap\models\Pets;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\OwnerFeedbackModel;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

class OwnerFeedbackController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'http_authenticator' => [
                    'except' => [
                        'decline-provide',
                        'create',
                        'get-info-for-feedback',
                        'upload-feedback-file',
                    ],
                ],
            ]
        );
    }

    /**
     * @param int $id_violation
     * @return OwnerFeedback[]
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet(int $id_violation)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        /** @var OwnerFeedback $ownerFeedback */
        if (!$ownerFeedback = OwnerFeedback::find()->where(['id_violation' => $id_violation])->orderBy(['id' => SORT_DESC])->one()) {
            throw new BadRequestHttpException('Не найдено обратной связи по нарушению');
        }

        return [
            'result' => $ownerFeedback,
        ];
    }

    /**
     * Публичный метод, для получения информации о владельце и животном по нарушению
     *
     * @param string $feedbackFormToken
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetInfoForFeedback(string $feedbackFormToken)
    {
        /** @var Violation $violation */
        if (!$violation = Violation::find()->where(['feedback_token' => $feedbackFormToken])->one()) {
            throw new BadRequestHttpException('Не найдено нарушения с переданным токеном обратной связи');
        }
        // Если нарушение уже закрыто - считаем форму заполненой по умолчанию
        if ($violation->isFinished()) {
            $isFormFilled = true;
        } else {
            $feedback = OwnerFeedback::find()->where(['id_violation' => $violation->id_violation, 'is_processed' => false])->one();
            $isFormFilled = !!$feedback;
        }

        $pet = Pets::find()
            ->where(['pets.id' => $violation->id_pet])->joinWith([
                'owner',
                'breed',
                'species'
            ])
            ->asArray()
            ->one();

        $pet += [
            'owner_feedback' => ($violation
                    ->getOwner_feedback()
                    ->with([
                        'files',
                        'organization',
                        'tmc',
                    ])
                    ->orderBy(['id' => SORT_DESC])
                    ->asArray()
                    ->one()
                ??
                [])
        ];

        return [
            'pet'          => $pet,
            'isFormFilled' => $isFormFilled,
        ];
    }

    /**
     * Публичный метод, для владельцев животных для отказа от предоставления сведений
     *
     * @param $feedbackFormToken
     * @param $reason
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeclineProvide($feedbackFormToken, $reason)
    {
        return [
            'result' => (new OwnerFeedbackModel())->declineProvide($feedbackFormToken, $reason),
        ];
    }

    /**
     * Публичный метод, для владельцев животных
     * Проверяем легитимность запроса по токену обратной связи записаном в Violation
     *
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate(
        $feedbackFormToken,
        $plannedCloseDate = null,
        $identificationTypeId = null,
        $identificationNumber = null,
        $vaccineId = null,
        $batch = null,
        $organizationId = null,
        $isOutOrg = null,
        $vaccinationDate = null,
        $expireDate = null,
        $productionDate = null,
        $validUntil = null)
    {
        return [
            'result' => (new OwnerFeedbackModel())->create(
                $feedbackFormToken,
                $plannedCloseDate,
                $identificationTypeId,
                $identificationNumber,
                $vaccineId,
                $batch,
                $organizationId,
                $isOutOrg,
                $vaccinationDate,
                $expireDate,
                $productionDate,
                $validUntil)
        ];
    }

    /**
     * Публичный метод, для прикрепления файлов к OwnerFeedback
     *
     * @param string $feedbackFormToken
     * @param $file
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\IntegrityException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionUploadFeedbackFile(string $feedbackFormToken)
    {
        /** @var Violation $violation */
        if (!$violation = Violation::find()->where(['feedback_token' => $feedbackFormToken])->one()) {
            throw new BadRequestHttpException('Не найдено нарушения с переданным токеном обратной связи');
        }
        /** @var OwnerFeedback $feedback */
        if (!$feedback = OwnerFeedback::find()->where(['id_violation' => $violation->id_violation, 'is_processed' => false])->one()) {
            throw new BadRequestHttpException('Не обнаружено обратной связи к которой прикреплять файл');
        }

        try {
            /** @var FileService $fileService */
            $fileService = \Yii::$app->fileService;

            $resource = $fileService->upload(UploadedFile::getInstanceByName('file'));
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $entityType = 'owner_feedback';
        if (!array_key_exists($entityType, Files::$entity_types)) {
            throw new BadRequestHttpException("Нет такого entity_type {$entityType}");
        }

        $resource->entity_type = $entityType;
        $resource->entity_id = $feedback->id;
        $fileService->attach($resource);

        return [
            'result' => $resource,
        ];
    }

    /**
     * Метод для Инспектора для подтверждения переданных владельцем животного данных
     *
     * @param $feedbackId
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionApprove($feedbackId)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new OwnerFeedbackModel())->approve($feedbackId)
        ];
    }

    /**
     * Метод для Инспектора для отклонения переданных владельцем животного данных
     *
     * @param $feedbackId
     * @param $reason
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDecline($feedbackId, $reason)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new OwnerFeedbackModel())->decline($feedbackId, $reason)
        ];
    }
}
