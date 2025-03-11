<?php

namespace app\modules\v2\modules\visit\controllers;

use app\common\models\VisitStatus;
use app\models\db\Violation;
use app\models\db\Visits;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\visit\models\BillModel;
use app\models\db\SignedVisitModel;
use app\modules\v2\modules\visit\models\VisitChangeStatusModel;
use app\modules\v2\modules\visit\models\VisitModel;
use app\modules\v2\modules\visit\models\VisitSaveModel;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException as ForbiddenHttpExceptionAlias;
use yii\web\NotFoundHttpException;

use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\TmpPetOwners;
use app\models\db\TmpPets;
use app\models\db\VisitPets;
use app\modules\animalid\models\PetsModel;


/**
 * Class GenericVisitController
 * @package app\modules\v2\modules\visit\controllers
 */
abstract class GenericVisitController extends BaseController
{
    use VisitTrait;

    /**
     * @param int $id_organization
     * @param string $date_from
     * @param null $id_shift_type
     * @param int $days_count
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return \app\modules\v2\modules\visit\skeletons\visit\Lists
     */
    abstract public function actionList(
        int    $id_organization,
        string $date_from,
               $id_shift_type = null,
        int    $days_count = 1,
        int    $page = 1,
        int    $limit = 10,
        array  $filter = []
    );

    /**
     * @param int $id_owner
     * @param int|array $id_pet
     * @param int $id_organization
     * @param int $channel
     * @param string|null $start_dttm
     * @param int|null $id_specialist
     * @param int|null $source
     * @param int|null $author
     * @param array|null $services
     * @param bool $is_veteran
     * @param bool $is_disabled
     * @param bool $is_blind
     * @param bool $is_orphan
     * @param bool $is_large_family
     * @param bool $is_veteran_of_labour
     * @param string|null $preferences_document
     * @param string|null $visit_to_address
     * @param string|null $description
     * @return array
     */
    abstract public function actionCreate(
        int    $id_owner,
               $id_pet,
        int    $id_organization,
        int    $channel,
        string $start_dttm,
        int    $id_specialist,
        int    $source = null,
        int    $author = null,
        array  $services = null,
        bool   $is_veteran = null,
        bool   $is_disabled = null,
        bool   $is_blind = null,
        bool   $is_orphan,
        bool   $is_large_family,
        bool   $is_veteran_of_labour,
        string $preferences_document = null,
               $visit_to_address = null,
               $description = null
    );

    /**
     * @param int $id
     * @param int $id_owner
     * @param int|array $id_pet
     * @param int $id_organization
     * @param string|null $start_dttm
     * @param int|null $id_specialist
     * @param array|null $services
     * @param string|null $visit_to_address
     * @param string|null $description
     * @return array
     */
    abstract public function actionEdit(
        int    $id,
        int    $id_owner,
               $id_pet,
        int    $id_organization,
        string $start_dttm = null,
        int    $id_specialist = null,
        array  $services = null,
               $visit_to_address = null,
               $description = null
    );

    /**
     * Возвращает указанный визит
     * https://jira.altarix.ru/browse/VETAIS-865
     *
     * @param $id_visit
     * @return array
     */
    public function actionGet($id_visit): array
    {
        $visit = $this->findVisit($id_visit, true, true);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        return [
            'result' => $visit,
        ];
    }

