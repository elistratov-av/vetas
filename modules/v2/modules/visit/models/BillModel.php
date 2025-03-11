<?php

namespace app\modules\v2\modules\visit\models;

use app\models\db\Discount;
use app\models\db\Organizations;
use app\models\db\OrganizationServicesWithoutMarkUp;
use app\models\db\VisitPrice;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitsGovServices;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class BillModel
{
    /**
     * @var integer
     */
    protected $id_visit;

    /**
     * @var Visits
     */
    protected $visit;

    /**
     * @var array
     */
    protected $balance_tmc;

    /**
     * @var array
     */
    protected $services;

    /**
     * @var integer
     */
    protected $id_discount;

    /**
     * @var Discount
     */
    protected $discount;

    /**
     * @var array
     */
    protected $applied_discounts_balance_tmc;

    /**
     * @var integer[]
     */
    protected $applied_discounts_balance_tmc_ids = [];

    /**
     * @var boolean
     */
    protected $ignore_night_mark_up_ratio;

    /**
     * @var array
     */
    protected $applied_discounts_services;

    /**
     * @var integer[]
     */
    protected $applied_discounts_services_ids = [];

    /**
     * @var Organizations
     */
    protected $organization;

    /**
     * @var VisitPrice
     */
    protected $stored_price;

    /**
     * @var integer
     */
    protected $petId = null;

    /**
     * BillModel constructor.
     *
     * @param $id_visit
     * @param $id_discount
     * @param $applied_discounts_balance_tmc
     * @param $applied_discounts_services
     * @param $ignore_night_mark_up_ratio
     * @throws BadRequestHttpException
     */
    public function __construct($id_visit, $id_discount, $applied_discounts_balance_tmc, $applied_discounts_services, $ignore_night_mark_up_ratio = false, $petId = null)
    {
        /*
         * id_visit и id_discount
         */
        if (!is_numeric($id_visit)) {
            throw new BadRequestHttpException('ID визита должен быть числом');
        }

        if (!empty($id_discount) && !is_numeric($id_discount)) {
            throw new BadRequestHttpException('ID скидки должен быть числом');
        }

        $this->id_visit = $id_visit;
        $this->id_discount = $id_discount;
        $this->applied_discounts_balance_tmc = $applied_discounts_balance_tmc;
        $this->applied_discounts_services = $applied_discounts_services;
        $this->ignore_night_mark_up_ratio = $ignore_night_mark_up_ratio;
        $this->petId = $petId;
        $this->findVisit();

        /*
         * Для сохраненных - смотрим сохраненные скидки
         */
        if ($this->isPaidVisit()) {
            if(!$this->findStoredPrice()){
                $this->findDiscount();
                $this->findOrganization();
            }
        } else {
            $this->findDiscount();
            $this->findOrganization();
        }

        if ($this->isPaidVisit() == true && (!empty($id_discount) || !empty($applied_discounts_balance_tmc) || !empty($applied_discounts_services))) {
            throw new BadRequestHttpException('Данный визит был оплачен и изменить стоимость нельзя');
        }

        /*
         * Услуги и балансовые тмц
         */
        $this->findBalanceTMC();
        $this->findServices();

        /*
         * Валидация примененных скидок
         */
        $this->validateSelectedDiscountsServices();
        $this->validateSelectedDiscountsBalanceTmc();
    }

    /**
     * Возвращает чек
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function getBill()
    {
        /*
         * Значения в зависимости от того оплачен или нет
         */
        if ($this->isPaidVisit() && $this->stored_price) {
            $discount = $this->stored_price->discount ?? null;
            $is_night_visit = (bool)$this->stored_price->night_mark_up_ratio ?? false;
            $night_mark_up_ratio = $this->stored_price->night_mark_up_ratio ? (float)$this->stored_price->night_mark_up_ratio : 1;
            $price = $this->stored_price;
        } else {
            $discount = $this->discount;
            $discount_rate = $this->getDiscountRate();
            $is_night_visit = $this->isNightVisit();
            $night_mark_up_ratio = $this->getMarkUpRatio();

            $price = $this->calcBill(
                $discount_rate,
                $is_night_visit,
                $night_mark_up_ratio
            );
        }

        /*
         * Чек
         */

        return [
            'price'               => [
                'service'     => [
                    'total'    => (float)$price->service_total ?? 0,
                    'discount' => sprintf("%+.2f", $price->service_discount ?? 0),
                ],
                'balance_tmc' => [
                    'total'    => (float)$price->balance_tmc_total ?? 0,
                    'discount' => sprintf("%+.2f", $price->balance_tmc_discount ?? 0),
                ],
                'sum'         => [
                    'total'               => (float)$price->price ?? 0,
                    'total_with_discount' => (float)$price->price_with_discount ?? 0,
                ]
            ],
            'is_night_visit'      => $is_night_visit,
            'is_paid'             => $this->visit->is_paid,
            'balance_tmc'         => $this->balance_tmc,
            'services'            => $this->services,
            'discount'            => $discount,
            'night_mark_up_ratio' => $night_mark_up_ratio,
        ];
    }

    /**
     * Ночной коэффициент для услуг у организации
     *
     * @return float
     */
    protected function getMarkUpRatio()
    {
        if ($this->ignore_night_mark_up_ratio) {
            return (float)1;
        }

        return (float)$this->organization->mark_up_ratio ?? 1;
    }

    /**
     * Подсчет цен
     *
     * @param $discount_rate
     * @param $is_night_visit
     * @param $night_mark_up_ratio
     * @return VisitPrice
     */
    protected function calcBill($discount_rate, $is_night_visit, $night_mark_up_ratio)
    {
        /*
         * Для простоты и однотипности используем модель VisitPrice
         */

        $result = new VisitPrice();
        /*
         * Считаем все услуги
         */
        foreach ($this->services as &$service) {
            if ($this->visit->id != $service['id_visit']) continue;
            $sum = (float)($service['service']['price'] * (int)$service['count']);

            if ($this->isPaidVisit()) {
                /*
                 * Для оплаченного визита ориентируемся на сохраненные значения
                 */
                $need_apply_night_mark_up = ($is_night_visit && $service['apply_night_discount']);
                $need_apply_discount = $service['apply_discount'];
            } else {
                /*
                 * Для НЕоплаченного - на входные параметры
                 */
                $need_apply_night_mark_up = ($is_night_visit && $service['service']['exclude_night_mark_up'] != 1);
                $need_apply_discount = in_array($service['id'], $this->applied_discounts_services_ids);

                // попросили выводить выбранные значения
                $service['apply_night_discount'] = $need_apply_night_mark_up;
                $service['apply_discount'] = $need_apply_discount;
            }

            if ($need_apply_night_mark_up && $need_apply_discount) {
                /*
                 *  Ночная наценка и скидка
                 */
                $night_sum = ($sum * ($night_mark_up_ratio - 1));
                $result->service_discount += $night_sum - ($sum * $night_mark_up_ratio * $discount_rate);
            } elseif ($need_apply_night_mark_up) {
                /*
                 * Только ночная наценка
                 */
                $result->service_discount += ($sum * ($night_mark_up_ratio - 1));
            } elseif ($need_apply_discount) {
                /*
                 * Только скидка
                 */
                $result->service_discount -= (float)($sum * $discount_rate);
            }

            $result->service_total += $sum;
        }
        unset($service);

        /*
         * Считаем все ТМЦ
         */
        foreach ($this->balance_tmc as &$balance_tmc) {
            $sum = $balance_tmc['price'];
            $result->balance_tmc_total += $sum;

            if ($this->isPaidVisit()) {
                /*
                 * Для оплаченного визита ориентируемся на сохраненные значения
                 */
                $need_apply_discount = $balance_tmc['apply_discount'];
            } else {
                /*
                 * Для НЕоплаченного - на входные параметры
                 */
                $need_apply_discount = in_array(
                    $balance_tmc['id'],
                    $this->applied_discounts_balance_tmc_ids
                );

                // попросили выводить выбранные значения
                $balance_tmc['apply_discount'] = $need_apply_discount;
            }

            unset($balance_tmc);

            // скидка
            if ($need_apply_discount) {
                $result->balance_tmc_discount -= (float)($sum * $discount_rate);
            }
        }

        /*
         * Округление
         */
        $result->balance_tmc_total = round($result->balance_tmc_total, 2);
        $result->balance_tmc_discount = round($result->balance_tmc_discount, 2);
        $result->service_discount = round($result->service_discount, 2);

        /*
         * Итог
         */
        $result->price = round($result->service_total + $result->balance_tmc_total, 2);

        $discount_sum = $result->service_discount + $result->balance_tmc_discount;
        $result->price_with_discount = round($result->price + $discount_sum, 2);

        /*
         * Если
         * - визит не попадает в ночное время,
         * - или настройка у организации отключена
         * - или оно не задано вовсе
         * ТО night_mark_up_ratio = NULL
         */
        $result->night_mark_up_ratio = ($is_night_visit == true) ? $night_mark_up_ratio : null;

        return $result;
    }

    /**
     * Сохраняем выбранные скидки
     *
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function discountSave()
    {
        if ($this->isPaidVisit() == true) {
            throw new BadRequestHttpException('Данный визит был оплачен и изменить стоимость нельзя');
        }

        $discount_rate = $this->getDiscountRate();
        $is_night_visit = $this->isNightVisit();
        $night_mark_up_ratio = $this->getMarkUpRatio();

        $visit_price = $this->calcBill(
            $discount_rate,
            $is_night_visit,
            $night_mark_up_ratio
        );

        $visit_price->id_visit = $this->id_visit;
        $visit_price->id_discount = $this->id_discount;

        /*
         * Сохраняем цены и выбор
         */
        VisitPrice::getDb()->beginTransaction();

        if (!$visit_price->save()) {
            $errors = $visit_price->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении скидки' : implode("\n", array_values($errors)));
        }

        /*
         * Для балансовых тмц надо только проставить применение скидки
         */
        if (!empty($this->discount) && !empty($this->applied_discounts_balance_tmc)) {
            $rows_num = VisitServiceTmc::updateAll(
                ['apply_discount' => true],
                [
                    'AND',
                    ['id_visit' => $this->id_visit],
                    ['IN', 'id', $this->applied_discounts_balance_tmc_ids],
                ]
            );

            if (count($this->applied_discounts_balance_tmc) != $rows_num) {
                VisitPrice::getDb()->transaction->rollBack();
                throw new BadRequestHttpException('Неизвестная ошибка при сохранении скидок #001');
            }
        }

        /*
         * Ищем услуги которым надо проставить флаг ночная наценка
         */
        $night_services_ids = ($is_night_visit) ? $this->getGovServicesAllowedNightMarkUp() : [];

        $applyDiscounts = (!empty($this->applied_discounts_services) || $is_night_visit);

        /*
         * Выбираем все по id визата
         */
        $visit_gov_services = VisitsGovServices::find()
            ->where(['id_visit' => $this->id_visit])
            ->with('service')
            ->all();

        foreach ($visit_gov_services as $visit_gov_service) {
            if ($applyDiscounts === true) {
                /*
                 * Для услуг надо проставить и скидки и флаг ночного повышения
                 */
                /** @var VisitsGovServices $visit_gov_service */
                $visit_gov_service->apply_discount = in_array(
                    $visit_gov_service->id, $this->applied_discounts_services_ids
                );
                $visit_gov_service->apply_night_discount = in_array(
                    $visit_gov_service->id, $night_services_ids
                );
            }

            if ($visit_gov_service->service !== null && $visit_gov_service->service->price !== null) {
                $visit_gov_service->price = $visit_gov_service->service->price;
                $price_with_discount = $visit_gov_service->service->price;
                if ($applyDiscounts === true) {
                    if ($visit_gov_service->apply_discount === true && $visit_gov_service->apply_night_discount === true) {
                        $price_with_discount = round($price_with_discount * $night_mark_up_ratio * (1 - $discount_rate), 2);
                    } elseif ($visit_gov_service->apply_night_discount === true) {
                        $price_with_discount = round($price_with_discount * $night_mark_up_ratio, 2);
                    } elseif ($visit_gov_service->apply_discount === true) {
                        $price_with_discount = round($price_with_discount * (1 - $discount_rate), 2);
                    }
                }
                $visit_gov_service->price_with_discount = $price_with_discount;
            }

            if (!$visit_gov_service->save()) {
                VisitPrice::getDb()->transaction->rollBack();
                $errors = $visit_price->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении скидки' : implode("\n", array_values($errors)));
            }
        }

        VisitPrice::getDb()->transaction->commit();
    }

    /**
     * Возвращает массив id услуг, к которым возможно применение ночного тарифа
     * ВНИМАНИЕ ! Не проверяет условие что визит можно считать ночным
     *
     * @return array
     */
    protected function getGovServicesAllowedNightMarkUp()
    {
        $night_services_ids = [];
        foreach ($this->services as $service) {
            if ($service['service']['exclude_night_mark_up'] != 1) {
                $night_services_ids[] = (int)$service['id'];
            }
        }

        return $night_services_ids;
    }

    /**
     * Возвращает значение скидки
     *
     * @return float|int
     */
    protected function getDiscountRate()
    {
        if (!empty($this->discount->value)) {
            return $this->discount->value / 100;
        }

        return 0;
    }

    /**
     * Подгружаем балансовые ТМЦ привязанные к приему
     */
    protected function findBalanceTMC()
    {
        $this->balance_tmc = VisitServiceTmc::find()
            ->select([
                'public.visit_service_tmc.*',
                new Expression('COALESCE(tmc.dosages.name, public.measures.name) AS unit'),
                'tmc.tmc.name',
                'public.visit_service_tmc_pet.id_pet as pet'
            ])
            ->where([
                'AND',
                ['public.visit_service_tmc.id_visit' => $this->id_visit],
                ['IS NOT', 'id_balance_tmc', null]
            ])
            // UNIT
            ->leftJoin(
                'tmc.balance',
                'tmc.balance.id = visit_service_tmc.id_balance_tmc'
            )
            ->leftJoin('tmc.tmc',
                'tmc.balance.id_tmc = tmc.tmc.id AND tmc.balance.type_tmc = tmc.tmc.type'
            )
            ->leftJoin('public.measures',
                'tmc.tmc.id_measure = public.measures.id'
            )
            // DOSAGE
            ->leftJoin(
                'tmc.dosages',
                'tmc.dosages.id = visit_service_tmc.id_dosage'
            )
            ->leftJoin(
                'public.visit_service_tmc_pet',
                'public.visit_service_tmc_pet.id_visit_service_tmc = visit_service_tmc.id'
            )
            ->asArray()
            ->all();
    }

    /**
     * Подгружаем услуги привязанные к приему
     */
    protected function findServices()
    {
        $id_organization = $this->visit->id_organization;
        $this->services = VisitsGovServices::find()
            ->with([
                'service' => function ($query) use ($id_organization) {
                    /** @var $query ActiveQuery * */
                    /*
                     * Подзапрос на получение флага,
                     * указывающего исключать или нет из списка ночного увеличения стоимости услугу
                     */
                    $exclude_night_mark_up_query = OrganizationServicesWithoutMarkUp::find()
                        ->select(
                            new Expression('1')
                        )
                        ->where([
                            'AND',
                            ['organization_services_without_mark_up.id_organization' => $id_organization],
                            new Expression('organization_services_without_mark_up.id_service = gov_services.id'),
                        ]);

                    /*
                     * Основной запрос
                     */
                    $query
                        ->select('*')
                        ->addSelect([
                            'exclude_night_mark_up' => $exclude_night_mark_up_query
                        ]);
                }
            ])
            ->with('service.serviceMeasures')
            ->where([
                'id_visit' => $this->id_visit,
            ]);
            if ($this->petId) $this->services = $this->services->where(['id_pet' => $this->petId]);

            $this->services = $this->services->asArray()->all();
    }

    /**
     * Подгружаем визит
     *
     * @throws BadRequestHttpException
     */
    protected function findVisit()
    {
        $this->visit = Visits::find()
            ->where(['id' => $this->id_visit])
            ->one();

        if (empty($this->visit)) {
            throw new BadRequestHttpException('Указанный прием не найден');
        }
    }

    /**
     * Подгружаем скидку
     *
     * @throws BadRequestHttpException
     */
    protected function findDiscount()
    {
        if (empty($this->id_discount) && (!empty($this->applied_discounts_services) || !empty($this->applied_discounts_services))) {
            throw new BadRequestHttpException('Укажите id_discount');
        }

        if (empty($this->id_discount)) {
            return;
        }

        $this->discount = Discount::findOne(['id' => $this->id_discount]);

        if (empty($this->discount)) {
            throw new BadRequestHttpException('Указанная скидка не найдена');
        }

        if ($this->discount->is_deleted) {
            throw new BadRequestHttpException('Указанная скидка была удалена');
        }

        if (!in_array($this->discount->id_organization, Organizations::orgTreeIds($this->visit->id_organization))
            && !$this->discount->is_system_discount) {
            throw new BadRequestHttpException('Скидка должна принадлежать организации, указанной в визите, либо одной из её дочерних');
        }
    }

    /**
     * Ищем сохраненные цены
     */
    protected function findStoredPrice(): bool
    {
        $this->stored_price = VisitPrice::find()
            ->where(['id_visit' => $this->id_visit])
            ->with('discount')
            ->one();

        return (bool)$this->stored_price;
    }

    /**
     * Это оплаченный визит
     *
     * @return bool
     */
    protected function isPaidVisit()
    {
        return $this->visit->is_paid;
    }

    /**
     * Подгружаем организацию
     *
     * @throws BadRequestHttpException
     */
    protected function findOrganization()
    {
        if (empty($this->visit->id_organization)) {
            throw new BadRequestHttpException('У визита не указан id организации');
        }

        $this->organization = Organizations::findOne(['id' => $this->visit->id_organization]);

        if (empty($this->organization)) {
            throw new BadRequestHttpException('Организация визита не найдена');
        }
    }

    /**
     * Вычисляем, ночной ли это визит
     *
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function isNightVisit()
    {
        if ($this->ignore_night_mark_up_ratio || $this->organization->mark_up_flag !== true) {
            return false;
        }

        /*
         * Для новых (без fact_start_time) считаем, что он не ночной
         */
        $visit_start_stamp = $this->getVisitStartTime();
        if (empty($visit_start_stamp)) {
            return false;
        }

        /*
         * Проверим корректность заполнения, а то остальной алгоритм развалится
         */
        if ($this->organization->mark_up_flag == true && (
                empty($this->organization->mark_up_to_time) || empty($this->organization->mark_up_to_time) ||
                empty($this->organization->mark_up_ratio)
            )) {
            throw new BadRequestHttpException('У организации неверно заполнены поля ночного тарифа');
        }

        /*
         * Кастуем из дат и времен числовые значения
         */
        $time_from = (int)\Yii::$app->formatter->asTime(
            $this->organization->mark_up_from_time,
            'php:His'
        );

        $time_to = (int)\Yii::$app->formatter->asTime(
            $this->organization->mark_up_to_time,
            'php:His'
        );

        $time_start_visit = (int)\Yii::$app->formatter->asTime(
            $visit_start_stamp,
            'php:His'
        );

        /*
         * Диапазон переходит через полночь
         */
        if ($time_from >= $time_to) {
            // между time_from и 24:00:00
            if ($time_start_visit >= $time_from && $time_start_visit < 240000) {
                return true;
            } // между 0 и time_to
            elseif ($time_start_visit > 0 && $time_start_visit <= $time_to) {
                return true;
            } else {
                return false;
            }
        } /*
         * Диапазон не проходит через полночь
         */
        else {
            if ($time_start_visit >= $time_from && $time_start_visit <= $time_to) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Получение даты фактического начала визита (Unix timestamp)
     *
     * @return false|int
     */
    protected function getVisitStartTime()
    {
        if (!empty($this->visit->fact_start_dttm)) {
            return strtotime($this->visit->fact_start_dttm);
        }

        return false;
    }

    /**
     * Валидация выбранных скидок для услуг
     *
     * @throws BadRequestHttpException
     */
    protected function validateSelectedDiscountsServices()
    {
        if (empty($this->applied_discounts_services)) {
            return;
        }

        if (!empty($this->applied_discounts_services) && !is_array($this->applied_discounts_services)) {
            throw new BadRequestHttpException('applied_discounts_services должен быть массивом');
        }

        /*
         * Список привязанных к приему услуг
         */
        $services_ids = ArrayHelper::getColumn($this->services, 'id');

        foreach ($this->applied_discounts_services as $applied_discounts) {

            if (!array_key_exists('id', $applied_discounts) || !is_numeric($applied_discounts['id'])) {
                throw new BadRequestHttpException('applied_discounts_services имеет ошибочный формат');
            }

            if (array_search($applied_discounts['id'], $services_ids) === false) {
                throw new BadRequestHttpException('applied_discounts_services содержит id, которого нет в приеме');
            }

            $this->applied_discounts_services_ids[] = (int)$applied_discounts['id'];
        }

        if (count($this->applied_discounts_services_ids) !== count($this->applied_discounts_services)) {
            throw new BadRequestHttpException('applied_discounts_services имеет дублирующиеся id');
        }
    }

    /**
     * Валидация выбранных скидок для балансовых ТМЦ
     *
     * @throws BadRequestHttpException
     */
    protected function validateSelectedDiscountsBalanceTmc()
    {
        if (empty($this->applied_discounts_balance_tmc)) {
            return;
        }

        if (!empty($this->applied_discounts_balance_tmc) && !is_array($this->applied_discounts_balance_tmc)) {
            throw new BadRequestHttpException('applied_discounts_balance_tmc должен быть массивом');
        }

        /*
         * Список привязанных к приему балансовых тмц
         */
        $tmc_ids = ArrayHelper::getColumn($this->balance_tmc, 'id');

        foreach ($this->applied_discounts_balance_tmc as $applied_discounts) {
            if (!array_key_exists('id', $applied_discounts) || !is_numeric($applied_discounts['id'])) {
                throw new BadRequestHttpException('applied_discounts_balance_tmc имеет ошибочный формат');
            }

            if (array_search($applied_discounts['id'], $tmc_ids) === false) {
                throw new BadRequestHttpException('applied_discounts_balance_tmc содержит id, которого нет в приеме');
            }

            $this->applied_discounts_balance_tmc_ids[] = (int)$applied_discounts['id'];
        }

        if (count($this->applied_discounts_balance_tmc) !== count($this->applied_discounts_balance_tmc_ids)) {
            throw new BadRequestHttpException('applied_discounts_balance_tmc имеет дублирующиеся id');
        }
    }
}
