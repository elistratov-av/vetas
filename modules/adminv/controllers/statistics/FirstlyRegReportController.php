<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.05.19
 * Time: 14:49
 */

namespace app\modules\adminv\controllers\statistics;

use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\Pets;
use app\models\db\Species;
use app\modules\adminv\models\export\FirstlyRegReportExport;
use app\modules\admin\models\Organization;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Отчет по первично зарегистрированным владельцам/животным
 *
 * Class FirstlyRegReportController
 * @package app\modules\adminv\controllers\statistics
 */
class FirstlyRegReportController extends StaticticsController
{
    public $areas;
    public $districts;
    public $species;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area');
        $this->districts = \Yii::$app->request->get('id_district');
        $this->species = \Yii::$app->request->get('idSpec');
    }

    /**
     * @param string    $from
     * @param string    $to
     * @param int|int[] $areas
     * @param int|int[] $districts
     * @param int|int[] $organizations
     * @param int|int[] $species
     * @return \app\models\db\PetsQuery
     */
    private function buildQuery($from, $to, $areas, $districts, $organizations, $species)
    {
        $query = Pets::find()
            ->select([
                'pets.id',
                'pet_owners.fullname as ownName',
                new Expression('coalesce(pet_owners.id_fias_address, pet_owners.id_fact_fias_address) as id_fias_address'),
                'ownFias.full_address as ownAddress',
                'main_contacts.name as ownPhone',
                'pets.name as petName',
                'pets.reg_date',
                'species.id as idSpec',
                'species.name as specName',
                new Expression('coalesce(main_identification.identification_code, other_identification.identification_code)') . ' AS identification_code',
                new Expression('coalesce(main_identification.identification_name, other_identification.identification_name) AS "identType"'),
                'pets.id_reg_organization',
                'organizations.short_name',
                'organizations.id_fias_address as orgAddress',
                'orgFias.id_area',
                'orgFias.id_district',
                'areas.name as areaName',
                'districts.name as distName',
            ])
            ->joinWith([
                'owners',
                'species',
                'regOrganization',
            ], false)
            ->leftJoin(
                ['main_identification' => new Expression('(select pet_identification.id_pet, pet_identification.identification_code, identification_types.name AS identification_name
                    from pet_identification
                        left join identification_types on pet_identification.id_ident_type = identification_types.id
                    where pet_identification.main_flag = TRUE)')],
                'pets.id = main_identification.id_pet'
            )
            ->leftJoin(
                ['other_identification' => new Expression('(select distinct on (pet_identification.id_pet) pet_identification.id_pet, pet_identification.identification_code, identification_types.name AS identification_name
                    from pet_identification
                        left join identification_types on pet_identification.id_ident_type = identification_types.id
                    where pet_identification.main_flag = FALSE)')],
                'pets.id = other_identification.id_pet'
            )
            ->leftJoin(
                ['main_contacts' => new Expression('(SELECT "name", "entity_id" FROM "contacts" WHERE "contacts"."main_flag" = TRUE AND "contacts"."id_contact_type" != 6 AND "contacts"."entity_type" = \'pet_owner\')')],
                'pet_owners.id = main_contacts.entity_id'
            )
            ->leftJoin('fias_addresses ownFias', ['ownFias.id' => new Expression('pet_owners.id_fias_address')])
            ->leftJoin('fias_addresses orgFias', ['orgFias.id' => new Expression('organizations.id_fias_address')])
            ->leftJoin('areas', ['orgFias.id_area' => new Expression('areas.id')])
            ->leftJoin('districts', ['orgFias.id_district' => new Expression('districts.id')])
            ->orderBy([
                'areaName' => SORT_ASC,
                'distName' => SORT_ASC,
                'organizations.short_name' => SORT_ASC,
                'specName' => SORT_ASC,
            ])
            ->asArray();

        $query->andWhere(['between', 'pets.reg_date', $from, $to]);
        $query->andWhere(['pets_to_owner.id_owner_type' => 1]);
        $query->andWhere(['pet_owners.is_deleted' => false]);

        if (!empty($organizations)) {
            $query->andWhere(['pets.id_reg_organization' => $organizations]);
        }
        if (!empty($areas)) {
            $query->andWhere(['orgFias.id_area' => $areas]);
        }
        if (!empty($districts)) {
            $query->andWhere(['orgFias.id_district' => $districts]);
        }
        if (!empty($species)) {
            $query->andWhere(['species.id' => $species]);
        }

        return $query;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->organizations, $this->species);

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
            'areas' => $this->areasOptions(),
            'districts' => $this->districtsOptions(),
            'species' => $this->speciesOptions(),
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
        $query = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->organizations, $this->species);

//         $exportedReport = new FirstlyRegReportExport();
//         $exportedReport->export($query, "Отчет по первично зарегистрированным владельцам/животным c {$this->from} по {$this->to}.xls", $this->from, $this->to);
//
        $exporter = new \app\modules\adminv\models\excel\FirstlyRegReportExport([
            'query' => $query,
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
        $query = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->organizations, $this->species);
        $query->select(new Expression('count(*) AS total_specs, "species"."id" AS "idSpec", "pets"."id_reg_organization", "orgFias"."id_area", "orgFias"."id_district"'));

        $query->orderBy([]);
        $query->groupBy(new Expression('"idSpec", id_reg_organization, "orgFias".id_area, "orgFias".id_district'));

        $result = $query->all();

        $subtotals = [];
        $subtotals['perOrg'] = ArrayHelper::map($result, 'idSpec', 'total_specs', 'id_reg_organization');
        $perDist = array_fill_keys(array_unique(ArrayHelper::getColumn($result, 'id_district')), 0);
        foreach ($result as $row) {
            $perDist[$row['id_district']] += $row['total_specs'];
        }
        $subtotals['perDist'] = $perDist;
        $perArea = array_fill_keys(array_unique(ArrayHelper::getColumn($result, 'id_area')), 0);
        foreach ($result as $row) {
            $perArea[$row['id_area']] += $row['total_specs'];
        }
        $subtotals['perArea'] = $perArea;

        return $subtotals;
    }
}
