<?php


namespace app\modules\adminv\models\statistic;


use app\models\db\Pets;
use app\models\db\Species;
use app\modules\admin\data\AdminDataProvider;
use yii\data\Pagination;
use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class UnvaccPetsReport extends Pets
{
    protected $areas;
    protected $districts;

    public $bti_city_area_codes;

    /**
     * @return array
     */
    public function search()
    {
        $mainQuery = Pets::find()
            ->alias('p')
            ->select([
                'row_number() over (order by ea.name, ed.name, po.fullname) as number',
                'p.id as id',
                'ea.id as id_area',
                'ea.name as area_name',
                'ed.id as id_district',
                'ed.name as dist_name',
                'po.fullname as owner_name',
                'reg_fias.full_address as reg_address',
                'fact_fias.full_address as fact_address',
                new Expression('(select c.name 
                                            from contacts c 
                                            where entity_type = \'pet_owner\'
                                            and entity_id = po.id
                                            and id_contact_type < 6
                                            order by main_flag desc, confirmed desc, created_at desc
                                            limit 1) as owner_phone'),
                new Expression('(select c.name 
                                            from contacts c 
                                            where entity_type = \'pet_owner\'
                                            and entity_id = po.id
                                            and id_contact_type = 6
                                            order by main_flag desc, confirmed desc, created_at desc
                                            limit 1) as owner_mail'),
                's.name as species',
                'b.name as breed',
                'p.sex as pet_sex',
                'ident.identification_code as pet_ident',
                'reg_certificates.number as reg_num',
                'p.name as pet_name',
                'prv.date as rab_date',
                'pov.date as lept_date',
            ])
            ->leftJoin('pets_to_owner owner', 'owner.id_pet = p.id and owner.id = (select entr.id from pets_to_owner entr where entr.id_pet = p.id order by id_owner_type asc, entr.id desc limit 1)')
            ->leftJoin('pet_owners po', 'po.id = owner.id_owner')
            ->leftJoin('fias_addresses reg_fias', 'reg_fias.id = po.id_fias_address')
            ->leftJoin('fias_addresses fact_fias', 'fact_fias.id = po.id_fact_fias_address')
            //bti_city_area_code используется как массив
            ->leftJoin('efsp.districts ed', 'ed.bti_city_area_code = coalesce(fact_fias.bti_city_area_code[1], reg_fias.bti_city_area_code[1])')
            ->leftJoin('efsp.districts ea', 'ed.parent_id = ea.id')
            ->leftJoin('reg_certificates', 'reg_certificates.id_pet = p.id')
            ->leftJoin('species s', 's.id = p.id_species')
            ->leftJoin('breeds b', 'b.id = p.id_breed')
            ->leftJoin('pet_identification ident', 'ident.id_pet = p.id and ident.main_flag = true')
            ->leftJoin('pet_rabies_vaccination prv', 'p.id = prv.id_pet and prv.date = (select max(prv2.date) from pet_rabies_vaccination prv2 where prv2.id_pet = p.id)')
            ->leftJoin('pet_other_vaccinations pov', 'pov.id_pet = p.id and pov.date = (select max(pov2.date) from pet_other_vaccinations pov2 inner join tmc.tmc t on t.id = pov2.id_vaccine
          inner join tmc.tmc_to_diseases ttd on ttd.id_tmc = t.id and ttd.type_tmc = \'vaccine\'
          inner join diseases d on d.id = ttd.id_disease and d.name ilike \'%лепто%\' where pov2.id_pet = p.id)')
            ->groupBy([
                'p.id',
                'area_name',
                'dist_name',
                'po.fullname',
                's.name',
                'b.name',
                'prv.date',
                'ea.id',
                'ed.id',
                'reg_fias.full_address',
                'fact_fias.full_address',
                'owner_phone',
                'owner_mail',
                'ident.identification_code',
                'reg_certificates.number',
                'pov.date',
                'prv.date',
            ])
            ->andWhere(['or', 'po.is_main is null', 'po.is_main = true'])
            ->andWhere(['or', 'p.is_main is null', 'p.is_main = true'])
            ->andWhere(['s.tech_name' => [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG]])
            ->andWhere(['p.reg_expire_date' => null])
            ->andWhere(['or', new Expression('now()::date > prv.valid_until'),
                new Expression('now()::date > pov.valid_until'),
                'prv.date is null',
                'CASE WHEN s.tech_name = \'DOG\' THEN pov.date is null ELSE false END'])
            ->orderBy([
                'area_name' => SORT_ASC,
                'dist_name' => SORT_ASC,
                'owner_name' => SORT_ASC
            ])
            ->limit(100);

        if (!empty($this->bti_city_area_codes)) {
            //bti_city_area_code используется как массив в БД
            $bti_codes = array_map(function (&$item) {
                return "'" .  $item . "'";
            }, $this->bti_city_area_codes);
            $bti_code_str = implode(',', $bti_codes);
            if (in_array("'without_bti'", $bti_codes)){
                $mainQuery->andWhere([
                    'or',
                    "coalesce(fact_fias.bti_city_area_code, reg_fias.bti_city_area_code) && ARRAY[$bti_code_str]::character varying[]",
                    "coalesce(fact_fias.bti_city_area_code, reg_fias.bti_city_area_code) isnull"
                ]);
            }
            else {
                $mainQuery->andWhere(
                    "coalesce(fact_fias.bti_city_area_code, reg_fias.bti_city_area_code) && ARRAY[$bti_code_str]::character varying[]"
                );
            }
        }

        $limit = 100;
        $dataProvider = new AdminDataProvider([
            'query' => $mainQuery->limit(100)->asArray(),
            'pagination' => [
                'defaultPageSize' => $limit,
                'pageSizeLimit' => false,
            ],
        ]);

        $cloneData = clone $mainQuery;
        $exportData = $cloneData->limit(1000000)->batch();

        $returnData = [
            'mainQuery' => $mainQuery,
            'dataProvider' => $dataProvider,
            'data' => $exportData
        ];

        return $returnData;
    }

    /**
     * @param mixed $areas
     */
    public function setAreas($areas): void
    {
        $this->areas = $areas;
    }

    /**
     * @param mixed $districts
     */
    public function setDistricts($districts): void
    {
        $this->districts = $districts;
    }

    /**
     * @return array
     */
    public function calculateSubtotals()
    {
        $query = $this->search()['mainQuery'];

        $query->select(['ea.id as id_area_count', 'ed.id as id_dist_count']);
        $query->addSelect(new Expression('count(distinct p.id) as count_pets'));

        $query->orderBy([]);
        $query->groupBy(['id_area_count', 'id_dist_count']);


        $result = $query->asArray()->all();


        $subtotals = ArrayHelper::map(
            $result,
            'id_dist_count',
            'count_pets',
            function ($el) {
                return $el['id_area_count'] ?? 0;
            }
        );

        foreach ($subtotals as $district => $subtotal) {
            $subtotals[$district]['total'] = array_sum(array_values($subtotal));
        }
        // $pagination->totalCount дает здесь некорректный результат из-за LEFT JOIN
        // @todo - переопределить $dataProvider или переписать запрос ?
        $subtotals['total'] = array_sum(ArrayHelper::getColumn($subtotals, 'total'));


        return $subtotals;
    }
}