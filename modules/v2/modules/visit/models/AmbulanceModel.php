<?php

namespace app\modules\v2\modules\visit\models;

use app\common\models\VisitStatus;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\models\db\Visits;
use app\modules\v2\modules\visit\skeletons\visit\Lists;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

/**
 * Class AmbulanceModel
 * @package app\modules\v2\modules\visit\models
 */
class AmbulanceModel extends Model


{
/**
     * @param int    $id_visit
     */
    public function getAmbulance(int $id_visit = null){
        return Visits::find()
        ->select([
            'public.visits.id AS id_visit',
            "public.visits.id_request", 
            "public.visits.id_brigade", 
            "public.visits.call_reason", 
            "br.name AS brigade", 
        ])
        ->leftJoin('brigades br', 'public.visits.id_brigade = br.id')
        ->leftJoin('brigades_specialists brs', 'br.id = brs.id_brigade')
        ->leftJoin('specialists sp', 'brs.id_specialist = sp.id')
        ->leftJoin('users us', 'sp.id_user = us.id')
        ->where([
        'public.visits.type' => 'AMBULANCE',
        'public.visits.id' => $id_visit,
        ])
        ->asArray()
        ->all();
}
    /**
     * @param int    $idOrganization
     * @param string $dateFrom
     * @param int    $daysCount
     * @param int    $page
     * @param int    $limit
     * @param array  $filter
     * @return \app\modules\v2\modules\visit\skeletons\visit\Lists
     */
    public function list(int $idOrganization = null, string $dateFrom = null, int $daysCount = 1, int $page = 1, int $limit = 10, array $filter = []): Lists
    {
        $query = $this->prepareQuery($idOrganization, $dateFrom, $daysCount, $filter);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query->asArray(),
            'pagination' => [
                'defaultPageSize' => $limit,
                'page' => ($page - 1),
            ],
        ]);

        $response = new Lists($dataProvider->getModels(), $dataProvider->getTotalCount());
        $response->countPages($limit);
        // исправление количества визитов без учета работы new Lists (для мобильного приложения)
        // $resultQuery =  $query->asArray()->all();
        // $response->visits= $resultQuery;
        // $response->total_count= count($resultQuery);
        // $response->pages_count= null;

        foreach ($response->visits as $key=>$visit) {
            $name = '';
            $rows = (new \yii\db\Query())
            ->select(['id_specialist'])
            ->from('brigades_specialists')
            ->where(['id_brigade' =>  $visit['id_brigade']])
            ->all();

            foreach ($rows as $row) {
                $specialist =  (new \yii\db\Query())
                ->select(['id_user'])
                ->from('specialists')
                ->where(['id' =>  $row['id_specialist']])
                ->all();

                $user =  (new \yii\db\Query())
                ->select(['fullname'])
                ->from('users')
                ->where(['id' =>  $specialist[0]['id_user']])
                ->all();
                $name = $name.$user[0]['fullname'].', ';
            }

            $visit['brigade_specialists'] = substr($name,0,-2);
            // unset($visit['id']);
            unset($visit['id_brigade']);

            $response->visits[$key] = $visit;
        }

        return $response;
    }

    /**
     * @param int    $idOrganization
     * @param string $dateFrom
     * @param int    $daysCount
     * @param array  $filter
     * @return \yii\db\ActiveQuery
     */

    //  private function prepareQuery()
    // {
    //     return Visits::find()
    //     ->select([
    //         'public.visits.id',
    //         "public.visits.id_request", 
    //         "public.visits.id_brigade", 
    //         "public.visits.call_reason", 
    //         "br.name", 
    //     ])
    //     ->leftJoin('brigades br', 'public.visits.id_brigade = br.id')
    //     ->leftJoin('brigades_specialists brs', 'br.id = brs.id_brigade')
    //     ->leftJoin('specialists sp', 'brs.id_specialist = sp.id')
    //     ->leftJoin('users us', 'sp.id_user = us.id')
    //     ->where([
    //     'public.visits.type' => 'AMBULANCE'
    //     ])
    //     ->andFilterWhere(['>=', 'start_dttm', date('Y-m-d')])
    //     ->andFilterWhere(['<=', 'start_dttm', '2023-03-19 23:59:59'])
    //     ->andFilterWhere( ['public.visits.status' => ['N', 'C', 'W']]);
    // }

    private function prepareQuery($idOrganization, $dateFrom, $daysCount, $filter = [])
    {
        $query = Visits::find()
            ->select([
                'channel AS id_shift_type',
                'status',
                'change_reason',
                'is_paid',
                'start_dttm',
                'ticket_number',
                'fact_start_dttm',
                'fact_end_dttm',
                'duration',
                'cooldown',
                'id_pet',
                'visits.id',
                Visits::tableName() . '.id_owner',
                Visits::tableName() . '.type',
                Visits::tableName() . '.variety',
                Visits::tableName() . '.visit_to_address',
                Visits::tableName() . '.description',
                Visits::tableName() . '.created_at',
                'vs.id_specialist',
                'public.visits.id',
                "public.visits.id_request", 
                "public.visits.id_brigade", 
                "public.visits.call_reason", 
                "br.name AS brigade_name",
            ])
            ->distinct()
            ->where([Visits::tableName() . '.type' => Visits::TYPE_AMBULANCE])
            ->with(['pets' => function ($petsQuery) {
                /* @var $petsQuery ActiveQuery */
                $petsQuery->with(['species' => function ($speciesQuery) {
                    /* @var $speciesQuery ActiveQuery */
                    $speciesQuery->select([
                        'id',
                        'name',
                    ]);
                }]);
            }])
            ->joinWith('visitsSpecialists AS vs', false)
            ->leftJoin('brigades br', 'public.visits.id_brigade = br.id')
            ->leftJoin('brigades_specialists brs', 'br.id = brs.id_brigade')
            ->leftJoin('specialists sp', 'brs.id_specialist = sp.id')
            ->leftJoin('users us', 'sp.id_user = us.id')
            ->with(['specialists' => function ($specialistQuery) {
                /* @var $specialistQuery \yii\db\ActiveQuery */
                $specialistQuery->joinWith('user', false)
                    ->select(array_merge(['specialists.*'], Specialists::personalAttributes()));
            }])
            ->with('owner');

        $dateTimeFrom = ($dateFrom === null) ? new \DateTime() : date_create_from_format('Y-m-d', $dateFrom);
        $from = $dateTimeFrom->format('Y-m-d') . ' 00:00:00';

        $dateTimeTo = $dateTimeFrom->modify('+' . $daysCount . ' days');
        $to = $dateTimeTo->format('Y-m-d') . ' 23:59:59';

        $query->andWhere(['>=', 'start_dttm', $from]);
        $query->andWhere(['<=', 'start_dttm', $to]);

        if (!empty($idOrganization)) {
            $organizations_ids = Organizations::orgTreeIds($idOrganization);
            if (!empty($organizations_ids)) {
                $query->andWhere(['in', Visits::tableName() . '.id_organization', $organizations_ids]);
            }
        }

        if (isset($filter['id_owner'])) {
            $query->andFilterWhere([Visits::tableName() . '.id_owner' => $filter['id_owner']]);
        }
        if (isset($filter['ticket_number'])) {
            $query->andFilterWhere(['ilike', 'ticket_number', $filter['ticket_number']]);
        }
        if (isset($filter['id_specialist'])) {
            $query->andFilterWhere(['vs.id_specialist' => $filter['id_specialist']]);
        }
        if (isset($filter['is_paid']) && \is_bool($filter['is_paid'])) {
            $query->andWhere([Visits::tableName() . '.is_paid' => $filter['is_paid']]);
        }
        if (isset($filter['status']) && \is_array($filter['status'])) {
            $status = [];
            foreach ($filter['status'] AS $filterStatus) {
                $filterStatus = strtoupper($filterStatus);
                if (!\in_array($filterStatus, VisitStatus::getStatusList(), true)) {
                    throw new BadRequestHttpException('Переданный параметр status не валиден');
                }
                $status[] = $filterStatus;
            }
            $query->andWhere(['IN', Visits::tableName() . '.status', $status]);
        }
        $query->orderBy(['start_dttm' => SORT_ASC]);

        return $query;
    }
}
