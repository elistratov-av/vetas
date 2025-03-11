<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 31.08.20
 * Time: 12:06
 */

namespace app\modules\adminv\models\statistic;

use app\models\db\spk\SpkUpdateSubscriptionTask;
use app\models\db\Visits;
use app\modules\admin\data\AdminDataProvider;
use yii\db\Expression;
use yii\db\Query;

class EmailReport extends Visits
{
    protected $from;
    protected $to;
    protected $areas;
    protected $districts;

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
        $mainQuery = (new Query())
            ->select([
                'fias_addresses.id_area',
                'areas.name as area_name',
                'fias_addresses.id_district',
                'districts.name as dist_name',
                'count(distinct po.id) as total_owners',
                'count(distinct case when contacts.entity_id is not null and contacts.id_contact_type = 6 then po.id end) as total_email',
                'count(distinct case when contacts.entity_id > 0 and contacts.id_contact_type != 6 and contacts.confirmed = true then po.id end) as total_confirmed',
                '(count(distinct case when contacts.entity_id > 0 and contacts.id_contact_type != 6 then po.id end) - count(distinct case when contacts.entity_id > 0 and contacts.id_contact_type != 6 and contacts.confirmed = true then po.id end)) as total_unconfirmed',
                'count(distinct v.id_owner) as total_reg_mosru',
                'count(distinct email.id) as total_email_spk',
                'count(distinct push.id) as total_push_spk'
            ])
            ->from('pet_owners po')
            ->leftJoin('fias_addresses', 'fias_addresses.id = coalesce(po.id_fact_fias_address, po.id_fias_address)')
            ->leftJoin('areas', 'areas.id = fias_addresses.id_area')
            ->leftJoin('districts', 'districts.id = fias_addresses.id_district')
            ->leftjoin('contacts', 'contacts.entity_type = \'pet_owner\' and entity_id = po.id')
            ->leftJoin('visits v', 'v.id_owner = po.id and v.channel = 2')
            ->leftJoin('spk.subscription email', 'po.sso_id = email.options->>\'ssoid\' and email.stream = :email', [':email' => 'email'])
            ->leftJoin('spk.subscription push', 'po.sso_id = push.options->>\'ssoid\' and push.stream = :push', [':push' => 'push'])
            ->leftJoin('spk.update_subscription_task ust', 'ust.id = email.id_task and ust.status = :status', [':status' => SpkUpdateSubscriptionTask::STATUS_DONE])
            ->leftJoin('spk.update_subscription_task ustt', 'ustt.id = push.id_task and ust.status = :status', [':status' => SpkUpdateSubscriptionTask::STATUS_DONE])
            ->andWhere(['between', 'po.created_at', $this->from, $this->to])
            ->andWhere(['or',
                ['po.is_main' => true],
                ['po.is_main' => null]])
            ->groupBy([
                'fias_addresses.id_area',
                'area_name',
                'fias_addresses.id_district',
                'dist_name'
            ])
            ->orderBy([
                'area_name' => SORT_ASC,
                'dist_name' => SORT_ASC,
            ]);

        if(!empty($this->areas)) {
            $mainQuery->andWhere(['fias_addresses.id_area' => $this->areas]);
        }
        if(!empty($this->districts)) {
            $mainQuery->andWhere(['fias_addresses.id_district' => $this->districts]);
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
}