    /**
     * @param int $id_visit
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionReferrals(int $id_visit): array
    {
        $this->checkAccess($this->action->getUniqueId(), $this->findVisit($id_visit), $this->actionParams);

        $visitModel = new VisitModel();

        return [
            'result' => $visitModel->referrals($id_visit),
        ];
    }

    /**
     * Взятие приема в работу (кнопка "Взять в работу")
     *
     * @param int $id
     * @return array
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionStart(int $id): array
    {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitChangeStatusModel(['visit' => $visit]);

        if (!$model->startVisit()) {
            $this->errorResponse($model, 'Ошибка при изменении статуса приема');
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * Завершение приема (кнопка "Завершить прием")
     *
     * @param int $id
     * @param bool $services
     * @param string $sign ЭЦП
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpExceptionAlias
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionFinish(int $id, bool $services, string $sign = null): array
    {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitChangeStatusModel(['visit' => $visit]);

        $this->checkIdentificationAndVaccinationViolation($visit);

        if (!$model->finishVisit($services)) {
            $this->errorResponse($model, 'Ошибка при изменении статуса приема');
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * Завершение приема без оплаты (кнопка "Завершить прием")
     *
     * @param int $id
     * @param bool $services
     * @param string $sign ЭЦП
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpExceptionAlias
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionFinishUnpayed(int $id, bool $services, string $sign = null): array
    {
        $visit = $this->findVisit($id);
        $visitStart = $visit->start_dttm;

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitChangeStatusModel(['visit' => $visit]);

        $this->checkIdentificationAndVaccinationViolation($visit);

        if (!$model->finishUnpayedVisit($services)) {
            $this->errorResponse($model, 'Ошибка при изменении статуса приема');
        }
        $insertNotification = \Yii::$app->db->createCommand('INSERT into public.notifications
                                                                (title, 
                                                                 text,
                                                                 id_visit,
                                                                 id_user,
                                                                 created_by,
                                                                 created_at)
                                                                VALUES (:title,
                                                                        :text,
                                                                        :id_visit,
                                                                        :id_user,
                                                                        :created_by,
                                                                        :created_at)');
        // $visitTimeRange = $visit->time_range;
        $start_date = date_format(new \DateTime($visitStart), "d.m.Y");
        $start_time = date_format(new \DateTime($visitStart), "H:i:s");
        $text = "Ожидается подтверждение оплаты приема (талон №$visit->ticket_number дата $start_date в $start_time)";
        $title = 'Прием завершен без оплаты';
        //$date = date("Y-m-d H:i:s");

        //

        $query='SELECT users.id FROM public.specialists ';
        $query.='LEFT JOIN users ON specialists.id_user=users.id ';
        $query.='LEFT JOIN auth_assignment ON specialists.id=auth_assignment.id_specialist ';
        $query.='WHERE specialists.expel_date IS NULL AND specialists.id_organization='.$visit->id_organization.' AND auth_assignment.item_name=\'managementGos\' ';
        $query.='GROUP BY users.id, specialists.id,auth_assignment.item_name ';
        $query.='ORDER BY specialists.id_organization ';
        
        $users_for_notification = \Yii::$app->db->createCommand($query)->queryAll();
        foreach ($users_for_notification as $user) {
            try {
                $insertNotification->bindValues([
                    ':title' => $title,
                    ':text' => $text,
                    ':id_visit' => $visit->id,
                    ':id_user' => $user['id'],
                    ':created_by' => $visit->created_by,
                    ':created_at' => 'NOW()'
                ])->execute();
            } catch (\Exception $e) {
                file_put_contents('debug.txt', print_r($e, true), FILE_APPEND);
            }
        }
                
        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * @param int $visit_id
     * @return bool
     */
    private
    function hasVaccinationViolation($visit_id)
    {
        return $this->hasViolation('vaccination_violation', $visit_id);
    }

    /**
     * @param int $visit_id
     * @return bool
     */
    private
    function hasIdentificationViolation($visit_id)
    {
        return $this->hasViolation('ident_violation', $visit_id);
    }

    /**
     * @param string $type
     * @param int $visit_id
     * @return bool
     */
    private
    function hasViolation($type, $visit_id)
    {
        return Violation::find()
            ->leftJoin('violation_type as vt', 'violation.id_type = vt.id_type')
            ->where([
                'AND',
                ['IS', 'date_plan', null],
                ['vt.type' => $type],
                ['id_visit' => $visit_id],
                ['rejection_reason' => null],
            ])
            ->exists();
    }

