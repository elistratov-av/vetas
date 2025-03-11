<?php

namespace app\modules\v2\modules\visit\controllers;

use app\common\components\inform\events\RefundEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\models\VisitStatus;
use app\models\db\audit\AuditLog;
use app\models\db\PetOwners;
use app\models\db\RefundData;
use app\models\db\VisitPets;
use app\common\components\pdfGenerator\PdfGenerator;
use app\common\components\visitServiceReport\ServiceReportActions;
use app\models\db\Agreements;
use app\models\db\AgreementTypes;
use app\models\db\PetIdentification;
use app\models\db\Pets;
use app\models\db\Visits;
use app\models\db\VisitsGovServices;
use app\modules\admin\models\FiasAddress;
use app\modules\soap\v2\queue\MosruStatusSender;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\visit\models\VisitSaveModel;
use app\modules\v2\modules\visit\models\VisitModel;
use app\modules\v2\modules\visit\models\VisitServiceModel;
use app\modules\v2\modules\visit\skeletons\visit\Lists;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Работа с визитами
 * https://jira.altarix.ru/browse/VETAIS-855
 *
 * Class VisitController
 * @package app\modules\v2\modules\visit\controllers
 *
 * @method actionGet(int $id_visit): array
 * @method actionReferrals(int $id_visit): array
 * @method actionStart(int $id): array
 * @method actionFinish(int $id): array
 * @method actionFinishUnpayed(int $id): array
 * @method actionCancel(int $id, string $cancel_initiator, string $change_reason = null): array
 * @method actionConfirmPayment(int $id, int $id_discount = null, $applied_discounts_balance_tmc = null, $applied_discounts_services = null): array
 */
