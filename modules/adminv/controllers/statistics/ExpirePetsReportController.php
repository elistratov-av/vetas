<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.19
 * Time: 17:39
 */

namespace app\modules\adminv\controllers\statistics;

use app\models\db\Pets;
use app\modules\adminv\models\export\ExpirePetsReportExport;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Отчет о снятии с учета
 *
 * Class ExpirePetsReportController
 * @package app\modules\adminv\controllers\statistics
 */
class ExpirePetsReportController extends StaticticsController
{
    /**
     * @var array
     */
    public $species;
    /**
     * @var array
     */
    public $reasons;

    /**
     * @inheritDoc
     */
    public function initVars()
    {
        parent::initVars();

        $this->species = \Yii::$app->request->get('id_species', []);
        $this->reasons = \Yii::$app->request->get('id_reg_expire_reason', []);
    }

    /**
     * @param string $from
     * @param string $to
     * @param array  $organizations
     * @param array  $species
     * @param array  $reasons
     * @return \app\models\db\PetsQuery
     */
    private function buildQuery($from, $to, $organizations, $species, $reasons)
    {
        $query = Pets::find()
            ->select([
                'pets.id',
                'pets.name as pet_name',
                'pet_identification.id_ident_type',
                'identification_types.name as ident_name',
                'pet_identification.identification_code',
                'pet_owners.fullname',
                'coalesce(pet_owners.id_fias_address, pet_owners.id_fact_fias_address) as owner_address',
                'fias_addresses.full_address',
                'contacts.name as contact_name',
                'pets.reg_expire_date',
                'pets.id_reg_expire_reason',
                'reg_expire_reasons.name as reason_name',
                'pets.id_species',
                'species.name as spec_name',
                'pets.id_reg_organization',
                'organizations.short_name',
            ])
            ->joinWith([
                'owner.fias_addresses',
                'pet_identification',
                'pet_identification.ident_type',
                'owner.phoneContacts',
                'reg_expire_reason',
                'species',
                'regOrganization',
            ], false)
            ->andWhere(['between', 'pets.reg_expire_date', $from, $to])
            ->orderBy([
                'organizations.short_name' => SORT_ASC,
                'spec_name' => SORT_ASC,
                'pet_name' => SORT_ASC,
            ])
            ->asArray();

        if (!empty($organizations)) {
            $query->andWhere(['pets.id_reg_organization' => $organizations]);
        }
        if (!empty($species)) {
            $query->andWhere(['pets.id_species' => $species]);
        }
        if (!empty($reasons)) {
            $query->andWhere(['pets.id_reg_expire_reason' => $reasons]);
        }

        return $query;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->species, $this->reasons);

        $limit = 100;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $limit,
                'pageSizeLimit' => false,
            ],
        ]);

        $rows = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();
        $subtotals = empty($rows) ? [] : $this->calculateSubtotals();

        return $this->render('index', [
            'rows' => $rows,
            'pagination' => $pagination,
            'subtotals' => $subtotals,
            'organizations' => $this->organizationsOptionsNoShelters(),
            'species' => $this->speciesOptions(),
            'reasons' => $this->regExpireReasonsOptions(),
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->species, $this->reasons);
        $data = $query->all();
        // $exportedReport = new ExpirePetsReportExport();
        // $exportedReport->export($data, "Отчет о снятии с учета c {$this->from} по {$this->to}.xlsx", $this->from, $this->to);

        $exporter = new \app\modules\adminv\models\excel\ExpirePetsReportExport([
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        $exporter->export();
    }

    /**
     * @return array
     */
    private function calculateSubtotals()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->species, $this->reasons);
        $query->select(['pets.id_reg_organization', 'pets.id_species']);
        $query->addSelect(new Expression('count(distinct pets.id) as count_pets'));

        $query->orderBy([]);
        $query->groupBy(['pets.id_reg_organization', 'pets.id_species']);

        $result = $query->all();

        $subtotals = ArrayHelper::map(
            $result,
            'id_species',
            'count_pets',
            function ($el) {
                return $el['id_reg_organization'] ?? 0;
            }
        );

        foreach ($subtotals as $id_reg_organization => $subtotal) {
            $subtotals[$id_reg_organization]['total'] = array_sum(array_values($subtotal));
        }
        // $pagination->totalCount дает здесь некорректный результат из-за LEFT JOIN
        // @todo - переопределить $dataProvider или переписать запрос ?
        $subtotals['total'] = array_sum(ArrayHelper::getColumn($subtotals, 'total'));

        return $subtotals;
    }
}