    /**
     * @param Visits $visit
     * @throws BadRequestHttpException
     */
    private
    function checkIdentificationAndVaccinationViolation($visit)
    {
        $vaccinationViolation = $this->hasVaccinationViolation($visit->id);
        $identificationViolation = $this->hasIdentificationViolation($visit->id);

        if ($vaccinationViolation || $identificationViolation) {
            $errorMessage = "Внимание! Не возможно завершить прием, у животного отсутствует ";

            if ($vaccinationViolation && $identificationViolation) {
                $errorMessage .= "вакцинация и идентификация.";
            } else if ($vaccinationViolation) {
                $errorMessage .= "вакцинация.";
            } else if ($identificationViolation) {
                $errorMessage .= "идентификация.";
            }

            throw new BadRequestHttpException($errorMessage);
        }
    }


    /**
     * Отмена приема (кнопка "Отменить запись")
     *
     * @param int $id
     * @param string $cancel_initiator
     * @param string $change_reason
     * @return array
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=106893652
     */
    public function actionCancel(int $id, string $cancel_initiator, string $change_reason = null): array
    {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitChangeStatusModel(['visit' => $visit]);

        if (!$model->cancelVisit($cancel_initiator, $change_reason)) {
            $this->errorResponse($model, 'Ошибка при изменении статуса приема');
        }

        if ($visit->isMosRu() && $visit->owner && $visit->owner->sso_id === null) {
            // 1. Обновляем Приёмы
            $visits = [$visit];
            $visitsIds = array_map(function ($v) {
                return $v['id'];
            }, $visits);
            Visits::updateAll(['id_pet' => null, 'id_owner' => null], ['IN', 'id', $visitsIds]);
            VisitPets::updateAll(['id_pet' => null], ['IN', 'id_visit', $visitsIds]);

            // 2. Удаляем Питомцев
            $petsIds = array_map(function ($v) {
                return $v['id_pet'];
            }, $visits);
            Pets::deleteAll(['id' => $petsIds]);
            $tmpPetsIds = array_map(function ($v) {
                return $v['id_pet_tmp'];
            }, $visit['pets']);
            TmpPets::deleteAll(['id' => $tmpPetsIds]);

            // 3. Удаляем Владельцев
            $ownersIds = array_map(function ($v) {
                return $v['id_owner'];
            }, $visits);
            PetOwners::deleteAll(['id' => $ownersIds]);
            $tmpOwnersIds = array_map(function ($v) {
                return $v['owner']['id_pet_owner_tmp'];
            }, $visits);
            TmpPetOwners::deleteAll(['id' => $tmpOwnersIds]);
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * Оплата приема (кнопка "Подтвердить оплату")
     *
     * @param int $id
     * @param int $id_discount
     * @param array $applied_discounts_balance_tmc
     * @param array $applied_discounts_services
     * @return array
     *
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpExceptionAlias
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\NotFoundHttpException
     * @throws \Throwable
     */
    public
    function actionConfirmPayment(int $id, int $id_discount = null, $applied_discounts_balance_tmc = null, $applied_discounts_services = null): array
    {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        if ($visit->status == VisitStatus::CANCELED) {
            $this->errorResponse($visit, 'Нельзя оплатить отмененный прием');
        }
        if (empty($visit->services)) {
            $this->errorResponse($visit, 'Для подтверждения оплаты необходимо, чтобы в приеме были услуги');
        }

        if ($visit->is_paid === false) {
            // сохраняем скидки
            $billing = new BillModel(
                $id,
                $id_discount,
                $applied_discounts_balance_tmc,
                $applied_discounts_services);

            $billing->discountSave();

            $visit->is_paid = true;
            if (!$visit->save(true, ['is_paid', 'updated_at', 'updated_by'])) {
                $this->errorResponse($visit, 'Ошибка при изменении статуса оплаты приема');
            }
        }
        // Очищаем ненужные описания
        \Yii::debug('Clearing visit descriptions');
        VisitDescriptionsModel::deleteExcessVisitDescriptionsFromDB($id);

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * Метод подписи завершенного приема
     *
     * @param int $id id приема
     * @param string $sign закодированная в base64 электронная подпись
     * @param string $document закодированный в base64 xml с данными приема
     * @param string $cert_number номер сертификата электронной подписи
     * @param string $cert_owner владелец сертификата
     * @param string $valid_from сертификат действителен с
     * @param string $valid_to сертификат действителен по
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpExceptionAlias
     * @throws NotFoundHttpException
     */
    public
    function actionSign($id, $sign, $document, $cert_number, $cert_owner, $valid_from, $valid_to)
    {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        if ($visit->status != VisitStatus::FINISHED) {
            $this->errorResponse($visit, 'Вы можете подписать только завершенные приемы');
        }

        $now = new \DateTime();

        $visitSign = new SignedVisitModel();
        $visitSign->id_visit = $id;
        $visitSign->sign_hash = $sign;
        $visitSign->document = $document;
        $visitSign->cert_number = $cert_number;
        $visitSign->cert_owner = $cert_owner;
        $visitSign->valid_from = $valid_from;
        $visitSign->valid_to = $valid_to;
        $visitSign->created_at = $now->format('Y-m-d H:i:s');

        if (!$visitSign->save()) {
            $this->errorResponse($visitSign, 'Ошибка при подписании приема');
        }

        $visit->time_signed = $now->format('Y-m-d');
        $visit->is_signed = true;
        $visit->id_sign = $visitSign->id;

        if (!$visit->save(false, ['time_signed', 'is_signed', 'id_sign'])) {
            $this->errorResponse($visit, 'Ошибка при обновлении статуса приема');
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * @param int $id id приема
     * @return array    закодированные в base64 данные приема
     *
     * @throws ForbiddenHttpExceptionAlias
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws BadRequestHttpException
     */
    public
    function actionGenerateXml($id)
    {
        $visit = $this->findVisit($id);

        if ($visit->status != VisitStatus::FINISHED || $visit->status != VisitStatus::FINISHED_UNPAYED) {
            throw new BadRequestHttpException("Прием не завершен");
        }

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        if (!$visit) {
            throw new NotFoundHttpException("Не удалось найти карточку приема", 422);
        }


        //кодируем xml файл, вызываем ЭЦП сервис для валидации подписи
        /** @var \app\common\components\xmlGenerator\XmlGenerator $generator */
        $generator = \Yii::$app->get('xmlGenerator');

        $xml = $generator->generate($id);
        $base64 = base64_encode($xml);

        return ['document' => $base64];
    }

    /**
     * @param int $id
     * @param bool $is_veteran
     * @param bool $is_disabled
     * @param bool $is_blind
     * @param bool $is_orphan
     * @param bool $is_large_family
     * @param bool $is_veteran_of_labour
     * @param string $preferences_document
     * @return array
     */
    public
    function actionEditPreferences(
        int    $id,
        bool   $is_veteran,
        bool   $is_disabled,
        bool   $is_blind,
        bool   $is_orphan,
        bool   $is_large_family,
        bool   $is_veteran_of_labour,
        string $preferences_document
    )
    {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitSaveModel([
            'scenario' => VisitSaveModel::SCENARIO_UPDATE_PREFERENCES,
            'visit' => $visit,
            'type' => $visit->type,
        ]);
        $model->load($this->actionParams, '');

        if (!$model->updateVisitPreferences()) {
            $this->errorResponse($model);
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * Подтверждение оплаты администратором (кнопка "подтвердить оплату")
     *
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpExceptionAlias
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public
    function actionApprovePayment(int $id): array
    {

        $visit = $this->findVisit($id);
        $model = new VisitChangeStatusModel(['visit' => $visit]);
        if (!$model->approvePaymentVisit()) {
            $this->errorResponse($model, 'Ошибка при изменении статуса приема');
        }
        $title = "'Оплата'";
        $deleteNotification = \Yii::$app->db->createCommand('UPDATE public.notifications SET read= TRUE WHERE id_visit=' . $id . ' AND title=' . $title .'');

        try {
            $deleteNotification->execute();
        } catch (\Exception $e) {
            file_put_contents('debug.txt', print_r($e, true), FILE_APPEND);
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }
}
