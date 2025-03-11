<?php

namespace app\modules\v2\modules\visit\models;

use app\common\models\UserModel;
use app\models\db\Diseases;
use app\models\db\OutsideOrg;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use app\models\db\tmc\TmcBase;
use app\models\db\Violation;
use app\models\db\ViolationCancellation;
use app\models\db\ViolationType;
use app\models\db\VisitPets;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitServiceTmcPet;
use app\models\db\VisitServiceVaccination;
use app\modules\v2\common\balance\WriteOffCalculation;
use app\modules\v2\modules\gosvetnadzor\models\ViolationChangeStateModel;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;
use app\modules\v2\modules\tmc\controllers\BalanceActionsController;
use app\modules\v2\modules\tmc\models\BalanceActionSaveModel;
use yii\base\BaseObject;
use yii\base\DynamicModel;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

class ServiceTmcsModelSave
{
    use VisitTrait, VisitServiceTmcTrait;

    /**
     * @var \app\models\db\Visits
     */
    public $visit;

    /**
     * @var \app\models\db\VisitsGovServices
     */
    public $visits_gov_service;

    /**
     * @var UserModel|null
     */
    protected $_user;

    /**
     * Основная информация о выбранных балансовых ТМЦ
     * Используется для валидации/работы логики списания
     *
     * @var array
     */
    protected $_balance_tmc_info;

    /**
     * Основная информация о выбранных ВНЕ балансовых ТМЦ
     * Используется для валидации/работы логики
     *
     * @var array
     */
    protected $_other_tmc_info;

    /**
     * Массива два, а row_id не должны повторяться, что бы было понятно на какую строку ругаемся в данный момент
     *
     * @var array
     */
    protected $_user_row_ids = [];

    /**
     * Сохранение выбранных ТМЦ
     *
     * @param int $id_visit
     * @param int $id_visits_gov_service
     * @param $balance_tmcs
     * @param $other_tmcs
     * @param string|null $valid_until
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function save(int $id_visit, int $id_visits_gov_service, $balance_tmcs, $other_tmcs, $valid_until = null)
    {
        // Валидируем
        $this->validateVisit_AND_VisitsGovService($id_visit, $id_visits_gov_service, true);

        // Основной массив
        $balance_tmcs = $this->checkMainArray($balance_tmcs, 'balance_tmcs');
        $other_tmcs = $this->checkMainArray($other_tmcs, 'other_tmcs');

        $balance_tmcs = $this->validateBalanceTmcs($id_visit, $id_visits_gov_service, $balance_tmcs);
        $other_tmcs = $this->validateOtherTmcs($id_visit, $id_visits_gov_service, $other_tmcs);

        // Подсчитываем реально использованное кол-во для балансовых ТМЦ + цену
        $balance_tmcs = $this->calcCountAndPrice($balance_tmcs);

        /*
         * Удаляем старые значения и добавляем новые в транзакции
         */
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            VisitServiceTmc::deleteAll([
                'AND',
                ['id_visit' => $id_visit],
                ['id_visits_gov_service' => $id_visits_gov_service],
            ]);

            //Перед удалением списаний найдем переводы с баланса организации, которые надо удалить
            $balance_action_ids = $this->transfersToDelete($id_visits_gov_service);

            // Удаляем списания
            BalanceFlow::deleteAll([
                'AND',
                ['id_visit_service' => $id_visits_gov_service],
            ]);

            //Удаляем связанные с передачей от орг. списки...
            BalanceActionTmcList::deleteAll([
                'AND',
                ['id_action' => $balance_action_ids]
            ]);
            //...и сами записи об этих переводах
            BalanceAction::deleteAll([
                'AND',
                ['id' => $balance_action_ids]
            ]);

            // балансовые сохраняем
            foreach ($balance_tmcs as $key => $attributes) {
                try {
                    $visitServiceTmc = $this->saveVisitServiceTmc($attributes);
                    $this
                        ->savePetVaccinations(
                            $attributes['pets'],
                            $visitServiceTmc,
                            [
                                'batch'           => $visitServiceTmc->balance->inventory_number,
                                'production_date' => $visitServiceTmc->balance->production_date,
                                'expiry_date'     => $visitServiceTmc->balance->expiration_date,
                                'valid_until'     => $attributes['valid_until'],
                            ]
                        )
                        ->saveVisitServiceTmcPet($attributes['pets'], $visitServiceTmc)
                        ->writeOff($id_visits_gov_service, $attributes); // Баланс

                } catch (\Throwable $e) {
                    throw new BadRequestHttpException('ID - ' . $key . '. ' . $e->getLine() . ':' . $e->getMessage());
                }
            }

