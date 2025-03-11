<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.04.19
 * Time: 13:47
 */

namespace app\modules\adminv\controllers\statistics;

use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceFlow;
use app\modules\adminv\models\export\BalanceReportExport;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Отчет по использованию препаратов
 *
 * Class BalanceTmcReportController
 * @package app\modules\adminv\controllers\statistics
 */
class BalanceTmcReportController extends StaticticsController
{
    /**
     * @param string $from
     * @param string $to
     * @param array $organizations
     * @return Query
     * @throws \yii\db\Exception
     */
    private function buildQuery($from, $to, $organizations)
    {
        $countTmcQuery = Balance::find()
            ->select([
                'id_organization as org',
                'id_tmc as bid_tmc',
                'sum(count) as bsum'
            ])
            ->groupBy([
                'id_organization',
                'id_tmc'
            ]);

        $fromQuery = BalanceFlow::find()
            ->alias('bf')
            ->select([
                'row_number() over (order by o.short_name asc, tt.name) as id',
                'b.id_organization',
                'o.short_name',
                'fa.id_area',
                '"areas"."name" as area_name',
                'tt."name" as drug_name',
                'tt.id_measure',
                'measures.name',
                'tbf.bsum as sum',
                new Expression('sum(case when bf.created_at::date >= :from and (ba.status = \'C\' or (bf.id_visit_service is not null and v.channel not in (44, 45, 46)) or (bf.id_balance_action isnull)) then (case
                                                       when bf.flow_action = \'income\' then -(bf.count) else bf.count end) else 0 end) as balance_before', [':from' => $from]),
                new Expression('sum(case when (bf.flow_action = \'income\' and ((ba.status = \'C\'
             and (ba.from_id_organization != ba.to_id_organization or ba.from_id_organization is null)) or bf.id_balance_action isnull) and bf.created_at::date between :from and :to) then bf.count else 0 end) as income', [':from' => $from, ':to' => $to]),
                new Expression('sum(case when (ba.status = \'C\' and ba.action ilike \'%write_off%\' and bf.created_at::date between :from and :to) then bf.count else 0 end) as outcome', [':from' => $from, ':to' => $to]),
                new Expression('sum(case when (bf.flow_action ilike \'expense%\' and ba.action ilike :transfer and ba.status = \'C\' and (ba.from_id_organization != ba.to_id_organization) and bf.created_at::date between :from and :to) then bf.count else 0 end) as transfer', [':from' => $from, ':to' => $to, ':transfer' => 'transfer_to_balance']),
                new Expression('sum(case when (bf.flow_action ilike \'expense%\' and vgs.id notnull and bf.created_at::date between :from and :to) then bf.count else 0 end) as used', [':from' => $from, ':to' => $to]),
                new Expression('sum(case when bf.created_at::date > :to and (ba.status = \'C\' or (bf.id_visit_service is not null and v.channel not in (44, 45, 46)) or (bf.id_balance_action isnull)) then (case
                                                                                                              when bf.flow_action = \'income\' then -(bf.count) else bf.count end) else 0 end) as balance_after', [':to' => $to])
            ])
            ->leftJoin('tmc.balance b', 'b.id = bf.id_tmc_balance')
            ->leftJoin('tmc.balance_action ba', 'ba.id = bf.id_balance_action')
            ->leftJoin('tmc.tmc tt', 'tt.id = b.id_tmc')
            ->leftJoin('visits_gov_services vgs', 'vgs.id = bf.id_visit_service')
            ->leftJoin('organizations o', 'o.id = b.id_organization')
            ->leftJoin('fias_addresses fa', 'fa.id = o.id_fias_address')
            ->leftjoin('measures', ['measures.id' => new Expression('tt.id_measure')])
            ->leftjoin('areas', ['areas.id' => new Expression('fa.id_area')])
            ->leftJoin(['tbf' => $countTmcQuery], 'tbf.bid_tmc = tt.id and tbf.org = b.id_organization')
            ->leftJoin('visits v', 'v.id = vgs.id_visit')
            ->andWhere([
                'or',
                ['not in', 'v.channel', [44, 45, 46]],
                ['v.channel' => null]
            ])
            ->andWhere(new Expression('tt.name is not null'))
            ->andWhere(['b.type_tmc' => 'drug'])
            ->groupBy([
                'b.id_organization',
                'o.id',
                'o.short_name',
                'fa.id_area',
                'tt.name',
                'area_name',
                'measures.name',
                'tt.id_measure',
                'tbf.bsum'
            ])
            ->orderBy([
                'o.short_name' => SORT_ASC,
                'tt.name' => SORT_ASC,
            ]);

        if(!empty($organizations)) {
            $fromQuery->andWhere(['b.id_organization' => $organizations]);
        }

        $mainQuery = (new Query())
            ->select([
                't.*'
            ])
            ->from(['t' => $fromQuery])
            ->where([
                'or',
                ['>', 't.income', 0],
                ['>', 't.outcome', 0],
                ['>', 't.transfer', 0],
                ['>', 't.used', 0],
                ['>', new Expression('"t"."sum" + "t"."balance_before"'), 0],
                ['>', new Expression('"t"."sum" + "t"."balance_after"'), 0],
            ])
            ->orderBy([
                't.short_name' => SORT_ASC,
                't.name' => SORT_ASC,
            ]);

        return $mainQuery;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->organizations);

        $limit = 100;
        $dataProvider = new ActiveDataProvider([
            'query' => $data,
            'pagination' => [
                'defaultPageSize' => $limit,
                'pageSizeLimit' => false,
            ],
        ]);

        $rows = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        return $this->render('index', [
            'rows' => $rows,
            'pagination' => $pagination,
            'data' => $data,
            'organizations' => $this->organizationsOptionsNoShelters(),
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $mainQuery = $this->buildQuery($this->from, $this->to, $this->organizations)->all();
        $exportedReport = new BalanceReportExport();
        $exportedReport->export($mainQuery, "Отчет по использованию препаратов c {$this->from} по {$this->to}.xls", $this->from, $this->to);
    }
}
