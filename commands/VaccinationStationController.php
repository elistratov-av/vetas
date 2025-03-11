<?php

namespace app\commands;

use app\common\models\VisitStatus;
use app\models\db\Discount;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitsGovServices;
use app\modules\v2\modules\visit\models\BillModel;
use yii\console\Controller;

class VaccinationStationController extends Controller
{
    /**
     * Закрывает осмотры в прививочных пунктах проставляя оплату и скидку для услуг и балансовых тмц
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionApplyDiscount()
    {
        // Закрываем осомтры через 3 дня после окончания ПП
        $threeDaysEarlier = Date('Y-m-d', strtotime('-3 days'));

        $visitsToPay = Visits::find()
            ->select(['visits.id id', 'vst.id visit_service_tmc_id', 'vgs.id visit_gov_service_id'])
            ->leftJoin('vaccination_stations vs', 'visits.vaccination_station_id = vs.id')
            ->leftJoin('visits_gov_services vgs', 'visits.id = vgs.id_visit')
            ->leftJoin('visit_price vp', 'visits.id = vp.id_visit')
            ->innerJoin('visit_service_tmc vst', 'visits.id = vst.id_visit AND vst.id_balance_tmc is not null')
            ->where(['and',
                ['is_paid' => false],
                ['<=', 'vs.date', $threeDaysEarlier],
                ['type' => Visits::TYPE_VISIT_VC],
                ['vp.id' => null],
        ])->asArray()->all();

        /*
         * должна применяться только С БАЛАНСОВЫМИ ТМЦ
         * и распространяется И НА УСЛУГУ И НА ТМЦ
         */
        /** @var Discount $discount */
        $discount = Discount::find()->where(['name' => Discount::VACCINE_STATION_DISCOUNT_NAME])->one();

        foreach ($visitsToPay as $visitAsArray) {
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
}
