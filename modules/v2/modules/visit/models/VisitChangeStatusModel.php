<?php

namespace app\modules\v2\modules\visit\models;

use app\common\components\inform\events\AppointmentResultsEvent;
use app\common\components\inform\events\CancelVisitByTechReasonEvent;
use app\common\components\inform\events\CancelVisitEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\SubscriptionService;
use app\common\models\VisitStatus;
use app\models\db\Diseases;
use app\models\db\GovServices;
use app\models\db\Params;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\ServiceTypes;
use app\models\db\Species;
use app\models\db\tmc\TmcBase;
use app\models\db\Violation;
use app\models\db\ViolationType;
use app\models\db\VisitDescriptions;
use app\models\db\VisitParamValues;
use app\models\db\VisitPets;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitsGovServices;
use app\models\db\VisitsSpecialists;
use app\models\MosruNotification;
use app\modules\soap\models\etp\response\service;
use app\modules\soap\v2\queue\MosruStatusSender;
use app\modules\v2\modules\pets\models\PetsModel;
use app\modules\v2\modules\pets\models\RegCertificateModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class VisitChangeStatusModel
 *
 * @package app\modules\v2\modules\visit\models
 * @see     https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
 */
class VisitChangeStatusModel extends Model
{
    use ParamsTrait, VisitTrait;

    /**
     * @var \app\models\db\Visits
     */
    public $visit;

    /**
     * @var string
     */
    private $oldStatus;

