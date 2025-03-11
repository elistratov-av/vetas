<?php

namespace app\commands;

use app\common\models\VisitStatus;
use app\models\db\Discount;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitsGovServices;
use app\modules\v2\modules\visit\models\BillModel;
use yii\console\Controller;

/**
 * Закрытие упрощённых вакцинаций с проставлением скидки на услугу и балансовые ТМЦ
 */
class VaccinationJournalController extends Controller
{
    const DAYS_PASS_BEFORE_CLOSE = 3;

    /** @var string  */
    private $numberOfDaysEarlier;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        $dayNumbers = self::DAYS_PASS_BEFORE_CLOSE;
        $this->numberOfDaysEarlier = Date('Y-m-d', strtotime("-$dayNumbers days"));
    }

    /**
     * Закрывает все осмотры из упрощенных вакцинаций (ПП, приюты, обходы)
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCloseAllSimplifiedVisits()
    {
        $this->actionCloseVaccineStationVisits();
        $this->actionCloseFlatVisits();
        $this->actionCloseShelterVisits();
    }

    /**
     * Закрывает осмотры в прививочных пунктах
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCloseVaccineStationVisits()
    {
        $this->closeVisits(Visits::TYPE_VISIT_VC, Discount::VACCINE_STATION_DISCOUNT_NAME);
    }

    /**
     * Закрывает осмотры из поквартирных обходов
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCloseFlatVisits()
    {
        $this->closeVisits(Visits::TYPE_VISIT_VC_DETOUR, Discount::FLAT_DISCOUNT_NAME);
    }

    /**
     * Закрывает осмотры в приютах проставляя
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCloseShelterVisits()
    {
        $this->closeVisits(Visits::TYPE_VISIT_VC_SHELTER, Discount::SHELTER_DISCOUNT_NAME);
    }

    /**
     * Закрытие осмотров определенного типа с проставлением оплаты, а так же скидки на услуги и балансовые ТМЦ
     *
     * @param string $visitType
     * @param string $discountName
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    private function closeVisits(string $visitType, string $discountName)
    {
        /** @var Discount $discount */
        $discount = Discount::find()->where(['name' => $discountName])->one();
        $visits = $this->getVisitsToPay($visitType);

        foreach ($visits as $visitAsArray) {
            /*
            * скидка применятся только С БАЛАНСОВЫМИ ТМЦ
            * и распространяется И НА УСЛУГУ И НА ТМЦ
            */
            $tmc = VisitServiceTmc::find()->where(['id' => $visitAsArray['visit_service_tmc_id']])->asArray()->all();
            $services = VisitsGovServices::find()->where(['id' => $visitAsArray['visit_gov_service_id']])->asArray()->all();
            $billing = new BillModel(
                $visitAsArray['id'],
                $discount->id,
                $tmc,
                $services,
                true);

            $billing->discountSave();

            /** @var Visits $visit */
            $visit = Visits::findOne($visitAsArray['id']);

            $visit->is_paid = true;
            $visit->status = VisitStatus::FINISHED;
            $visit->save(true, ['is_paid', 'status', 'updated_at', 'updated_by']);
        }
    }

    /**
     * Получение осмотров готовых к закрытию (прошло 3 дня)
     *
     * @param string $visitType
     * @return array|\yii\db\ActiveRecord[]
     */
    private function getVisitsToPay(string $visitType)
    {
        if (!in_array($visitType, [Visits::TYPE_VISIT_VC, Visits::TYPE_VISIT_VC_DETOUR, Visits::TYPE_VISIT_VC_SHELTER])) {
            return [];
        }

        return Visits::find()
            ->select(['visits.id id', 'vst.id visit_service_tmc_id', 'vgs.id visit_gov_service_id'])
            ->leftJoin('vaccination_stations vs', 'visits.vaccination_station_id = vs.id')
            ->leftJoin('visits_gov_services vgs', 'visits.id = vgs.id_visit')
            ->leftJoin('visit_price vp', 'visits.id = vp.id_visit')
            ->leftJoin('visit_service_tmc vst', 'visits.id = vst.id_visit AND vst.id_balance_tmc is not null')
            ->where([
                'and',
                ['is_paid' => false],
                ['vp.id' => null],
                ['<=', 'visits.fact_start_dttm', $this->numberOfDaysEarlier],
                ['type' => $visitType],
            ])->asArray()->all();
    }
}
