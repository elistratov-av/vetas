<?php

namespace app\modules\v2\modules\shelter\models;

use app\models\db\Areas;
use app\models\db\Aviary;
use app\models\db\IdentificationTypes;
use app\models\db\Organizations;
use app\models\db\PetHealth;
use app\models\db\PetIdentification;
use app\models\db\PetOwners;
use app\models\db\PetRabiesVaccination;
use app\models\db\PetRefColor;
use app\models\db\Pets;
use app\models\db\ShelterGuests;
use app\models\db\Specialists;
use app\models\db\Users;
use app\modules\admin\models\Organization;
use app\modules\mdm\models\Pet;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use Yii;

/**
 * Class ShelterSearchModel
 * @package app\modules\v2\modules\shelter\models
 */
class ShelterSearchModel extends Model
{
    /** @var Organization */
    private $organization;

    public static function tableName()
    {
        return 'shelter_guests';
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        /** @var $user \app\common\models\UserModel */
        if (!$organization = \Yii::$app->user->getIdentity()->organization) {
            throw new InvalidConfigException();
        }

        $this->organization = $organization;
    }

    /**
     * @param int $page
     * @param int|false $limit
     * @param array $filter
     * @return array
     */
    public function list(int $page = 1, $limit = 10, array $filter = [], $sortBy = null, $sortDesc = false)
    {
        if ($this->organization) {
            $organization_ids = array_keys([$this->organization->id => $this->organization->id]
                + $this->organization->getNestedOrganizations(true));
        }

        $organization_ids = array_merge($organization_ids, $this->organization->getAllChildOrganisations());
        $query = $this->prepareQuery($filter, $organization_ids ?? []);

        if($sortBy == ''){
            $sortBy='arrival_date';
            $sortDesc=true;
        }

        if ($sortBy) {
            switch ($sortBy) {
                case 'status':
                case 'arrival_date':
                    $attribute = 'sub.' . $sortBy;
                    break;
                case 'departure_date':
                    $attribute = 'sub.' . $sortBy;
                    break;
                case 'k_u':
                    $attribute = 'record_id';
                    break;
                case 'aviary':
                    $attribute = 'aviary_id';
                    break;
                case 'birthday_date':
                    $attribute = 'birthday';
                    break;
                case 'area':
                    $attribute = '"sub"."area_name"';
                    break;
                case 'catching_act_number':
                    $attribute = '"sub"."catching_act_number"';
                    break;
                case 'shelter':
                    $attribute = '"sub"."organization_short_name"';
                    break;
                default:
                    $attribute = $sortBy;
            }

            if ($sortBy) {
                $query
                    // ->andWhere(['not', [$attribute => null]])->andWhere(['not', [$attribute => '']])
                    ->orderBy(new Expression("$attribute " . ($sortDesc ? 'DESC' : 'ASC') . ' NULLS LAST'));
            }
        }

        $query
            ->with(['petHealths' => function ($query) {
                return $query
                // ->where(['AND',
                //     ['OR',
                //         ['status' => PetHealth::HEALTH_STATUS_QUARANTINE],
                //         ['status' => PetHealth::HEALTH_STATUS_QUARANTINE_PR],
                //     ],
                //     ['>=', 'date', date('Y-m-d')]
                // ]);
                ;
            }])
            ->with('shelterGuests');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => (($limit === false || $limit === 'false') ? false : [
                'defaultPageSize' => $limit,
                'page' => ($page - 1),
            ]),
        ]);

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("
        SELECT pr.id_pet, max(pr.valid_until) as exp_d FROM pet_rabies_vaccination pr LEFT JOIN shelter_guests ON shelter_guests.id_pet = pr.id_pet WHERE shelter_guests.id_pet is not null GROUP BY pr.id_pet");
        $vactinationQuery = $command->queryAll();

        $models = $dataProvider->getModels();
        foreach ($models as &$model) {
            $model['can_edit'] = ($model['id_created_organization'] == $this->organization->id);
            $model['area_short_name'] = !empty($model['area_short_name'])
                ? $model['area_short_name']
                : $this->parseStringToShortName($model['area_name']);
            // $model['status'] = ShelterGuests::STATUSES[$model['status']] ?? null;
            $model['k_u'] = $model['record_id'] . mb_substr($model['species']['name'], 0, 1) . $model['organization_id'];
            $model['sex'] = Pets::GENDER_TYPES[$model['sex']] ?? null;
            $model['vaccination_last_date']='';
            foreach ($vactinationQuery as $row) {
                if($row['id_pet']==$model['id']){
                    $model['vaccination_last_date']=$row['exp_d'];
                    break;
                }
            }

            if (!empty($model['birthday']) && $birthday = \DateTime::createFromFormat('Y-m-d', $model['birthday'])) {
                $interval = (new \DateTime())->diff($birthday);
                $model['age'] = $interval->y . ' г. ' . $interval->m . ' мес.';
            } else {
                $model['birthday'] = null;
            }

            if (count($model['petHealths'])) {
                $quarantineD = date_create(date('Y-m-d'));
                $quarantineD->modify('-60 day');

                $quarantineD2 = date_create(date('Y-m-d'));
                $quarantineD2->modify('+5 day');

                // обновляем статус с учетом введенных карантинов
                $result_quarantine_to = date_format($quarantineD, 'Y-m-d');
                $result_quarantine_from = date_format($quarantineD2, 'Y-m-d');
                foreach ($model['petHealths'] as $petHealth) {
                    if (strtotime($result_quarantine_to) < strtotime($petHealth['date'])) {
                        $result_quarantine_to = $petHealth['date'];
                    }
                    if (strtotime($result_quarantine_from) > strtotime($petHealth['date'])) {
                        $result_quarantine_from = $petHealth['date'];
                    }
                }

                if ($result_quarantine_to !== $model['shelterGuests']['quarantine_to']) {
                    $attributes = ['quarantine_to' => $result_quarantine_to];
                    $model['shelterGuests']['quarantine_to'] = $result_quarantine_to;
                    ShelterGuests::updateAll($attributes, ['id' => $model['shelterGuests']['id']]);
                }
                if ($result_quarantine_from !== $model['shelterGuests']['quarantine_from']) {
                    $attributes = ['quarantine_from' => $result_quarantine_from];
                    $model['shelterGuests']['quarantine_from'] = $result_quarantine_from;
                    ShelterGuests::updateAll($attributes, ['id' => $model['shelterGuests']['id']]);
                }
            } else {
                $result_quarantine_to = null;
            }
            if(($model['shelterGuests']['status']==ShelterGuests::STATUS_IN_SHELTER))
            {
                $quarantine_r = date_create($model['shelterGuests']['quarantine_to']);
                $quarantine_r->modify('+1 day');
                $model['move_to_shelter_date'] = date_format($quarantine_r, 'Y-m-d');
            }
            else{
                $model['move_to_shelter_date']='';
            }


            $quarantineWarnDate = date_create(date('Y-m-d'));
            $quarantineWarnDate->modify('+3 day');
            $qwD = date_format($quarantineWarnDate, 'Y-m-d');

            $vacWarnDate = date_create(date('Y-m-d'));
            $vacWarnDate->modify('+30 day');
            $vwD = date_format($vacWarnDate, 'Y-m-d');

            $model['status_label'] = ShelterGuests::STATUSES[$model['shelterGuests']['status']];
            //0 - все хорошо, 1 - до окончания карантина менее 3-ех дней, 2 - животное в приюте и не вакцинировано, 3 - до вакцинации остается менее 30 дней
            $model['status_text']='';

            if($model['shelterGuests']['status'] == ShelterGuests::STATUS_DEPARTURED && $model['shelterGuests']['departure_reason'] == ShelterGuests::DEPARTURE_REASON_EUTHANASIA && $model['shelterGuests']['departure_specialist']){
                $model['departure_specialist_id']=$model['shelterGuests']['departure_specialist'];
                $specialist = Specialists::findOne(['id' => (int)$model['shelterGuests']['departure_specialist']]);
                if($specialist->id_user){
                    $specialist_user = Users::findOne(['id' => (int)$specialist->id_user]);
                    $model['departure_specialist_name']=$specialist_user->fullname;
                }
            }

            if($model['shelterGuests']['status']==ShelterGuests::STATUS_QUARANTINE)
            {
                $model['status_text']='На карантине';
                $model['status_warn']=0;
                if($model['shelterGuests']['quarantine_to']<$qwD){
                    $model['status_text']='Карантин закончится менее чем через три дня';
                    $model['status_warn']=1;
                }
            }else if ($model['shelterGuests']['status']==ShelterGuests::STATUS_IN_SHELTER){
                //проверяем есть ли вакцина
                $model['status_warn']=0;
                if($model['vaccination_last_date']=='')
                {
                    $model['status_text']='Вакцинация не проводилась';
                    $model['status_warn']=2;
                }else{
                    $nD=date('Y-m-d');
                    //проверяем дату последней вакцины
                    if($model['vaccination_last_date']<$vwD && $model['vaccination_last_date']>$nD)
                    {
                        $model['status_text']='До вакцинации осталось менее 30 дней';
                        $model['status_warn']=3;
                    }else{
                        $model['status_text']='Действие вакцинации закончилось';
                        $model['status_warn']=2;
                    }
                }
            }

        }

        // Необходимо определить в скольких организациях реально присутствуют животные, чтобы определить надо ли показывать колонки Округ и Приют
        $count = $this->prepareSubQuery($filter, $organization_ids)
            ->select('id_organization')->groupBy('id_organization')->count();

        return [
            'pets' => array_values($models),
            'total_count' => $dataProvider->getTotalCount(),
            'pages_count' => ($dataProvider->pagination === false ? 1 : $dataProvider->pagination->getPageCount()),
            'enable_area_columns' => $count > 1,
        ];
    }

    /**
     * @param array $filter
     * @return \yii\db\ActiveQuery
     */
    public function prepareQuery($filter = [], $organization_ids = [])
    {
        $query = $this->preparePetsQuery($filter);

        // @ToDo пришлось перенести чтобы можно было использовать подзапрос в других местах
        $subquery = $this->prepareSubQuery($filter, $organization_ids);

        $expr = '"sub"."id" AS record_id,
        "sub"."arrival_date",
        "sub"."departure_date",
        "sub"."status",
        "sub"."catching_act_number",
        "sub"."id_owner" AS departure_id_owner,
        
        "sub"."area_id",
        "sub"."area_name",
        "sub"."area_short_name",
        "sub"."organization_id",
        "sub"."organization_name",
        "sub"."organization_short_name",
        
        "sub"."socialized",
        "sub"."aviary_title",

        "sub"."managed_organization_id",
        "sub"."managed_organization_name",
        
        (case when "sub"."pet_owner_jur_name" isnull or "sub"."pet_owner_jur_name" = \'\'
        then "sub"."pet_owner_fullname"
        else
        concat("sub"."pet_owner_jur_name", \' (\', "sub"."pet_owner_fullname", \')\')
        end) AS departure_owner_fullname';
        $query->addSelect(new Expression($expr));

        $query->orderBy(new Expression('coalesce("sub"."departure_date", "sub"."arrival_date") desc'));

        if (isset($filter['arrival_date'])) {
            if (isset($filter['arrival_date']['date_from'])) {
                $query->andFilterWhere(['>=', 'arrival_date', $filter['arrival_date']['date_from']]);
            }
            if (isset($filter['arrival_date']['date_to'])) {
                $query->andFilterWhere(['<=', 'arrival_date', $filter['arrival_date']['date_to']]);
            }
            $query->andWhere(['not', ['arrival_date' => null]]);
        }

        if (isset($filter['departure_date'])) {
            if (isset($filter['departure_date']['date_from'])) {
                $query->andFilterWhere(['>=', 'departure_date', $filter['departure_date']['date_from']]);
            }
            if (isset($filter['departure_date']['date_to'])) {
                $query->andFilterWhere(['<=', 'departure_date', $filter['departure_date']['date_to']]);
            }
            // $query->andWhere(['not', ['departure_reason' => null]]);
        }

        $subquery2 = clone $subquery;
        $query->leftJoin(
            ['sub' => $subquery],
            'sub.id_pet = p.id'
        );
        $query->andWhere(['in', 'p.id', $subquery2->select('id_pet')])
            ->asArray()
            ->indexBy('record_id');

        return $query;
    }

    public function prepareSubQuery($filter = [], $organization_ids = [])
    {
        $query = ShelterGuests::find()->alias('sh')
            ->select([
                'sh.*',
                'area_id' => 'ar.id',
                'area_name' => 'ar.name',
                'area_short_name' => 'ar.short_name',
                'organization_id' => 'o.id',
                'organization_name' => 'o.name',
                'organization_short_name' => 'o.short_name',
                'aviary_title' => 'aviary.title',
                'pet_owner_fullname' => 'pet_owners.fullname',
                'pet_owner_jur_name' => 'pet_owners.jur_name',
                'managed_organization_id' => 'o.managing_organization_id',
                'managed_organization_name' => 'om.name',
            ])
            ->leftJoin(PetOwners::tableName() . ' pet_owners', 'pet_owners.id = sh.id_owner')
            ->leftJoin(Organizations::tableName() . ' o', 'o.id = sh.id_organization')
            ->leftJoin(Aviary::tableName() . ' aviary', 'aviary.id = sh.aviary_id')
            ->leftJoin(Areas::tableName() . ' ar', 'ar.id = o.id_area')
            ->leftJoin(Organizations::tableName() . ' om', 'om.id = o.managing_organization_id')
            ->where(['id_organization' => $organization_ids]);

        if (isset($filter['status']) && in_array($filter['status'], array_keys(ShelterGuests::STATUSES))) {
            $query->andFilterWhere(['sh.status' => $filter['status']])
                ->andWhere(['not', ['sh.status' => null]])->andWhere(['not', ['sh.status' => '']]);
        }
        if (isset($filter['aviary']) && is_array($filter['aviary'])) {
            $query->andFilterWhere(['sh.aviary_id' => $filter['aviary']])
                ->andWhere(['not', ['sh.aviary_id' => null]]);
        }
        if (isset($filter['socialized'])) {
            $query->andFilterWhere(['sh.socialized' => $filter['socialized'] == 1]);
        }
        if (isset($filter['catching_act_number'])) {
            $query->andFilterWhere(['like', 'sh.catching_act_number', $filter['catching_act_number']]);
        }
        if (isset($filter['k_u'])) {
            $query->andFilterWhere(['sh.id' => $filter['k_u']])
                ->andWhere(['not', ['sh.id' => null]]);
            /*
             * $subquery->andFilterWhere(['ilike', 'sh.id', '%' . $filter['k_u'] . '%', false])
                ->andWhere(['not', ['sh.id' => null]]);
             */
        }
        if (isset($filter['area']) && is_array($filter['area'])) {
            $query->andFilterWhere(['o.id_area' => $filter['area']])
                ->andWhere(['not', ['o.id_area' => null]]);
        }
        if (isset($filter['shelter']) && is_array($filter['shelter'])) {
            $query->andFilterWhere(['o.id' => $filter['shelter']])
                ->andWhere(['not', ['o.id' => null]]);
        }

        if (empty($filter['departure_reason'])) {
            if (isset($filter['status'])) {
                if ($filter['status'] === ShelterGuests::STATUS_DEPARTURED) {
                    $query->andWhere(['not', ['departure_reason' => null]]);
                } elseif($filter['status'] !== 'all') {
                    $query->andWhere(['departure_reason' => null]);
                }
            }
        } else {
            $query->andWhere(['departure_reason' => $filter['departure_reason']]);
        }

        return $query;
    }

    /**
     * @param array $filter
     * @return \yii\db\ActiveQuery
     */
    public function preparePetsQuery($filter = [])
    {
        $query = Pets::find()
            ->alias('p')
            ->select('p.*')
            //показывать животных снятых с учета при нужном фильтре
            //->where(['reg_expire_date' => null])
            ->joinWith('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('species')
            ->with('breeds')
            ->with('color')//->with('shelter')
        ;

        if (isset($filter['search'])) {
            $id_ident_type_chip = IdentificationTypes::findIdentificationTypeId('чип');
            $query->leftJoin(PetIdentification::tableName() . ' pi', 'pi.id_pet = p.id AND pi.id_ident_type = ' . $id_ident_type_chip);
            $query->innerJoin(ShelterGuests::tableName() . ' sg', 'sg.id_pet = p.id');

            $searchText = mb_strtolower($filter['search']);

            $whereCondition = [
                'OR',
                ['like', 'lower(p.name)', $searchText],
                ['like', 'lower(pi.identification_code)',$searchText],
                ['like', 'lower(sg.catching_act_number)', $searchText],
                ['like', 'lower(concat(sub.id, substr((select s.name from species as s where p.id_species = s.id), 1, 1), sub.organization_id))', $searchText],
            ];

            if (is_numeric($searchText))
                $whereCondition[] = ['like', 'lower(sg.id::text)', $searchText];

            $query->andFilterWhere($whereCondition);
        }

        if (isset($filter['identification_code'])) {
            $query->andFilterWhere(['=', 'pet_identification.identification_code', $filter['identification_code']]);
        }
        if (isset($filter['id_species'])) {
            $query->andFilterWhere(['=', 'p.id_species', (int)$filter['id_species']]);
        }
        if (isset($filter['color_id'])) {
            $query->andFilterWhere(['=', 'p.color_id', (int)$filter['color_id']]);
        }
        if (isset($filter['sex'])) {
            $query->andFilterWhere(['=', 'p.sex', $filter['sex']]);
        }
        if (isset($filter['name'])) {
            $query->andFilterWhere(['LIKE', 'p.name', $filter['name']]);
        }
        if ($filter['birthday_date'] ?? false) {
            if ($filter['birthday_date']['date_from'] > 0) {
                $date_from = (int)$filter['birthday_date']['date_from'];
                $query->andFilterWhere(['<=', 'birthday', date('Y-m-d', strtotime("-$date_from year", time()))]);
            }
            if ($filter['birthday_date']['date_to'] > 0) {
                $date_to = (int)$filter['birthday_date']['date_to'];
                $query->andFilterWhere(['>=', 'birthday', date('Y-m-d', strtotime("-$date_to year", time()))]);
            }
            $query->andWhere(['not', ['birthday' => null]]);
        }

        return $query;
    }

    protected function parseStringToShortName($string)
    {
        $result = '';
        $token = strtok($string, ' -');
        do {
            $result .= mb_strtoupper(mb_substr($token, 0, 1));
        } while ($token = strtok(' -'));

        return $result;
    }
}