    /**
     * @var string
     */
    private $newStatus;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->visit)) {
            throw new InvalidConfigException();
        }

        $this->oldStatus = $this->visit->status;
    }

    /**
     * CODы услуг при которых снимаем животное с учета
     *
     */
    const EVTANAZIA_ID = ['0350', '0338', '0339', '0340', '0380',
        '0341', '0342', '0343', '0344', '0345', '0346',
        '0347', '1005', '1006', '1007', '1008', '1009',
        '1010', '1011', '1012', '1013', '1014', '1015',
        '0408', '0409', '0410', '0411', '0412', '0413',
        '0414', '0415', '0416', '0417', '0418', '0379',
    ];

    /**
     * Взятие приема в работу
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function startVisit()
    {
        $this->newStatus = VisitStatus::IN_WORK;

        if ($this->statusNotChanged()) {
            return true;
        }

        $dateStart = date('Y-m-d H:i:s');

        // проверяем наличие экстренной ситуации для приемов, не относящихся к вызовам на дом и вызовам НВП
        if (!$this->isCallToHome() && !$this->isAmbulanceVisit() && $this->checkVisitForEmergency(
                $this->visit->id_organization,
                $dateStart
            )) {
            return false;
        }

        $attributes = [
            'status' => $this->newStatus,
            'fact_start_dttm' => $dateStart,
        ];

        $result = $this->changeStatus($attributes);

        if ($result === true) {
            $this->updateVisitSpecialist();
            if ($this->visit->isMosRu()) {
                //MosruNotification::visitStart($this->visit->id);
                $this->getSendStatusService()->visitStart($this->visit->id, $this->visit->isOnlineVisit());
            }

            $this->savePetRegistration();
            $this->updateVisitParamValues();

            if ($this->oldStatus == VisitStatus::NEW || $this->oldStatus == VisitStatus::CHANGED) {
                // при взятии приема в работу сохраняем параметры приема (visit_params)
                $this->saveVisitParamsModel();

                // При взятии приема в работу автоматически формируем регистрационное удостоверение
                // https://jira.altarix.ru/browse/VETAIS-3263
                $this->createRegCertificate();
            }
        }

        return $result;
    }

    /**
     * @throws \yii\db\Exception
     */
    private function saveVisitParamsModel()
    {
        //TODO: Пока закоментровано. Метод надо пенести в место открытия формы формирования отчета...
        //VisitParamsModel::saveVisitParams($this->visit);
    }

    /**
     * Завершение приема
     *
     * @return bool
     */

    public function finishVisit($services = false)
    {

        //services - true  если среди услуг ЭВТАНАЗИЯ
        $this->newStatus = VisitStatus::FINISHED;

        if ($this->statusNotChanged()) {
            return true;
        }

        // проверяем наличие в приеме услуг
        // @see https://jira.altarix.ru/browse/VETAIS-1418
        if (empty($this->visit->services)) {
            $this->addError('services', 'Для завершения приема необходима хотя бы одна услуга.');

            return false;
        }

        // проверяем, авторизован ли пользователь
        // @see https://jira.altarix.ru/browse/VETAIS-2628
        if ($this->visit->type != Visits::TYPE_VISIT_VC_SHELTER) {
            if ($this->checkInkognito()) {
                $this->addError('status', "Завершение приема запрещено!\nНеобходимо указать фактические ФИО владельца");

                return false;
            }
        }

        // проверяем, оплачен ли прием
        // @see https://jira.altarix.ru/browse/VETAIS-2945
//        if ($this->visit->is_paid === false) {
//            $this->addError('status', "Подтвердите оплату приёма для завершения.");
//
//            return false;
//        }
        // vaccin_srvc = true при вакцинации нескольких животных и выводков исключить проверку на вакцинацию
        $vaccin_srvc = false;
        if ($this->visit['variety'] === 'SINGLE' || $this->visit['variety'] === 'MULTIPLE' || $this->visit['variety'] === 'BROOD') {
            $srvc = $this->visit->services;
            foreach ($srvc as $item) {
                if (strpos($item['name'], 'Вакцинация') !== false) {
                    $vaccin_srvc = true;
                }
            }
        }

        //  проверка на наличие услуг Эвтаназии и Приема трупов
        //  возвращает массив
        $evatanaziaAndUtilizacia = $this->checkEvtanazia();

        if (count($evatanaziaAndUtilizacia) == 0){
            // проверяем заполнение описаний (вкладка "Данные приема")
            if (!$this->checkVisitDescriptions()) {
                return false;
            }

            // при завершении приема проверяем заполнение обязательных исходящих параметров
            // @see https://jira.altarix.ru/browse/VETAIS-1144
            if (!$this->checkReqOutParams()) {
                return false;
            }
        }

        // Пропускаем проверку для временных питомцев
        if (!$this->visit->is_for_unauth_client) {

            // Проверка на наличие нарушений по вакцинации у обследуемых животных
            // Только для собак и кошек
            // Доработки по госветнадзору VETAIS-3266
            // https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=136937762#id-%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%D0%BA%D1%80%D0%B0%D0%B7%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D0%B5%22%D0%A3%D0%B2%D0%B5%D0%B4%D0%BE%D0%BC%D0%BB%D0%B5%D0%BD%D0%B8%D1%8F%D0%B8%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%B0%D1%81%D0%BD%D0%B0%D1%80%D1%83%D1%88%D0%B5%D0%BD%D0%B8%D1%8F%D0%BC%D0%B8%D0%B8%D0%BD%D1%81%D0%BF%D0%B5%D0%BA%D1%82%D0%BE%D1%80%D0%BE%D0%B2%D0%93%D0%BE%D1%81%D0%B2%D0%B5%D1%82%D0%BD%D0%B0%D0%B4%D0%B7%D0%BE%D1%80%D0%B0%22-1.1.3.%D0%98%D0%B7%D0%BC%D0%B5%D0%BD%D0%B5%D0%BD%D0%B8%D1%8F%D0%B4%D0%BB%D1%8F%D1%80%D0%BE%D0%BB%D0%B8%D0%B2%D0%B5%D1%82%D1%81%D0%BF%D0%B5%D1%86%D0%B8%D0%B0%D0%BB%D0%B8%D1%81%D1%82%D0%B0

            // vaccin_srvc - true при вакцинации нескольких животных пропускаем проверку на вакцинацию

            if (!$vaccin_srvc && count($evatanaziaAndUtilizacia) == 0) {
                $petsWithViolation = $this->hasRabiesViolation($this->visit->pets);
                if (!empty($petsWithViolation)) {
                    $msg = implode(', ', array_map(function ($pet) {
                        return $pet->id . " " . $pet->name;
                    }, $petsWithViolation));
                    $this->addError('status', "Внимание! У животного/ых " . $msg . " отсутствует вакцинация");
                    return false;
                }
            }
        }

        $attributes = [
            'status' => $this->newStatus,
            'fact_end_dttm' => date('Y-m-d H:i:s'),
        ];

        $result = $this->changeStatus($attributes);

        //Сохраняем адрес на момент завершения приема.
        //https://jira.altarix.ru/browse/VETAIS-3008
        if ($result) {

            if ($this->visit->isMosRu()) {
                $this->getSendStatusService()->visitFinish($this->visit->id, $this->visit->isOnlineVisit());
            }

            $this->saveAddresses();

            // Удаляем данные по обследованию, в случае, если были удалены все релевантные для таких данных услуги
            // https://jira.altarix.ru/browse/VETAIS-3354
//            $this->manageServiceDescriptions();

            if ($this->visit->is_for_unauth_client) {
                $this->migrateTmpData();
            }
        }

        if ($this->visit->isOnlineVisit()) {
            $contacts = SubscriptionService::getOwnerSubscriptions($this->visit->owner);
            if (!empty($contacts)) {
                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new AppointmentResultsEvent([
                    'visit' => $this->visit,
                    'contacts' => $contacts,
                ]));
            }
        }

        // если эвтаназия - то сразу снимаем с учета Животное
        $this->setExpireReason($evatanaziaAndUtilizacia);
        //

        return $result;
    }

    /**
     * Завершение приема без оплаты
     *
     * @return bool
     */

    public
    function finishUnpayedVisit($services)
    {
        //services - true  если среди услуг ЭВТАНАЗИЯ
        $this->newStatus = VisitStatus::FINISHED_UNPAYED;

        if ($this->statusNotChanged()) {
            return true;
        }

        // проверяем наличие в приеме услуг
        // @see https://jira.altarix.ru/browse/VETAIS-1418
        if (empty($this->visit->services)) {
            $this->addError('services', 'Для завершения приема необходима хотя бы одна услуга.');

            return false;
        }

        // проверяем, авторизован ли пользователь
        // @see https://jira.altarix.ru/browse/VETAIS-2628
        if ($this->visit->type != Visits::TYPE_VISIT_VC_SHELTER) {
            if ($this->checkInkognito()) {
                $this->addError('status', "Завершение приема запрещено!\nНеобходимо указать фактические ФИО владельца");

                return false;
            }
        }

        // vaccin_srvc = true при вакцинации нескольких животных и выводков исключить проверку на вакцинацию
        $vaccin_srvc = false;
        if ($this->visit['variety'] === 'SINGLE' || $this->visit['variety'] === 'MULTIPLE' || $this->visit['variety'] === 'BROOD') {
            $srvc = $this->visit->services;
            foreach ($srvc as $item) {
                if (strpos($item['name'], 'Вакцинация') !== false) {
                    $vaccin_srvc = true;
                }
            }
        }

        //  проверка на наличие услуг Эвтаназии и Приема трупов
        // возвращает массив
        $evatanaziaAndUtilizacia = $this->checkEvtanazia();

        if (count($evatanaziaAndUtilizacia) == 0){
            // проверяем заполнение описаний (вкладка "Данные приема")
            if (!$this->checkVisitDescriptions()) {
                return false;
            }

            // при завершении приема проверяем заполнение обязательных исходящих параметров
            // @see https://jira.altarix.ru/browse/VETAIS-1144
            if (!$this->checkReqOutParams()) {
                return false;
            }
        }

        // Пропускаем проверку для временных питомцев
        if (!$this->visit->is_for_unauth_client) {

            // Проверка на наличие нарушений по вакцинации у обследуемых животных
            // Только для собак и кошек
            // Доработки по госветнадзору VETAIS-3266
            // https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=136937762#id-%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%D0%BA%D1%80%D0%B0%D0%B7%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D0%B5%22%D0%A3%D0%B2%D0%B5%D0%B4%D0%BE%D0%BC%D0%BB%D0%B5%D0%BD%D0%B8%D1%8F%D0%B8%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%B0%D1%81%D0%BD%D0%B0%D1%80%D1%83%D1%88%D0%B5%D0%BD%D0%B8%D1%8F%D0%BC%D0%B8%D0%B8%D0%BD%D1%81%D0%BF%D0%B5%D0%BA%D1%82%D0%BE%D1%80%D0%BE%D0%B2%D0%93%D0%BE%D1%81%D0%B2%D0%B5%D1%82%D0%BD%D0%B0%D0%B4%D0%B7%D0%BE%D1%80%D0%B0%22-1.1.3.%D0%98%D0%B7%D0%BC%D0%B5%D0%BD%D0%B5%D0%BD%D0%B8%D1%8F%D0%B4%D0%BB%D1%8F%D1%80%D0%BE%D0%BB%D0%B8%D0%B2%D0%B5%D1%82%D1%81%D0%BF%D0%B5%D1%86%D0%B8%D0%B0%D0%BB%D0%B8%D1%81%D1%82%D0%B0

            // vaccin_srvc - true при вакцинации нескольких животных пропускаем проверку на вакцинацию

            if (!$vaccin_srvc && count($evatanaziaAndUtilizacia) == 0) {
                $petsWithViolation = $this->hasRabiesViolation($this->visit->pets);
                if (!empty($petsWithViolation)) {
                    $msg = implode(', ', array_map(function ($pet) {
                        return $pet->id . " " . $pet->name;
                    }, $petsWithViolation));
                    $this->addError('status', "Внимание! У животного/ых " . $msg . " отсутствует вакцинация");
                    return false;
                }
            }
        }

        $attributes = [
            'status' => $this->newStatus,
            'fact_end_dttm' => date('Y-m-d H:i:s'),
        ];

        $result = $this->changeStatus($attributes);

        //Сохраняем адрес на момент завершения приема.
        //https://jira.altarix.ru/browse/VETAIS-3008
        if ($result) {
            if ($this->visit->isMosRu()) {
                $this->getSendStatusService()->visitFinish($this->visit->id, $this->visit->isOnlineVisit());
            }

            $this->saveAddresses();

            // Удаляем данные по обследованию, в случае, если были удалены все релевантные для таких данных услуги
            // https://jira.altarix.ru/browse/VETAIS-3354
//            $this->manageServiceDescriptions();

            if ($this->visit->is_for_unauth_client) {
                $this->migrateTmpData();
            }
        }


        if ($this->visit->isOnlineVisit()) {
            $contacts = SubscriptionService::getOwnerSubscriptions($this->visit->owner);
            if (!empty($contacts)) {
                \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new AppointmentResultsEvent([
                    'visit' => $this->visit,
                    'contacts' => $contacts,
                ]));
            }
        }

        // если эвтаназия или утилизация трупов - то сразу снимаем с учета Животное
        $this->setExpireReason($evatanaziaAndUtilizacia);
        //

        return $result;
    }

    /**
     * Удаляет временные данные владельца и питомца для приёмов
     * неавторизованных пользователей mos.ru
     *
     * https://azuredevops.starlink-soft.ru/Starlink/VetAs/_workitems/edit/188
     */
    private
    function migrateTmpData()
    {
        $owner = $this->visit->owner;
        $tmpOwner = null;
        if ($tmpOwner = $owner->tmpOwner) {
            $ownerAttrs = [
                'f_fio',
                'i_fio',
                'o_fio',
                'fullname',
                'birthday',
                'snils',
            ];
            $owner->setAttributes($tmpOwner->getAttributes($ownerAttrs));
            $owner->id_pet_owner_tmp = null;
        }

        /** @var Pets $pet */
        $pet = $this->visit->pets[0];
        $tmpPet = null;
        if ($tmpPet = $pet->tmpPet) {
            $petAttrs = [
                'birthday',
                'name',
                'sex',
                'id_species',
                'id_breed',
            ];
            $pet->setAttributes($tmpPet->getAttributes($petAttrs));
            $pet->id_pet_tmp = null;
        }

        if ($tmpOwner || $tmpPet) {
            try {
                \Yii::$app->db->transaction(function () use ($owner, $pet, $tmpOwner, $tmpPet) {
                    if ($tmpOwner) {
                        $owner->save(false);
                        $tmpOwner->delete();
                    }
                    if ($tmpPet) {
                        $pet->save(false);
                        $tmpPet->delete();
                    }
                });
            } catch (\Throwable $err) {
                \Yii::error('Error while migrating Owner and Pet tmp data: ' . $err);
            }
        }
    }

    /**
     * Отмена приема
     *
     * @param string $cancel_initiator
     * @param string $change_reason
     * @return bool
     * @throws \Exception
     */
    public
    function cancelVisit(string $cancel_initiator, string $change_reason = null)
    {
        $this->newStatus = VisitStatus::CANCELED;

        if (
            $this->statusNotChanged()
            && $this->visit->cancel_initiator == $cancel_initiator
            && $this->visit->change_reason == $change_reason
        ) {
            return true;
        }

        // Телевет можно отменить с возвратом средств
        if (!$this->visit->isOnlineVisit() && $this->visit->is_paid === true) {
            $this->addError('status', 'Невозможно отменить оплаченный прием');

            return false;
        }

        // VETAIS-1859
        if ($this->hasBalanceTmc()) {
            $this->addError(
                'status',
                'Невозможно отменить прием, в котором списаны ТМЦ. Удалите ТМЦ, если необходимо отменить прием'
            );

            return false;
        }

        $attributes = [
            'status' => $this->newStatus,
            'cancel_initiator' => $cancel_initiator,
            'change_reason' => $change_reason,
        ];

        $result = $this->changeStatus($attributes);
        $oldStatesToNotify = [VisitStatus::NEW, VisitStatus::CHANGED, VisitStatus::IN_WORK];

        if ($result === true && in_array($this->oldStatus, $oldStatesToNotify)) {
            if ($this->visit->isMosRu()) {
                if ($this->visit->isOnlineVisit() && $change_reason == Visits::CANCEL_BY_TECH_REASON) {
                    $this->getSendStatusService()->visitCancelByTechReason($this->visit->id);
                } else $this->getSendStatusService()->visitCancelInClinic($this->visit->id, $this->visit->isOnlineVisit());
            }

            if ($cancel_initiator == Visits::INITIATOR_IS_CLINIC && $this->visit->canNotify()) {
                if ($contacts = SubscriptionService::getOwnerSubscriptions($this->visit->owner)) {
                    if ($this->visit->isOnlineVisit() && $change_reason == Visits::CANCEL_BY_TECH_REASON) {
                        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new CancelVisitByTechReasonEvent([
                            'visit' => $this->visit,
                            'contacts' => $contacts,
                            'id_visit' => $this->visit->id,
                        ]));
                    } else {
                        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new CancelVisitEvent([
                            'visit' => $this->visit,
                            'contacts' => $contacts,
                            'id_visit' => $this->visit->id,
                        ]));
                    }
                }
            }
        }

        //Сохраняем адрес на момент отмены приема.
        //https://jira.altarix.ru/browse/VETAIS-3008
        if ($result) {
            $this->saveAddresses();
        }

        return $result;
    }

    /**
     * У визита есть балансовые ТМЦ
     *
     * @return bool
     */
    private
    function hasBalanceTmc(): bool
    {
        return VisitServiceTmc::find()
            ->where([
                'AND',
                ['id_visit' => $this->visit->id],
                ['IS NOT', 'id_balance_tmc', null],
                [
                    'IN',
                    'type_tmc',
                    [
                        TmcBase::TYPE_VACCINE,
                        TmcBase::TYPE_EQUIPMENT,
                        TmcBase::TYPE_EXP_MATERIAL,
                        TmcBase::TYPE_DRUG
                    ]
                ]
            ])->exists();
    }

    /**
     * На случай повторной отправки данных с фронта.
     * Если статус не изменился, не будем повторно сохранять прием, но и не будем возвращать ошибку.
     *
     * @return bool
     */
    private
    function statusNotChanged(): bool
    {
        return $this->newStatus === $this->oldStatus;
    }

    /**
     * @param array $attributes
     * @return bool
     */
    private
    function changeStatus(array $attributes): bool
    {
        $result = $this->visit->load($attributes, '')
            ? $this->visit->save(true, array_merge(array_keys($attributes), ['updated_at', 'updated_by']))
            : false;

        if ($result === false) {
            $this->addErrors($this->visit->getErrors());
        }

        return $result;
    }

    /**
     * После взятия приема в работу устанавливаем для животного организацию и дату регистрации
     */
    private
    function savePetRegistration()
    {
        if (!empty($this->visit->pets) && !empty($this->visit->id_organization)) {
            foreach ($this->visit->pets as $pet) {
                if ($pet->reg_date === null && $pet->id_reg_organization === null) {
                    $pet->reg_date = new Expression('NOW()');
                    $pet->id_reg_organization = $this->visit->id_organization;
                    $attributes = ['reg_date', 'id_reg_organization', 'updated_at'];
                    if (empty($pet->id_created_organization)) {
                        // попробуем заполнить id_created_organization если животное создано тем же юзером
                        /** @var $user \app\common\models\UserModel */
                        $user = \Yii::$app->user->getIdentity();
                        if ($pet->created_by == $user->id && $user->specialist !== null) {
                            $pet->id_created_organization = $user->specialist->id_organization;
                            $attributes[] = 'id_created_organization';
                        }
                    }
                    $pet->save(true, $attributes);
                }
            }
        }
    }

    /**
     * Обновляет некоторые параметры отчетов по услугах
     */
    private
    function updateVisitParamValues()
    {
        if ($this->visit->fact_start_dttm) {
            $start_dttm = date_create_from_format('Y-m-d H:i:s', $this->visit->fact_start_dttm);
            $idParam = Params::find()->select('id')->where(['tech_name' => 'P3_Visitstartdate'])->scalar();
            if ($start_dttm !== false && $idParam) {
                VisitParamValues::updateAll([
                    'date_value' => $start_dttm->getTimestamp(),
                ], [
                    'id_visit' => $this->visit->getPrimaryKey(),
                    'id_param' => $idParam, //Дата
                ]);
            }
        }
    }

    /**
     * @return bool
     */
    private
    function checkReqOutParams()
    {
        $visitServices = (new Query())
            ->from(VisitsGovServices::tableName())
            ->where(['id_visit' => $this->visit->id])
            ->orderBy(['id_service' => SORT_ASC])
            ->all();

        if (empty($visitServices)) {
            return true;
        }

        $visitServiceIds = ArrayHelper::getColumn($visitServices, 'id');

        $paramValues = (new Query())
            ->from(VisitServiceParamValues::tableName())
            ->where(['in', 'id_visitservice', $visitServiceIds])
            ->all();

        $grouped = ArrayHelper::index($paramValues, null, 'id_visitservice');

        /**
         * Rumenko 18:45
         * Сергей, обсудили с Машей. Вообще пришли к следующему:
         * Необходимо формировать ошибку (общую).
         * Необходимо заполнить обязательные параметры услуг: параметр 1; параметр 2; ….; n
         */
        $errors = [];

        foreach ($visitServices as $visitService) {
            $fields = $this->findReportParamsForService($visitService['id_service']);
            if (empty($fields)) {
                continue;
            }
            $result = $this->validateNotPassedRequiredParams(
                ArrayHelper::getValue($grouped, $visitService['id'], []),
                $fields,
                $visitService['id'],
                false,
                true
            );
            if (!empty($result)) {
                $errors = array_merge($errors, $result);
            }
        }

        if (!empty($errors)) {
            $errors = array_unique($errors);
            $this->addError(
                'services',
                'Необходимо заполнить обязательные параметры услуг: ' . "\n" . implode("\n", $errors)
            );

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private
    function checkVisitDescriptions()
    {
        $descriptions = $this->visit->getVisitDescriptions()
            ->asArray()
            ->all();

        $model = new VisitDescriptionsModel([
            'scenario' => VisitDescriptionsModel::SCENARIO_VALIDATE_WHEN_FINISH_VISIT,
            'visit' => $this->visit,
            'descriptions' => $descriptions,
        ]);

        if (!$model->validate(['descriptions'])) {
            $this->addErrors($model->getErrors());

            return false;
        }

        return true;
    }

    /**
     * @throws \Throwable
     */
    private
    function updateVisitSpecialist()
    {
        /* @var $currentUser \app\common\models\UserModel */
        $currentUser = \Yii::$app->user->getIdentity();
        if ($currentUser->specialist === null) {
            return;
        }

        /* @var $visitSpecialists VisitsSpecialists[] */
        $visitSpecialists = $this->visit->getVisitsSpecialists()
            ->all();
        // так и осталось hasMany - поэтому берем первого
        if (!empty($visitSpecialists)) {
            $visitSpecialist = reset($visitSpecialists);
            if ($visitSpecialist->id_specialist == $currentUser->specialist->id) {
                return;
            }
            $visitSpecialist->id_specialist = $currentUser->specialist->id;
        } else {
            $visitSpecialist = new VisitsSpecialists([
                'id_visit' => $this->visit->id,
                'id_specialist' => $currentUser->specialist->id,
            ]);
        }

        $visitSpecialist->save();

        // меняем id_organization приема, если она не совпадает с организацией специалиста
        if ($this->visit->id_organization != $currentUser->specialist->id_organization) {
            $this->visit->id_organization = $currentUser->specialist->id_organization;
            $this->visit->save(true, ['id_organization', 'number']);
        }

        $this->visit->refresh();
    }

    /**
     * Создание регистрационного удостоверения
     */
    private
    function createRegCertificate()
    {
        $pets = $this->visit->getPets()->all();
        if (!empty($pets)) {
            foreach ($pets as $pet) {
                try {
                    $reg_model = new RegCertificateModel(['pet' => $pet]);
                    $reg_model->createCertificate(true);
                } catch (\Throwable $e) {
                    \Yii::error($e->getMessage());
                }
            }
        }
    }

    /**
     * @return bool
     */
    public
    function checkInkognito()
    {
        $petOwner = $this->visit->owner;
        $correctedFullname = preg_replace('/[^a-zа-яё]/iu', '', $petOwner->fullname);
        if (empty($correctedFullname)) {
            return true;
        }

        foreach (['f_fio', 'i_fio', 'o_fio'] as $field) {
            $filtered = preg_replace('/[^а-яё]/iu', '', $petOwner->$field);
            if (mb_stripos($filtered, 'нкогни') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает массив животных у которых обнаружено нарушение по вакцинации от бешенства и нет записи на исправление нарушения
     *
     * @param Pets[] $pets
     * @return Pets[] $petsWithViolations
     */
    private
    function hasRabiesViolation(array $pets)
    {
        $petsWithViolations = [];
        foreach ($pets as $pet) {
            $techName = $pet->species->tech_name;
            if ($techName === Species::TECH_NAME_DOG || $techName === Species::TECH_NAME_CAT) { // проверяем кошек и собак
                $vaccinationCount = PetRabiesVaccination::find()
                    ->where("id_pet = $pet->id")
                    ->andWhere('valid_until >= NOW()')
                    ->count();

                $vaccinationDublicatesCount = 0;
                if ($pet->is_main === true && $pet->duplicates) {
                    $idsDuplicates = $pet->getDuplicates()->select('id')->column();
                    $vaccinationDublicatesCount = PetRabiesVaccination::find()
                        ->where(['id_pet' => $idsDuplicates])
                        ->andWhere('valid_until >= NOW()')
                        ->count();
                }

                $birthday = new \DateTime($pet->birthday);
                $now = new \DateTime();
                $isThreeMonthsOlder = $birthday->diff($now)->days >= 93;

                // если у животного есть запланированная запись на вакцинацию,
                // то замечаний к вакцинации нет
                if ($this->hasVaccinationPlans($pet)
                ) {
                    return $petsWithViolations = [];
                }

                if (
                    ($vaccinationCount + $vaccinationDublicatesCount) === 0
                    && //НЕТ дейтвующей вакцинации по животному, в т.ч. по дубликату ("Действительно до (valid_until)" вакцины >= тек. даты
                    !$this->isVaccinationViolationFormed($pet)
                    &&   //И НЕ Сформировано нарушение по отсутствию вакцинаци от бешенства
                    !$this->hasVaccinationPlans($pet)
                    &&            //И НЕТ у животного запланированой запись на вакцинацию
                    (!$pet->birthday || $isThreeMonthsOlder)        //Животное старше 3 месяцев или не указана дата рождения
                ) {
                    $petsWithViolations[] = $pet;
                }
            }
        }

        return $petsWithViolations;
    }

    /**
     * Есть ли у животного запланированая запись на вакцинацию
     *
     * @param Pets $pet
     * @return bool
     */
    private
    function hasVaccinationPlans(Pets $pet)
    {
        // все id услуг с Вакцинацией
        $vaccineIds = GovServices::find()
            ->select('id')
            ->where(['ilike', 'name', 'вакцинация'])
            ->asArray()
            ->column();

        // все id приемов с Вакцинацией у питомца
        $visitsGovServicesId = VisitsGovServices::find()
            ->select('id_visit')
            ->where(['id_pet' => $pet->id])
            ->andWhere(['in', 'id_service', $vaccineIds])
            ->asArray()
            ->column();

        // приемы с Вакцинацией у питомца не взятый в работу
        // и с датой оказания позднее текущей
        $visitsWithVacinationInFuture = Visits::find()
            ->where(['in', 'id', $visitsGovServicesId])
            ->andWhere(['status' => 'N'])
            ->andWhere(['>=', 'start_dttm', date('Y-m-d H:i:s')])
            ->asArray()
            ->all();

        return (($pet->date_plan_rabies_vaccination
                && strtotime($pet->date_plan_rabies_vaccination) >= strtotime('today'))
            || count($visitsWithVacinationInFuture)>=1
        );
    }

    /**
     * Сформировано ли уже нарушение по отсутствию вакцинаци от бешенства
     *
     * @param Pets $pet
     * @return bool
     */
    private
    function isVaccinationViolationFormed(Pets $pet)
    {
        /** @var Diseases $rabies */
        $rabies = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one();

        $violationCount = Violation::find()
            ->leftJoin('violation_type as vt', 'violation.id_type = vt.id_type')
            ->where([
                'id_pet' => $pet->id,
                'id_disease' => $rabies->id,
                'vt.type' => ViolationType::TYPE_VACCINATION_VIOLATION
            ])
            ->andWhere(['not in', 'state', [Violation::STATE_FINISHED, Violation::STATE_CANCELED]])
            ->count();

        return $violationCount > 0;
    }

    /**
     * Сохранение адреса содержания на момент завершения/отмены приема.
     *
     * @see https://jira.altarix.ru/browse/VETAIS-3008
     */
    private
    function saveAddresses()
    {
        /** @var VisitPets[] $visit_pets */
        $visit_pets = VisitPets::find()->where(['id_visit' => $this->visit->id])->all();
        try {
            foreach ($visit_pets as $visit_pet) {
                /** @var Pets $pet */
                $pet = Pets::find()->where(['id' => $visit_pet->id_pet])->one();
                $visit_pet->id_fias_address = $pet->id_fias_address;
                $visit_pet->update();
            }
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
        }
    }

    /**
     * Удаляет данные по обследованию, если были удалены все соответствующие этим данным услуги у животного
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private
    function manageServiceDescriptions()
    {
        foreach ($this->visit->pets as $pet) {
            $descToKeepSubQuery = VisitDescriptions::find()
                ->select('visit_descriptions.id as id')
                ->leftJoin('visits_gov_services vgs', "vgs.id_visit = visit_descriptions.id_visit and vgs.id_pet = $pet->id")
                ->leftJoin('services_description_types sdt', 'sdt.id_service = vgs.id_service')
                ->where([
                    'AND',
                    ['visit_descriptions.id_visit' => $this->visit->id],
                    ['visit_descriptions.id_pet' => $pet->id],
                    new Expression('visit_descriptions.id_description_type = sdt.id_description_type'),
                ]);

            $descriptions = VisitDescriptions::find()
                ->from('visit_descriptions vd')
                ->where([
                    'AND',
                    ['vd.id_visit' => $this->visit->id],
                    ['vd.id_pet' => $pet->id],
                    ['NOT', ['vd.id' => $descToKeepSubQuery]]
                ])->all();

            foreach ($descriptions as $description) {
                $description->delete();
            }
        }
    }

    /**
     * @return \app\modules\soap\v2\queue\MosruStatusSender
     */
    private
    function getSendStatusService()
    {
        return new MosruStatusSender();
    }

    /**
     * Перевод приема в статус Оплата подтверждена/Завершен
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public
    function approvePaymentVisit()
    {
        $this->newStatus = VisitStatus::FINISHED;
        if ($this->statusNotChanged()) {
            return true;
        }
        $attributes = [
            'status' => $this->newStatus,
        ];
        $result = $this->changeStatus($attributes);
        return $result;
    }

    /**
     * Прверка приема на наличие услуги -Эвтаназия/Прием трупов.
     *
     */
    private
    function checkEvtanazia()
    {
        $evtanaziaIds = [];
        foreach ($this->visit->services as $item) {
            $evtanazia = "эвтаназия";
            $utilizacia = "трупов";
            $burn = 'биологических отходов';
            if (
                (strpos($item['name'], $evtanazia) !== false) ||
                (strpos($item['name'], $utilizacia) !== false) ||
                (strpos($item['name'], $burn) !== false) ||
                ($item['cod'] && in_array("'" . $item['cod'] . "'", self::EVTANAZIA_ID)) ||
                ($item['cod'] && in_array($item['cod'], self::EVTANAZIA_ID))
            ) {
                $evtanaziaIds[] = $item['id'];
            }
        }

        return $evtanaziaIds;
    }

    /**
     * Снятие с учета животного если услуга -Эвтаназия/Прием трупов.
     *
     */
    private
    function setExpireReason($evtanaziaIds)
    {
        if (count($evtanaziaIds) > 0) {
            $visitsGovServices = VisitsGovServices::find()
                ->where(['id_visit' => $this->visit->id])
                ->asArray()
                ->all();
            $idPetsToEvtanazia = [];
            foreach ($visitsGovServices as $item) {
                if (in_array($item['id_service'], $evtanaziaIds)) {
                    $idPetsToEvtanazia[] = $item['id_pet'];
                }
            }
            $ids = implode(",", $idPetsToEvtanazia);
            $sql = "UPDATE pets SET id_reg_expire_reason = 8, reg_expire_date = NOW() WHERE id IN ($ids)";
            try {
                \Yii::$app->db->createCommand($sql)->execute();
            } catch (\Exception $e) {
                return false;
            }
        }
    }

}
