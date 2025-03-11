<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.07.19
 * Time: 12:50
 */

namespace app\modules\adminv\models\statistic;

use app\common\models\VisitStatus;
use app\models\db\ShiftType;
use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\models\Species;
use app\modules\admin\models\Visits;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status8021;
use yii\db\Expression;
use yii\db\Query;

class FullVisitsVol2Report extends Visits
{
    protected $from;
    protected $to;
    protected $organizations;
    protected $areas;
    protected $districts;
    protected $species;

    public $visit_types;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['from', 'date', 'format' => 'php:Y-m-d'],
            ['to', 'date', 'format' => 'php:Y-m-d'],
        ];
    }


    /**
     * @return array
     */
    public function search()
    {
        $mosruChannelId = ShiftType::find()
            ->select('id')
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])
            ->scalar();

        if (empty($this->from)) {
            $this->from = date("Y")-5 . '-01-01';
        }
        if (empty($this->to)) {
            $this->to = date('Y-m-d');
        }

        $mainQuery = (new Query())
            ->select([
                'organizations.id',
                'fias_addresses.id_area',
                'areas.name as area_name',
                'fias_addresses.id_district',
                'districts.name as dist_name',
                'organizations.short_name',
                new Expression('(case when species.tech_name = :cat then \'Кошки\' else 
                                                case when species.tech_name = :dog then \'Собаки\' else \'Иные животные\' 
                                                end
                                            end) as spec_name', [':cat' => Species::TECH_NAME_CAT, ':dog' => Species::TECH_NAME_DOG]),
                'count(distinct visits.id) as total_visits',
                new Expression('sum(case when visits.status = :finished then 1 else 0 end) as total_finished', [':finished' => VisitStatus::FINISHED]),
                new Expression('count(distinct case when visits.status = :cancelled and (visits.cancel_initiator != :owner or visits.cancel_initiator is null) then visits.id end) as cancelled_by_clinic', [':cancelled' => VisitStatus::CANCELED, ':owner' => Visits::INITIATOR_IS_OWNER]),
                new Expression('count(distinct case when visits.status = :cancelled and visits.cancel_initiator = :owner then visits.id end) as cancelled_by_owner', [':cancelled' => VisitStatus::CANCELED, ':owner' => Visits::INITIATOR_IS_OWNER]),
                new Expression('sum(case when visits.status = :transferred and visits.channel <> :mosru then 1 else 0 end) as transferred_not_mosru', [':transferred' => VisitStatus::TRANSFER, ':mosru' => $mosruChannelId]),
                new Expression('sum(case when visits.status = :changed and etp.status_log.etp_status = :status1053Clinic and visits.channel = :mosru then 1 else 0 end) as transferred_by_clinic_mosru', [':changed' => VisitStatus::CHANGED, ':status1053Clinic' => Status1053::CODE, ':mosru' => $mosruChannelId]),
                new Expression('sum(case when visits.status = :changed and etp.status_log.etp_status = :status8021Owner and visits.channel = :mosru then 1 else 0 end) as transferred_by_owner_mosru', [':changed' => VisitStatus::CHANGED, ':status8021Owner' => Status8021::CODE, ':mosru' => $mosruChannelId]),

            ])
            ->from('visits')
            ->leftJoin('organizations', 'organizations.id = visits.id_organization')
            ->leftJoin('fias_addresses', 'fias_addresses.id = organizations.id_fias_address')
            ->leftJoin('areas', 'areas.id = fias_addresses.id_area')
            ->leftJoin('districts', 'districts.id = fias_addresses.id_district')
            ->leftJoin('pets', 'pets.id = visits.id_pet')
            ->leftJoin('species', 'species.id = pets.id_species')
            ->leftJoin('etp.status_log', 'etp.status_log.visit_id = visits.id and etp.status_log.etp_status in (\'1053\', \'8021\')')
            ->andWhere(['between', new Expression('coalesce(visits.fact_start_dttm, visits.start_dttm, visits.created_at)::date'), $this->from, $this->to])
            //->andWhere(['visits.type' => $this->visit_types])
            ->groupBy([
                'organizations.id',
                'fias_addresses.id_area',
                'areas.name',
                'fias_addresses.id_district',
                'districts.name',
                'visits.id_organization',
                'organizations.short_name',
                'spec_name',
            ])
            ->orderBy([
                'area_name' => SORT_ASC,
                'dist_name' => SORT_ASC,
                'organizations.short_name' => SORT_ASC,
                'spec_name' => SORT_ASC,
            ]);

        if(!empty($this->organizations)) {
            $mainQuery->andWhere(['organizations.id' => $this->organizations]);
        }
        if(!empty($this->areas)) {
            $mainQuery->andWhere(['fias_addresses.id_area' => $this->areas]);
        }
        if(!empty($this->districts)) {
            $mainQuery->andWhere(['fias_addresses.id_district' => $this->districts]);
        }

        if(!empty($this->species)) {
            $mainQuery->andWhere(['in', new Expression('(case when species.tech_name = :cat then \'Кошки\' else 
                                                case when species.tech_name = :dog then \'Собаки\' else \'Иные животные\' 
                                                end
                                            end)', [':cat' => Species::TECH_NAME_CAT, ':dog' => Species::TECH_NAME_DOG]), $this->species]);
        }

        $data = $mainQuery->all();
        $dataProvider = new AdminDataProvider([
            'pagination' => false,
            'query' => $data,
        ]);

        $returnData = [
            'dataProvider' => $dataProvider,
            'from' => $this->from,
            'to' => $this->to,
            'data' => $data
        ];

        return $returnData;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from): void
    {
        $this->from = $from;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to): void
    {
        $this->to = $to;
    }

    /**
     * @param mixed $organizations
     */
    public function setOrganizations($organizations): void
    {
        $this->organizations = $organizations;
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
     * @param mixed $species
     */
    public function setSpecies($species): void
    {
        $this->species = $species;
    }
}