class VisitController extends GenericVisitController
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
                        'provide-refund-data-temp',
                    ],
                ],
            ]
        );
    }

    /**
     * Получение списка визитов
     * https://jira.altarix.ru/browse/VETAIS-855
     *
     * @param int $id_organization
     * @param string $date_from
     * @param array|int|null $id_shift_type
     * @param int $days_count
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return Lists
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(
        int    $id_organization,
        string $date_from,
        $id_shift_type = null,
        int    $days_count = 1,
        int    $page = 1,
        int    $limit = 10,
        array  $filter = []
    ): Lists {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $visitModel = new VisitModel();

        return $visitModel->list($id_organization, $id_shift_type, $date_from, $days_count, $page, $limit, $filter);
    }

    /**
     * Получение всего списка визитов для выгрузки в Xls
     *
     * @param int $id_organization
     * @param string $date_from
     * @param array|int|null $id_shift_type
     * @param int $days_count
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return Lists
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionVisitsForXls(
        int    $id_organization,
        string $date_from,
        $id_shift_type = null,
        int    $days_count = 1,
        int    $page = 1,
        int    $limit = 1000000,
        array  $filter = []
    ): Lists {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $visitModel = new VisitModel();

        return $visitModel->list($id_organization, $id_shift_type, $date_from, $days_count, $page = 1, $limit, $filter);
    }

    /**
     * Возвращает рабочий день специалиста по слотам с указанием визитов,
     * что их занимают
     *
     * @see https://jira.altarix.ru/browse/VETAIS-905
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=96318510
     *
     * @param int $id_specialist
     * @param int $id_organization
     * @param string $date_from
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSpecialistworkday(
        int    $id_specialist,
        int    $id_organization,
        string $date_from,
        array  $service_type_ids = []
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $visitModel = new VisitModel();

        return [
            'result' => [
                'time_slots' => $visitModel->specialistWorkday($id_specialist, $id_organization, $date_from, $service_type_ids),
            ],
        ];
    }

    public function actionTomographyReport()
    {
        header('Access-Control-Allow-Origin: *');

        $data = json_decode(\Yii::$app->getRequest()->getRawBody(), true);
        $visit = $this->findVisit($data['idVisit']);

        $generator = \Yii::$app->get('pdfGenerator');

        $path = $generator->createTomographyReport($data);

        $file = \Yii::$app->basePath . '/web/upload/pdf/' . $path[1] . '.' . $path[2];

        \Yii::$app->response->getHeaders()->add('Content-Description', 'File Transfer')
            ->add('Content-Type', 'application/octet-stream')
            ->add('Content-Disposition', 'attachment; filename=' . basename($file))
            ->add('Content-Transfer-Encoding', 'binary')
            ->add('Expires', '0')
            ->add('Cache-Control', 'must-revalidate')
            ->add('Pragma', 'public');

        \Yii::$app->response->sendFile($file)->send();
    }

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
     * @param string|null $type
     * @param string|null $variety
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionCreate(
        int    $id_owner,
        $id_pet,
        int    $id_organization,
        int    $channel,
        string $start_dttm = null,
        int    $id_specialist = null,
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
        $description = null,
        $type = null,
        $variety = null
    ) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new VisitSaveModel([
            'scenario' => VisitSaveModel::SCENARIO_CREATE_VISIT,
            'type' => $type ?? Visits::TYPE_VISIT,
            'variety' => $variety ?? Visits::VISIT_SINGLE,
        ]);
        $model->load($this->actionParams, '');

        if (!$model->createVisit()) {
            $this->errorResponse($model);
        }
        $visit = Visits::findOne(['id' => $model->visit->id]);
        $agreement = Agreements::findOne([
            'id_pet_owner' => $model->visit->id_owner,
            'is_agree' => true,
            'id_type' => 1
        ]);
        if ($agreement !== null) {
            $visit->is_agreed_pers_data = true;
            $visit->save();
        }

        return [
            'result' => $this->findVisit($model->visit->id, true),
        ];
    }

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
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionEdit(
        int    $id,
        int    $id_owner,
        $id_pet,
        int    $id_organization,
        string $start_dttm = null,
        int    $id_specialist = null,
        array  $services = null,
        $visit_to_address = null,
        $description = null
    ) {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitSaveModel([
            'scenario' => VisitSaveModel::SCENARIO_UPDATE_VISIT,
            'visit' => $visit,
            'type' => $visit->type ?? Visits::TYPE_VISIT,
            'variety' => $visit->variety ?? Visits::VISIT_SINGLE,
        ]);
        $model->load($this->actionParams, '');

        if (!$model->updateVisit()) {
            $this->errorResponse($model);
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionNotify(int $id)
    {
        $visit = $this->findVisit($id);
        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        return [
            'result' => (new VisitModel())->notify($visit)
        ];
    }

    /**
     * @param int $id_visit
     * @param int $id_pet
     * @return array
     */
    public function actionRemovePet(int $id_visit, int $id_pet)
    {
        $visit = $this->findVisit($id_visit);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        if ($visit->status != VisitStatus::IN_WORK) {
            $this->errorResponse($visit, 'Недопустимый статус приема');
        }
        if ($visit->variety != Visits::VISIT_BROOD && $visit->variety != Visits::VISIT_MULTIPLE) {
            $this->errorResponse($visit, 'Недопустимая разновидность приема');
        }

        $visitPet = VisitPets::findOne([
            'id_pet' => $id_pet,
            'id_visit' => $id_visit,
        ]);
        if ($visitPet === null) {
            $this->errorResponse($visitPet, 'Животное отсутствует в приеме');
        }

        $count = VisitPets::find()
            ->where(['id_visit' => $id_visit])
            ->count();
        if ($count == 1) {
            $this->errorResponse($visit, 'Нельзя удалить последнее животное в приеме');
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            /* @var $visitServices VisitsGovServices[] */
            $visitServices = VisitsGovServices::find()
                ->where([
                    'id_pet' => $id_pet,
                    'id_visit' => $id_visit,
                ])
                ->all();
            foreach ($visitServices as $visitService) {
                $model = new VisitServiceModel([
                    'scenario' => VisitServiceModel::SCENARIO_DELETE,
                    'visit' => $visit,
                    'visitService' => $visitService
                ]);

                if (!$model->validate()) {
                    $this->errorResponse($model, 'Ошибка при удалении услуги');
                }

                if ($visitService->getBalanceFlow()->exists()) {
                    $this->errorResponse($visitService, 'Для удаления услуги сначала необходимо удалить связанные ТМЦ');
                }

                // Note: при удалении visits_gov_services каскадно удаляются visit_service_param_values (constraint в БД)
                if (!$visitService->delete()) {
                    $this->errorResponse($visitService, 'Ошибка при удалении услуги');
                }
            }
            /* @var $commonServices VisitsGovServices[] */
            $commonServices = VisitsGovServices::find()
                ->where([
                    'id_pet' => null,
                    'id_visit' => $id_visit,
                ])
                ->andWhere(['>', 'count', 1])
                ->all();
            foreach ($commonServices as $service) {
                $service->count = $service->count - 1;
                if (!$service->save()) {
                    $this->errorResponse($service, 'Ошибка при обновлении количества услуги');
                }
            }

            if (!$visitPet->delete()) {
                $this->errorResponse($visitPet, 'Ошибка при удалении животного');
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return [
            'result' => $this->findVisit($id_visit, true),
        ];
    }

    /**
     * @param int $id_owner
     * @param array $id_pet
     * @return array
     */
    public function actionUnpaidVisits($id_owner = null, $id_pet = null)
    {
        return (new VisitModel())->unpaidVisits($id_owner, $id_pet);
    }

    /**
     * @param int $id_owner
     * @param int|null $id_visit
     * @return array
     * @throws Exception
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionGeneratePersAgreement(int $id_owner, int $id_visit = null)
    {
        $agreement = Agreements::find()
            ->andWhere([
                'id_pet_owner' => $id_owner,
                'id_type' => 1,
            ])->one();

        if (!empty($agreement)) {
            $agreement->is_agree = true;
            if ($agreement->save()) {
                echo 'Данные успешно сохранены!';
            } else {
                echo 'Ошибка при сохранении данных: ', print_r($agreement->errors, true);
            }
        } else {
            $agreement = new Agreements();
            $agreement->is_agree = true;
            $agreement->id_type = 1;
            $agreement->id_pet_owner = $visit->id_owner ?? $id_owner;
            $agreement->id_visit = $visit->id ?? null;
            $agreement->id_pet = null;
            $agreement->id_organization = $visit->id_organization ?? null;
            if ($agreement->save()) {
                echo 'Данные успешно сохранены!';
            } else {
                echo 'Ошибка при сохранении данных: ', print_r($agreement->errors, true);
            }
        }

        return [
            'result' => $agreement
        ];
    }

    /**
     * @param int $id_visit
     * @param int $id_pet
     * @return array
     * @throws Exception
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionGenerateSurgAgreement(int $id_visit, int $id_pet)
    {
        $visit = $this->findVisit($id_visit);
        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);
        $existedAgreement = Agreements::find()
            ->andWhere([
                'id_visit' => $id_visit,
                'id_pet' => $id_pet,
                'id_type' => AgreementTypes::SURGERY,
                'is_agree' => true
            ]);
        if (!empty($existedAgreement->all())) {
            throw new Exception('Согласие уже создано');
        }
        $pets = $visit->pets;
        $petsIds = [];
        foreach ($pets as $pet) {
            $petsIds[] = $pet->id;
        }
        if (!in_array($id_pet, $petsIds)) {
            throw new Exception('Указанное животное отсутствует в рамках приема');
        }

        if ($visit->status == VisitStatus::IN_WORK || $visit->status == VisitStatus::NEW || $visit->status == VisitStatus::CHANGED) {
            $agreement = new Agreements();
            $agreement->is_agree = true;
            $agreement->id_type = AgreementTypes::SURGERY;
            $agreement->id_pet_owner = $visit->id_owner;
            $agreement->id_visit = $visit->id;
            $agreement->id_pet = $id_pet;
            $agreement->id_organization = $visit->id_organization;
            $visit->save();
            $agreement->save();
        } else {
            throw new Exception('Недопустимый статус приема');
        }

        return [
            'result' => $agreement
        ];
    }

    /**
     * @param int $id_owner
     * @return array
     * @throws Exception
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDeclinePersAgreement(int $id_owner)
    {
        $owner = PetOwners::findOne(['id' => $id_owner]);
        if (!$owner) {
            throw new Exception('Владелец не найден');
        }
        $agreement = Agreements::findOne([
            'id_pet_owner' => $id_owner,
            'id_type' => AgreementTypes::PD_PROCESSING,
            'is_agree' => true
        ]);
        if ($agreement === null) {
            throw new Exception('Согласие не найдено');
        }
        $this->checkAccess($this->action->getUniqueId(), $agreement, $this->actionParams);

        $agreement->is_agree = false;
        $agreement->save();
        //Часть кода лишняя так как согласие на обработку перс данных
        // не для одного визита, а для всех

        //        $visits = Visits::find()
        //            ->andWhere([
        //                'is_agreed_pers_data' => true,
        //                'id_owner' => $agreement->id_pet_owner
        //            ])
        //            ->all();
        //        foreach ($visits as $visit) {
        //            $visit->is_agreed_pers_data = false;
        //            $visit->agreement_rejected_at = $agreement->updated_at;
        //            $visit->save();
        //        }

        return [
            'result' => $agreement
        ];
    }

    /**
     * @param int $id_visit
     * @param int $id_pet
     * @return array
     * @throws Exception
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDeclineSurgAgreement(int $id_visit, int $id_pet)
    {
        $visit = $this->findVisit($id_visit);
        $agreement = Agreements::findOne([
            'id_visit' => $visit->id,
            'id_pet' => $id_pet,
            'id_type' => AgreementTypes::SURGERY,
            'is_agree' => true
        ]);
        if ($agreement === null) {
            throw new Exception('Согласие не найдено');
        }
        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        if ($visit->status == VisitStatus::IN_WORK || $visit->status == VisitStatus::NEW || $visit->status == VisitStatus::CHANGED) {
            $agreement->is_agree = false;
            $agreement->save();
        } else {
            throw new Exception('Недопустимый статус приема');
        }

        return [
            'result' => $agreement
        ];
    }

    public function actionRefreshAgreement(int $id_owner)
    {
        $owner = PetOwners::findOne(['id' => $id_owner]);
        if (!$owner) {
            throw new Exception('Владелец не найден');
        }
        $agreement = Agreements::findOne([
            'id_pet_owner' => $id_owner,
            'id_type' => AgreementTypes::PD_PROCESSING,
            'is_agree' => true
        ]);
        if ($agreement === null) {
            throw new Exception('Согласие не найдено');
        }
        $this->checkAccess($this->action->getUniqueId(), $agreement, $this->actionParams);

        $agreement->updated_at = date('Y-m-d H:i:s');
        $agreement->updated_by = \Yii::$app->user->id;;
        $agreement->save();

        return [
            'result' => $agreement
        ];
    }

    public function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    /**
     * Формирование pdf файла для печати согласия на обработку персональных данных
     *
     * @param int $id_owner
     * @param string $format
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionMakePersFile(int $id_owner, $format = PdfGenerator::FORMAT_A4)
    {
        $owner = PetOwners::findOne(['id' => $id_owner]);
        if (!$owner) {
            $this->errorResponse($owner, 'Владелец не найден');
        }
        $ownerReg = FiasAddress::findOne(['id' => $owner->id_fias_address])['full_address'];
        $ownerFact = FiasAddress::findOne(['id' => $owner->id_fact_fias_address])['full_address'];
        $agreement = Agreements::findOne([
            'id_pet_owner' => $id_owner,
            'id_type' => AgreementTypes::PD_PROCESSING,
            'is_agree' => true
        ]);
        $timestamp = $agreement->updated_at ?? date('Y-m-d H:i:s');
        $logAddr = AuditLog::find()
            ->alias('l')
            ->select([
                new Expression("l.snapshot->>'fact_fias_addresses' as fact"),
                new Expression("l.snapshot->>'fias_addresses' as reg")
            ])
            ->andWhere(new Expression('(l.snapshot->>\'id\')::int = :owner'), [':owner' => $id_owner])
            ->andWhere(['<', 'l.date', $timestamp])
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->one();
        $logCnt = AuditLog::find()
            ->alias('l')
            ->select([
                new Expression("l.snapshot->'contacts' as cnt"),
            ])
            ->andWhere(new Expression('(l.snapshot->>\'id\')::int = :owner'), [':owner' => $id_owner])
            ->andWhere(['<', 'l.date', $timestamp])
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->one();
        $json = json_decode($logCnt['cnt']);
        $phones = [];
        $mails = [];
        if (!empty($json)) {
            foreach ($json as $item) {
                $temp = ['id' => 0, 'name' => '', 'type' => '', 'main_flag' => false];
                if ($item->type == 'Электронная почта') {
                    $temp['id'] = $item->id;
                    $temp['name'] = $item->name;
                    $temp['type'] = $item->type;
                    $temp['main_flag'] = $item->main_flag;
                    $mails[] = $temp;
                } else {
                    $temp['id'] = $item->id;
                    $temp['name'] = $item->name;
                    $temp['type'] = $item->type;
                    $temp['main_flag'] = $item->main_flag;
                    $phones[] = $temp;
                }
            }
            $phones = $this->array_orderby($phones, 'main_flag', SORT_DESC, 'id', SORT_DESC);
            $mails = $this->array_orderby($mails, 'main_flag', SORT_DESC, 'id', SORT_DESC);
        }
        $initialData = [
            'fact' => $logAddr['fact'] ?? $ownerFact ?? null,
            'reg' => $logAddr['reg'] ?? $ownerReg ?? null,
            'phone' => $phones[0]['name'] ?? null,
            'mail' => $mails[0]['name'] ?? null,
        ];
        if ($format !== PdfGenerator::FORMAT_A4 && $format !== PdfGenerator::FORMAT_A5) {
            $this->errorResponse($agreement, 'Неподдерживаемый формат печати');
        }

        $this->checkAccess($this->action->getUniqueId(), $agreement, $this->actionParams);

        /* @var $generator PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');
        $data['owner'] = $owner;
        $data['initial_data'] = $initialData;
        $data['agreement'] = $agreement;

        try {
            $path = $generator->createPersAgreementFile($data, $format);
        } catch (\Exception $e) {
            throw new \yii\base\Exception('Ошибка при генерации файла');
        }

        return [
            'result' => [
                'url' => \Yii::getAlias('@web') . '/upload/pdf/' . $path[1] . '.' . $path[2],
            ],
        ];
    }

    /**
     * Формирование пустого pdf файла для печати согласия на обработку персональных данных
     *
     * @param string $format
     * @return array
     */
    public function actionMakeEmptyPersFile($format = PdfGenerator::FORMAT_A4)
    {
        if ($format !== PdfGenerator::FORMAT_A4 && $format !== PdfGenerator::FORMAT_A5) {
            throw new Exception('Неподдерживаемый формат печати');
        }

        /* @var $generator PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');

        try {
            $path = $generator->createPersAgreementFile($data = [], $format);
        } catch (\Exception $e) {
            throw new \yii\base\Exception('Ошибка при генерации файла');
        }

        return [
            'result' => [
                'url' => \Yii::getAlias('@web') . '/upload/pdf/' . $path[1] . '.' . $path[2],
            ],
        ];
    }

    /**
     * Формирование pdf файла для печати согласия на ветеринарное вмешательство
     *
     * @param int $id_visit
     * @param int $id_pet
     * @param string $format
     * @return array
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionMakeSurgFile(int $id_visit, int $id_pet, $format = PdfGenerator::FORMAT_A4)
    {
        $visit = Visits::findOne(['id' => $id_visit]);
        if (!$visit) {
            $this->errorResponse($visit, 'Визит не найден');
        }
        $pets = $visit->pets;
        $petsIds = [];
        foreach ($pets as $pet) {
            $petsIds[] = $pet->id;
        }
        if (!in_array($id_pet, $petsIds)) {
            throw new Exception('Указанное животное отсутствует в рамках приема');
        }
        if ($format !== PdfGenerator::FORMAT_A4 && $format !== PdfGenerator::FORMAT_A5) {
            $this->errorResponse($visit, 'Неподдерживаемый формат печати');
        }

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        /* @var $generator PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');
        $data['visit'] = $visit;
        $data['id_pet'] = $id_pet;

        try {
            $path = $generator->createSurgAgreementFile($data, $format);
        } catch (\Exception $e) {
            throw new \yii\base\Exception('Ошибка при генерации файла');
        }

        return [
            'result' => [
                'url' => \Yii::getAlias('@web') . '/upload/pdf/' . $path[1] . '.' . $path[2],
            ],
        ];
    }

    /**
     * Формирование пустого pdf файла для печати согласия на ветеринарное вмешательство
     *
     * @param string $format
     * @return array
     */
    public function actionMakeEmptySurgFile($format = PdfGenerator::FORMAT_A4)
    {
        if ($format !== PdfGenerator::FORMAT_A4 && $format !== PdfGenerator::FORMAT_A5) {
            throw new Exception('Неподдерживаемый формат печати');
        }

        /* @var $generator PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');

        try {
            $path = $generator->createSurgAgreementFile($data = [], $format);
        } catch (\Exception $e) {
            throw new \yii\base\Exception('Ошибка при генерации файла');
        }

        return [
            'result' => [
                'url' => \Yii::getAlias('@web') . '/upload/pdf/' . $path[1] . '.' . $path[2],
            ],
        ];
    }

    /**
     * Временный метод, необходимый до полноценной интеграции с мосру и получения данных от них по соап, кроме этого
     * не несёт смысловой нагрузки
     * @return array
     */
    public function actionProvideRefundDataTemp($id_visit, $bank_name, $corresponded_account, $credit_organization_name, $bik, $client_account, $client_fio)
    {
        $visit = Visits::findOne(['id' => $id_visit]);

        if (!$visit) {
            throw new Exception("Не найден осмотр с id $id_visit для возврата средств");
        }

        $refundData = new RefundData();
        $refundData->id_visit = $visit->id;
        $refundData->bank_name = $bank_name;
        $refundData->corresponded_account = $corresponded_account;
        $refundData->org_name = $credit_organization_name;
        $refundData->bik = $bik;
        $refundData->client_account = $client_account;
        $refundData->client_fio = $client_fio;
        $refundData->save();

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new RefundEvent([
            'visit' => $visit,
            'bank' => $bank_name,
            'corresponded_account' => $corresponded_account,
            'org_name' => $credit_organization_name,
            'bik' => $bik,
            'client_account' => $client_account,
            'client_fio' => $client_fio,
        ]));

        (new MosruStatusSender())->visitPaymentDataReceived($id_visit);

        return [
            'result' => 'OK',
        ];
    }

    public function actionGetService($id, $is_file = false)
    {
        $service = new ServiceReportActions();
        $res = $service->actionView($id);
        $ids = [];
        if (isset($res['data']['animals'])) foreach ($res['data']['animals'] as $animal) {
            $ids[] = $animal['id_pet'];
        }
        $pets = $ids ? (new Query())
            ->select([
                'pets.id',
                'pets.name as P9_Petname',
                'pets.sex as P8_Petsex',
                'species.id as species_id',
                'species.name as P6_Speciesname',
                'breeds.id as breed_id',
                'breeds.name as P7_Breedname',
                new Expression("STRING_AGG(pi.identification_code, ', ') as \"P0_Petchpidentificationcode\"")
            ])
            ->from('pets')
            ->leftJoin('species', 'species.id = pets.id_species')
            ->leftJoin('breeds', 'breeds.id = pets.id_breed')
            ->leftJoin(PetIdentification::tableName() . ' pi', 'pi.id_pet = pets.id AND pi.id_ident_type = 1')
            ->where(['in', 'pets.id', $ids])
            ->groupBy([
                'pets.id',
                'pets.name',
                'pets.sex',
                'species.id',
                'species.name',
                'breeds.id',
                'breeds.name'
            ])
            ->all() : [];
        $petsIndexed = [];
        foreach ($pets as $pet) $petsIndexed[$pet['id']] = $pet;
        if (isset($res['data']['animals'])) foreach ($res['data']['animals'] as &$animal) {
            foreach ($animal['params'] as &$param) {
                if (isset($pets[$animal['id_pet']][$param['attributes']['tech_name']])) $param['value'] = $pets[$animal['id_pet']][$param['attributes']['tech_name']];
            }
        }

        if ($is_file) {
            if (empty($service->findServicesWithReports($service->findVisitServiceRecord($id, 'files')->id_service))) {
                throw new BadRequestHttpException('Для данной услуги не предусмотрено печатной формы отчета');
            }
            $data = $service->generatePDF($id, true);
            foreach ($data['params'][0] as $key => $param) {
                if (isset($pets[0][$key])) $data['params'][0][$key] = $pets[0][$key];
            }
            $generator = Yii::$app->get('pdfGenerator');
            $generator->entity_type = 'visits-gov-services';
            $generator->entity_id = $id;
            
            try {
                [$dir, $filename, $ext] = $generator->createDocumentList($data['id_report'], $data['params']);
            } catch (\Exception $e) {
                throw new ServerErrorHttpException("Ошибка при генерации отчета: {$e->getMessage()}", 0, $e);
            }

            /** @var FileService $fileService */
            $fileService = \Yii::$app->fileService;
            $fileService->repository = $generator;
            $hash = $fileService->generateHash($filename);
            $filePath = $fileService->repository->save($dir . '/' . $filename . '.' . $ext, $hash, $ext);

            try {
                $fileResource = new FileResource();
                $fileResource->hash = $hash;
                $fileResource->path = $filePath;
                $fileResource->name = $hash . '.' . $ext;
                $fileResource->entity_id = $id;
                $fileResource->entity_type = 'visits-gov-services';
                $fileResource->save();
            } catch (\Exception $e) {
                $fileService->repository->delete($filePath);
                throw new \app\common\components\media\MediaException("Ошибка при сохранении файла: {$e->getMessage()}", 0, $e);
            }

            return [
                'data' => [
                    'id' => $fileResource->id,
                    'type' => 'file',
                    'attributes' => [
                        'path' => $fileResource->path,
                        'name' => $fileResource->name,
                        'entity-id' => $fileResource->entity_id,
                        'entity-type' => $fileResource->entity_type,
                        'created' => $fileResource->created
                    ],
                    'links' => [
                        'self' => [
                            'href' => $fileResource->links['self']
                        ],
                        'related' => [
                            'href' =>  $fileResource->links['related']
                        ]
                    ]
                ]
            ];
        }



        return ['data' => $res];
    }
}