            // ВНЕбалансовые сохраняем
            foreach ($other_tmcs as $key => $attributes) {
                try {
                    $visitServiceTmc = $this->saveVisitServiceTmc($attributes);
                    $this
                        ->savePetVaccinations($attributes['pets'], $visitServiceTmc, $attributes, $valid_until)
                        ->saveVisitServiceTmcPet($attributes['pets'], $visitServiceTmc);
                } catch (\Throwable $e) {
                    throw new BadRequestHttpException('ID - ' . $key . '. ' . $e->getLine() . ':' . $e->getMessage());
                }
            }

            $this->saveVisitParams($id_visits_gov_service);

        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction->commit();
    }

    private function saveVisitParams($visitsGovServiceId)
    {

        try {
            (new VisitParamsModel())
                ->saveVisitParamsByVisitGovService($visitsGovServiceId);
        } catch (\Throwable $exception){

        }
    }


    /**
     * Сохранение ТМЦ
     *
     * @param $balance_tmc
     * @return VisitServiceTmc
     * @throws BadRequestHttpException
     */
    private function saveVisitServiceTmc($balanceTmc)
    {
        $visitService = (new VisitServiceTmc())
            ->setAttributes($balanceTmc);

        if (!$visitService->save()) {
            $errors = $visitService->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении ТМЦ' : implode(";", array_values($errors)));
        }

        return $visitService;
    }

    /**
     * Сохраняет данные о вакцинации
     *
     * @param VisitServiceTmc $visitServiceTmc
     */
    private function savePetVaccinations(array $petIds, VisitServiceTmc $visitServiceTmc, array $tmcAttributes = [], $valid_until = null)
    {
        foreach ($petIds as $petId) {
            // 1.
            // Проверка типа сервисов на Викцинацию
            if ($visitServiceTmc->type_tmc != TmcBase::TYPE_VACCINE) {
                continue;
            }

            $attributes = $this->getPetVaccinationsAttr($visitServiceTmc, $petId, $tmcAttributes, $valid_until);
            if (!$attributes) {
                return $this;
            }

            //Проверка, если ли уже информация о вакцинации в разделе "ВАКЦИНАЦИИ И ОБРАБОТКИ"
            $petVaccination = $this->createPetVaccination($visitServiceTmc, $attributes);

            if (!$petVaccination->save()) {
                $errors = $petVaccination->getErrorSummary(true);
                throw new BadRequestHttpException(!$errors ? 'Ошибка при сохранении данных о вакцинации' : implode("\n", array_values($errors)));
            }

            // Сохранение в VisitServiceVaccination
            // $tmcAttributes заполняется только для Внебалансовых ТМЦ
            if ($tmcAttributes && !VisitServiceVaccination::find()->where(['id_visit_service_tmc' => $visitServiceTmc->id])->count()) {
                $visitServiceTmc->link(
                    'visitServiceVaccination',
                    (new VisitServiceVaccination())
                        ->setAttributes(array_merge(
                            $tmcAttributes, [
                                'date' => $petVaccination->date
                            ]
                        ))
                );
            }
        }

        // Закрываем нарушения по вакцинации от бешенства, если внесены соответствующие вакцины
        (new ViolationModel())->checkAndCancelPetsRabiesViolation($petIds);

        return $this;
    }

    /**
     * Сохраняет данные о связях Прием-Услуга-ТМЦ-Живорное
     *
     * @param array $petIds
     * @param VisitServiceTmc $visitServiceTmc
     * @throws BadRequestHttpException
     */
    private function saveVisitServiceTmcPet(array $petIds, VisitServiceTmc $visitServiceTmc)
    {
        foreach ($petIds as $petId) {
            $attr = $visitServiceTmc->getAttributes();
            unset($attr['id']);

            $model = (new VisitServiceTmcPet())
                ->setAttributes(array_merge(
                    $attr,
                    [
                        'id_pet'               => $petId,
                        'id_visit_service_tmc' => $visitServiceTmc->id,
                    ]
                ));

            if (!$model->save()) {
                $errors = $model->getErrorSummary(true);
                throw new BadRequestHttpException(!$errors ? 'Ошибка при сохранении связанных данных' : implode("\n", array_values($errors)));
            }

        }

        return $this;
    }

    /**
     * Поиск, если ли уже информация о вакцинации или обработке в разделе "ВАКЦИНАЦИИ И ОБРАБОТКИ"
     *
     * @param $id
     * @param $attributes
     * @return PetRabiesVaccination|PetOtherVaccinations
     */
    private function createPetVaccination(VisitServiceTmc $visitServiceTmc, $attributes)
    {
        // Если вакцина против бешенства
        if ($visitServiceTmc->tmc->isDiseasesRabies()) {
            $className = PetRabiesVaccination::class;
        } else {
            $className = PetOtherVaccinations::class;
        }

        //Ищем уже существующую запись о вакцинации
        /* @var PetRabiesVaccination|PetOtherVaccinations $petVaccination */
        $petVaccination = $className::find()
            ->where([
                'id_pet'     => $attributes['id_pet'],
                'id_vaccine' => $attributes['id_vaccine'],
                'type_tmc'   => $attributes['type_tmc'],
                'batch'      => $attributes['batch'],
                'date'       => $attributes['date'],
            ])
            ->one();

        if ($petVaccination && !$petVaccination->id_visit_service_tmc) {
            $petVaccination->id_visit_service_tmc = $visitServiceTmc->id;
        } else {
            $petVaccination = new $className;
            $petVaccination->setAttributes($attributes);
        }

        return $petVaccination;
    }

    /**
     * Возвращает аттрибуты для сущности о вакцинации
     *
     * @param VisitServiceTmc $visitService
     */
    private function getPetVaccinationsAttr(VisitServiceTmc $visitService, int $petId, array $tmcAttributes = [], string $valid_until = null)
    {
        if ($visitService->type_tmc !== TmcBase::TYPE_VACCINE) {
            return [];
        }

        return array_merge([
            'id_visit_service_tmc' => $visitService->id,
            'id_pet'               => $petId,
            'id_vaccine'           => $visitService->id_tmc,
            'id_organization'      => $visitService->visit->id_organization,
            'is_out_org'           => false,
            'id_specialist'        => $visitService->visit->getVisitsSpecialists()->one()->id_specialist,
            'type_tmc'             => $visitService->type_tmc,
            'drug_name'            => $visitService->tmc->name,
            'producer_name'        => $visitService->tmc->produced,
            'batch'                => $visitService->balance->inventory_number ?? null,
            'production_date'      => $visitService->balance->production_date ?? null,
            'expiry_date'          => $visitService->balance->expiration_date ?? null,
            'valid_until'          => $valid_until ?? date('Y-m-d', strtotime($visitService->visit->fact_start_dttm . ' +1 year')),
            'date'                 => date('Y-m-d', strtotime($visitService->visit->fact_start_dttm)),
        ], array_filter($tmcAttributes, function ($v, $k) {
                return in_array($k, ['batch', 'expiry_date', 'production_date', 'valid_until']);
            }, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * Списываем с балансов использованные ТМЦ
     *
     * @param $id_visits_gov_service
     * @param $row_id
     * @param $tmc
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    protected function writeOff($id_visits_gov_service, $tmc)
    {
        $id_balance_tmc = $tmc['id_balance_tmc'];
        if (empty(Balance::findOne($tmc['id_balance_tmc'])->id_specialist)) {
            $id_balance_tmc = $this->makeTransfer($id_visits_gov_service, $tmc);
        }

        $flow = new BalanceFlow([
            'id_tmc_balance'   => $id_balance_tmc,
            'flow_type'        => BalanceFlow::FLOW_TYPE_DECREASE,
            'flow_action'      => BalanceFlow::FLOW_ACTION_EXPENSE,
            'count'            => $tmc['count'],
            'id_visit_service' => $id_visits_gov_service,
        ]);

        if (!$flow->save()) {
            $errors = $flow->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors)
                ? 'Ошибка при сохранении приходной/расходной операции' : implode(";", array_unique(array_values($errors))));
        }

        if (!$tmc['count_utilize']) {
            return $this;
        }

        // Утилизируем остатки
        $flow_utilize = new BalanceFlow([
            'id_tmc_balance'   => $id_balance_tmc,
            'flow_type'        => BalanceFlow::FLOW_TYPE_DECREASE,
            'flow_action'      => BalanceFlow::FLOW_ACTION_EXPENSE_UTILIZE_IN_VISIT,
            'count'            => $tmc['count_utilize'],
            'id_visit_service' => $id_visits_gov_service,
        ]);

        if (!$flow_utilize->save()) {
            $errors = $flow_utilize->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors)
                ? 'Ошибка при сохранении приходной/расходной операции' : implode(";", array_unique(array_values($errors))));
        }

        return $this;
    }

    /**
     * Подсчет использованного кол-ва и цены для балансового ТМЦ
     *
     * @param array $balance_tmcs
     * @return array
     */
    protected function calcCountAndPrice($balance_tmcs)
    {
        if (empty($balance_tmcs)) {
            return [];
        }

        foreach ($balance_tmcs as $key => $tmc) {
            $calculation = new WriteOffCalculation([
                    'idBalanceTmc'     => $tmc['id_balance_tmc'],
                    'idDosage'         => $tmc['id_dosage'],
                    'countSelected'    => $tmc['count_selected'],
                    'writeOffPackForm' => $tmc['write_off_pack_form'],
                    'useUtilize'       => true,
                ]
            );
            $balance_tmcs[$key]['count'] = $calculation->getCount();
            $balance_tmcs[$key]['count_utilize'] = $calculation->getCountUtilize();
            $balance_tmcs[$key]['count_production_form'] = $calculation->getCountProductionForm();
            $balance_tmcs[$key]['price'] = $calculation->getPrice();
        }

        return $balance_tmcs;
    }

    /**
     * Валидируем ВНЕбалансовые ТМЦ
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @param $other_tmcs
     * @return array
     * @throws BadRequestHttpException
     */
    protected function validateOtherTmcs($id_visit, $id_visits_gov_service, $other_tmcs)
    {
        if (empty($other_tmcs)) {
            return [];
        }

        /*
         *  Формат каждой записи по отдельности
         * + собираем ID ТМЦ чтобы подтянуть по ним инфо для дальнейшей валидации и логики
         */
        $cleared_other_tmcs = [];
        $other_tmcs_ids = [];
        foreach ($other_tmcs as $key => $tmc) {
            try {
                $cleared_other_tmcs[$key] = $this->validateOtherTmcByOne($id_visit, $tmc);
                $other_tmcs_ids[$key] = $cleared_other_tmcs[$key]['id_tmc'];
            } catch (\Throwable $e) {
                throw new BadRequestHttpException('ID - ' . $key . '. ' . $e->getMessage());
            }
        }

        /*
         * Запрашиваем из бд данные для проверки и проверяем каждый элемент
         */
        $this->loadOtherTmsInfo($other_tmcs_ids);

        foreach ($cleared_other_tmcs as $key => $tmc) {
            $id_tmc = $tmc['id_tmc'];

            // Существует?
            if (!array_key_exists($id_tmc, $this->_other_tmc_info)) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Нет ТМЦ с указанными id_tmc'
                );
            }

            // Дозировка существует.
            if (!empty($tmc['id_dosage'])
                &&
                !array_key_exists($tmc['id_dosage'], $this->_other_tmc_info[$id_tmc]['dosages'])) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Указанна несуществующая дозировка'
                );
            }

            // ID_TMC | TYPE_TMC
            if (
                $this->_other_tmc_info[$id_tmc]['id_tmc'] !== $tmc['id_tmc']
                ||
                $this->_other_tmc_info[$id_tmc]['type_tmc'] !== $tmc['type_tmc']
            ) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Нет ТМЦ с указанными id_tmc, type_tmc'
                );
            }

            $is_uncountable = ($this->_other_tmc_info[$id_tmc]['is_uncountable'] === true);

            // У ИСЧИСЛИМЫХ должно быть указано кол-во
            if (empty($tmc['count_selected']) && $is_uncountable == false) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Необходимо указать кол-во'
                );
            }

            /*
             * Дополняем полями
             */
            $cleared_other_tmcs[$key]['id_visit'] = $id_visit;
            $cleared_other_tmcs[$key]['id_visits_gov_service'] = $id_visits_gov_service;
        }

        return $cleared_other_tmcs;
    }

    /**
     * Валидируем ВНЕбалансовые ТМЦ по одному
     *
     * @param $tmc
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateOtherTmcByOne($id_visit, $tmc)
    {
        $empty_service_tmc = [
            'id_tmc'          => null,
            'type_tmc'        => null,
            'count_selected'  => null,
            'id_dosage'       => null,
            'batch'           => null,
            'production_date' => null,
            'expiry_date'     => null,
            'valid_until'     => null,
            'pets'            => [],
        ];

        // Исключаем то чего нет в списке, недостающие добавляем
        $tmc = array_intersect_key($tmc, $empty_service_tmc);
        $tmc = array_merge($empty_service_tmc, $tmc);

        $rules = [
            // TYPE
            [['type_tmc', 'pets'], 'required'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'],
                'in',
                'range'       => [
                    TmcBase::TYPE_VACCINE,
                    TmcBase::TYPE_EXP_MATERIAL,
                    TmcBase::TYPE_DRUG
                ],
                'strict'      => true,
                'skipOnEmpty' => false,
                'skipOnError' => false
            ],
            [['id_tmc'], 'required'],
            [['id_tmc', 'id_dosage'], 'integer', 'min' => 0, 'max' => 2147483647], // PSQL INTEGER	4 bytes
            [['count_selected'], 'number', 'min' => 0.01, 'numberPattern' => '/^[0-9]{1,7}\.?[0-9]{0,2}$/'],
            ['batch', 'string', 'max' => 255],
            [['production_date', 'expiry_date', 'valid_until'], 'date', 'format' => 'php:Y-m-d'],
            [
                'pets',
                function ($attribute, $params) use ($id_visit) {
                    $attrValue = $params['attrValue'];
                    if (!$attrValue) {
                        $this->addError($attribute, 'Список животных не определен');

                        return;
                    }

                    if (!is_array($attrValue)) {
                        $this->addError($attribute, 'Значение pets должо быть массивом');

                        return;
                    }

                    $petIds = VisitPets::find()
                        ->select('id_pet')
                        ->andWhere(['id_visit' => $id_visit])
                        ->andWhere(['in', 'id_pet', $attrValue])
                        ->column();

                    foreach ($attrValue as $idPet) {
                        if (!in_array($idPet, $petIds)) {
                            $this->addError($attribute, 'Значение(я) pets не соответствует списку животных в приеме');
                            break;
                        }
                    }
                },
                'params' => [
                    'attrValue' => $tmc['pets']
                ]
            ],
        ];

        $model = DynamicModel::validateData($tmc, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode(";", array_values($errors)));
        }

        return $model->attributes;
    }

    /**
     * Валидация массива балансовых ТМЦ
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @param $balance_tmcs
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    protected function validateBalanceTmcs($id_visit, $id_visits_gov_service, $balance_tmcs)
    {
        if (empty($balance_tmcs)) {
            return [];
        }

        /*
         *  Формат каждой записи по отдельности
         * + собираем ID балансов чтобы проверить их
         */
        $cleared_balance_tmcs = [];
        $balance_tmcs_ids = [];

        foreach ($balance_tmcs as $key => $balance_tmc) {
            try {
                $cleared_balance_tmcs[$key] = $this->validateBalanceTmcByOne($id_visit, $balance_tmc);
                $balance_tmcs_ids[$key] = $cleared_balance_tmcs[$key]['id_balance_tmc'];

            } catch (\Throwable $e) {
                throw new BadRequestHttpException('ID - ' . $key . '. ' . $e->getMessage());
            }
        }

        /*
         * Запрашиваем из бд данные для проверки и проверяем каждый элемент
         */
        $this->loadBalanceTmsInfo($balance_tmcs_ids);

        foreach ($cleared_balance_tmcs as $key => $tmc) {

            $id_balance_tmc = $tmc['id_balance_tmc'];
            // Принадлежность доступному балансу
            if (!array_key_exists($id_balance_tmc, $this->_balance_tmc_info)) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Указанное ТМЦ не найдено ни на балансе у пользователя, ни на балансе организации'
                );
            }

            // Дозировка существует.
            if (!empty($tmc['id_dosage'])
                &&
                !array_key_exists($tmc['id_dosage'], $this->_balance_tmc_info[$id_balance_tmc]['dosages'])) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Указанна несуществующая дозировка'
                );
            }

            $is_uncountable = ($this->_balance_tmc_info[$id_balance_tmc]['is_uncountable'] === true);

            // У ИСЧИСЛИМЫХ должно быть указано кол-во
            if (empty($tmc['count_selected']) && $is_uncountable == false) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Необходимо указать кол-во'
                );
            }

            // ID_TMC | TYPE_TMC
            if (
                $this->_balance_tmc_info[$id_balance_tmc]['id_tmc'] !== $tmc['id_tmc']
                ||
                $this->_balance_tmc_info[$id_balance_tmc]['type_tmc'] !== $tmc['type_tmc']
            ) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. Нет ТМЦ с указанными id_tmc, type_tmc, id_balance_tmc'
                );
            }

            /*
             * Дополняем полями
             */
            $cleared_balance_tmcs[$key]['id_visit'] = $id_visit;
            $cleared_balance_tmcs[$key]['id_visits_gov_service'] = $id_visits_gov_service;

        }

        // Проверка количества ТМЦ на балансе специалиста
        $balanceTmcCount = []; // тут ведем подсчет суммы кол. ТМЦ, что мы использовали в услуге
        $tmcCountCache = [];
        // еще проверки ТМЦ...
        foreach ($balance_tmcs as $key => $tmc) {
            //Количество всех ТМЦ, включая и тех, что уже сохранены
            if (!array_key_exists($tmc['id_balance_tmc'], $tmcCountCache)) {
                $tmcCountCache[$tmc['id_balance_tmc']] = $this->getSavedCountTmc($id_visit, $id_visits_gov_service, $tmc['id_balance_tmc']) + $this->_balance_tmc_info[$tmc['id_balance_tmc']]['count'];
            }

            $calculation = new WriteOffCalculation([
                    'idBalanceTmc'     => $tmc['id_balance_tmc'],
                    'idDosage'         => $tmc['id_dosage'] ?? null,
                    'countSelected'    => $tmc['count_selected'],
                    'writeOffPackForm' => $tmc['write_off_pack_form'],
                ]
            );

            $count = $calculation->getCount();
            if (!empty($balanceTmcCount[$tmc['id_balance_tmc']])) {
                $outCount = $balanceTmcCount[$tmc['id_balance_tmc']] + $count;
            } else {
                $outCount = $count;
            }

            if (bccomp((string)$outCount, (string)$tmcCountCache[$tmc['id_balance_tmc']], 2) === 1) {
                throw new BadRequestHttpException(
                    'ID - ' . $key . '. На балансе нет достаточного количества ТМЦ'
                );
            }

            $balanceTmcCount[$tmc['id_balance_tmc']] = $outCount;
        }

        return $cleared_balance_tmcs;
    }

    /**
     * @param int $id_visit
     * @param int $id_visits_gov_service
     * @param int $id_tmc
     * @return float
     */
    public function getSavedCountTmc(int $id_visit, int $id_visits_gov_service, int $id_tmc): float
    {
        return (float)VisitServiceTmc::find()
            ->andWhere([
                'id_visit'              => $id_visit,
                'id_visits_gov_service' => $id_visits_gov_service,
                'id_balance_tmc'        => $id_tmc,
            ])
            ->sum('count');
    }

    /**
     * Валидация балансовых ТМЦ по структуре по одному
     *
     * @param array $balance_tmc
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateBalanceTmcByOne($id_visit, $balance_tmc): array
    {
        $empty_service_tmc = [
            'id_balance_tmc'      => null,
            'id_tmc'              => null,
            'type_tmc'            => null,
            'count_selected'      => null,
            'id_dosage'           => null,
            'write_off_pack_form' => null,
            'valid_until'         => null,
            'pets'                => [],
        ];

        // Исключаем то чего нет в списке, недостающие добавляем
        $balance_tmc = array_intersect_key($balance_tmc, $empty_service_tmc);
        $balance_tmc = array_merge($empty_service_tmc, $balance_tmc);

        $rules = [
            // TYPE
            [['type_tmc'], 'required'],
            [['pets'], 'required', 'message' => 'Необходимо выбрать животное (-ых)'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'],
                'in',
                'range'       => [
                    TmcBase::TYPE_VACCINE,
                    TmcBase::TYPE_EXP_MATERIAL,
                    TmcBase::TYPE_DRUG
                ],
                'strict'      => true,
                'skipOnEmpty' => false,
                'skipOnError' => false
            ],
            [['id_balance_tmc', 'id_tmc'], 'required'],
            [['id_balance_tmc', 'id_tmc', 'id_dosage'], 'integer', 'min' => 0, 'max' => 2147483647], // PSQL INTEGER	4 bytes
            [['valid_until'], 'date'],
            [['write_off_pack_form'], 'boolean'],
            [
                'pets',
                function ($attribute, $params) use ($id_visit) {
                    $attrValue = $params['attrValue'];
                    if (!$attrValue) {
                        $this->addError($attribute, 'Список животных не определен');

                        return;
                    }

                    if (!is_array($attrValue)) {
                        $this->addError($attribute, 'Значение pets должо быть массивом');

                        return;
                    }

                    $petIds = VisitPets::find()
                        ->select('id_pet')
                        ->andWhere(['id_visit' => $id_visit])
                        ->andWhere(['in', 'id_pet', $attrValue])
                        ->column();

                    foreach ($attrValue as $idPet) {
                        if (!in_array($idPet, $petIds)) {
                            $this->addError($attribute, 'Значение(я) pets не соответствует списку животных в приеме');
                            break;
                        }
                    }
                },
                'params' => [
                    'attrValue' => $balance_tmc['pets']
                ]
            ],
        ];

        $model = DynamicModel::validateData($balance_tmc, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode(";", array_values($errors)));
        }

        return $model->attributes;
    }

    /**
     * Проверяем основной массив
     * и формируем массив ключ(id) => строка (балансового) ТМЦ
     *
     * @param $array
     * @param $array_name
     * @return array
     * @throws BadRequestHttpException
     */
    protected function checkMainArray($array, $array_name)
    {
        if (!is_array($array)) {
            throw new BadRequestHttpException('Параметр ' . $array_name . ' должен быть массивом');
        }

        $result = [];
        foreach ($array as $row) {
            if (!is_array($row)) {
                throw new BadRequestHttpException('Параметр ' . $array_name . ' должен быть массивом массивов');
            }

            if (!array_key_exists('row_id', $row)) {
                throw new BadRequestHttpException('Некоторые строки не содержат параметра row_id');
            }

            if (!is_numeric($row['row_id'])) {
                throw new BadRequestHttpException('Параметр row_id должен быть числом');
            }

            if (array_key_exists($row['row_id'], $result)) {
                throw new BadRequestHttpException('Массив ' . $array_name . ' содержит элементы с повторяющимися row_id');
            }

            if (in_array($row['row_id'], $this->_user_row_ids)) {
                throw new BadRequestHttpException('В массивах балансовых и внебалансовых ТМЦ повторяющиеся row_id: ' . $row['row_id']);
            }

            $key = $row['row_id'];
            unset($row['row_id']);
            $result[$key] = $row;
            $this->_user_row_ids[] = $key;
        }

        return $result;
    }

    /**
     * Подгружаем информацию о выбранных балансовых ТМЦ
     *
     * @param $balance_tmcs_ids
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    protected function loadBalanceTmsInfo($balance_tmcs_ids)
    {
        /*
         * ТМЦ либо на балансе пользователя, либо на балансе организации пользователя
         * В будущем, списывать с баланса организации можно будет только расходные материалы
         */
        //        $id_specialist = 2673;
        if ($this->visit->type === Visits::TYPE_VISIT_VC) {
            $id_specialist = (new Query())
                ->select('specialist_id')
                ->from('specialist2vaccination_station')
                ->where([
                    'vaccination_station_id' => $this->visit->vaccination_station_id
                ])->column();
            $id_organization = $this->visit->vaccinationStation->parent_id;
        } else {
            $id_specialist = $this->getUserIdentity()->id_specialist;
            $id_organization = $this->getUserIdentity()->specialist->id_organization;
        }

        $this->_balance_tmc_info = Balance::find()
            ->select([
                'tmc.balance.id',
                'tmc.balance.id_tmc',
                'tmc.balance.type_tmc',
                'tmc.balance.count',
                'tmc.balance.price',
                'tmc.balance.id_organization',
                'tmc.balance.id_specialist',

                'tmc.tmc.is_uncountable',

                'tmc.production_form.volume',
                'tmc.production_form.is_utilize',

            ])
            ->innerJoin(
                'tmc.production_form',
                'tmc.balance.id_tmc = tmc.production_form.id_tmc AND ' .
                'tmc.balance.type_tmc = tmc.production_form.type_tmc AND ' .
                'tmc.balance.id_production_form = tmc.production_form.id'
            )
            ->innerJoin(
                'tmc.tmc',
                'tmc.balance.id_tmc = tmc.tmc.id'// AND tmc.balance.type_tmc = tmc.tmc.type
            )
            ->with([
                'dosages' => function ($query) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query
                        ->select([
                            'id',
                            'id_tmc',
                            'type_tmc',
                            'dosage',
                        ])
                        ->indexBy('id');
                },
            ])
            ->where([
                'AND',
                ['IN', 'tmc.balance.id', $balance_tmcs_ids],
                ['!=', 'tmc.balance.type_tmc', 'equipment'], // оборудование в этом методе не нужно
                //['>', 'tmc.balance.count', 0],
                [
                    'OR',
                    [
                        // Расходники могут и со своего баланса и с баланса организации
                        'AND',
                        ['tmc.balance.type_tmc' => 'exp_material'],
                        ['tmc.balance.id_organization' => $id_organization],
                        ['IS', 'tmc.balance.id_specialist', null],
                    ],
                    [
                        // На балансе спеца
                        'AND',
                        ['tmc.balance.id_organization' => $id_organization],
                        ['IN', 'tmc.balance.id_specialist', $id_specialist],
                    ],
                ]
            ])
            ->indexBy('id')
            ->asArray()
            ->all();
    }

    /**
     * Подгружаем информацию о выбранных ВНЕбалансовых ТМЦ
     *
     * @param $other_tmcs_ids
     */
    protected function loadOtherTmsInfo($other_tmcs_ids)
    {
        $this->_other_tmc_info = TmcBase::find()
            ->select([
                'tmc.tmc.id',
                'tmc.tmc.type',

                'tmc.tmc.id AS id_tmc',
                'tmc.tmc.type AS type_tmc',
                'tmc.tmc.is_uncountable'
            ])
            ->with([
                'dosages' => function ($query) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query
                        ->select([
                            'id',
                            'id_tmc',
                            'type_tmc',
                            'dosage',
                        ])
                        ->indexBy('id');
                },
            ])
            ->where([
                'IN',
                'tmc.tmc.id',
                $other_tmcs_ids
            ])
            ->indexBy('id_tmc')
            ->asArray()
            ->all();

    }

    /**
     * Осуществляет перевод с баланса организации на баланс спеца
     * @param int $id_visit_service
     * @param array $tmc
     * @return mixed|null
     * @throws Exception
     */
    protected function makeTransfer(int $id_visit_service,array $tmc)
    {
        try {
            $balance_actions_model = new BalanceActionSaveModel();
            $params = [
                'action' => [
                    'to_id_organization' => $this->visit->specialists->id_organization,
                    'to_id_specialist' => $this->visit->specialists->id
                ],
                'items' => [
                    ['row_id' => 1,
                        'id_balance_tmc' => $tmc['id_balance_tmc'],
                        'count_selected' => $tmc['count'],]
                ]
            ];
            $transfer_result = $balance_actions_model->transferWithinVisit($params['action'], $params['items'], $id_visit_service);

            // Через BalanceAction не можем узнать, на какой баланс прилетело ТМЦ
            $balance = Balance::find()
                ->alias('b')
                ->leftJoin('tmc.balance_flow bf', 'b.id = bf.id_tmc_balance')
                ->where([
                    'bf.id_balance_action' => $transfer_result->id,
                    'bf.flow_type' => BalanceFlow::FLOW_TYPE_INCREASE
                ])
                ->one();
            return $balance->id;
        } catch (\Exception $e) {
            throw new Exception('Ошибка при передаче на баланс специалиста с баланса организации: ' . $e->getMessage() . ' ');
        }
    }

    /**
     * Переводы с баланса организации, которые надо удалить как старые записи
     * @param int $id_visit_service
     * @return array|\yii\db\ActiveRecord[]
     */
    private function transfersToDelete(int $id_visit_service){
        $balance_action_ids = BalanceAction::find()
            ->select(['ba.id'])
            ->alias('ba')
            ->leftJoin(BalanceFlow::tableName() . ' bf', 'ba.id = bf.id_balance_action')
            ->where([
                'bf.id_visit_service' => $id_visit_service,
                //Сейчас есть только перевод, но мало ли
                'ba.status'           => BalanceAction::STATUS_COMPLETED,
                'ba.action'           => BalanceAction::ACTION_TRANSFER_TO_BALANCE,
            ])
            ->distinct()
            ->asArray()
            ->all();

        return $balance_action_ids;
    }
}
