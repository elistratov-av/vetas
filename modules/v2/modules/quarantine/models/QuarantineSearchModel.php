<?php

namespace app\modules\v2\modules\quarantine\models;

use app\models\db\FiasAddresses;
use app\models\db\Organizations;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetOwners;
use app\models\db\PetOwnerType;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Quarantine;
use app\models\db\QuarantineFocus;
use app\models\db\QuarantineLocality;
use app\models\db\Species;
use app\models\db\tmc\TmcToDiseases;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class QuarantineSearchModel
 * @package app\modules\v2\modules\quarantine\models
 */
class QuarantineSearchModel extends Model
{
    const ADDRESS_TYPE_PET = 'address_pet';
    const ADDRESS_TYPE_OWNER = 'address_owner';
    const ADDRESS_TYPE_REPRESENTATIVE = 'address_representative';

    public $addressTypes = [
        self::ADDRESS_TYPE_OWNER, self::ADDRESS_TYPE_PET, self::ADDRESS_TYPE_REPRESENTATIVE,
    ];

    /**
     * @param int       $page
     * @param int|false $limit
     * @param array     $filter
     * @return array
     */
    public function list(int $page = 1, $limit = 10, array $filter = [])
    {
        $query = $this->prepareQuery($filter);

        $dataProvider = new ActiveDataProvider([
            'query' => $query->asArray(),
            'pagination' => (($limit === false || $limit === 'false') ? false : [
                'defaultPageSize' => $limit,
                'page' => ($page - 1),
            ]),
        ]);

        $models = $dataProvider->getModels();
        foreach ($models as &$model) {
            if (!empty($model['localities'])) {
                foreach ($model['localities'] as &$locality) {
                    unset($locality['coords']);
                    if (!empty($locality['territory']) && is_string($locality['territory'])) {
                        try {
                            $territories = Json::decode($locality['territory']);
                        } catch (\Throwable $e) {
                            $territories = [];
                        }
                        $locality['territory'] = $territories;
                    }
                }
            }
            if (!empty($model['focuses'])) {
                foreach ($model['focuses'] as &$focus) {
                    unset($focus['coords']);
                }
            }
        }

        return [
            'quarantines' => $models,
            'total_count' => $dataProvider->getTotalCount(),
            'pages_count' => ($dataProvider->pagination === false ? 1 : $dataProvider->pagination->getPageCount()),
        ];
    }

    /**
     * @param array $filter
     * @return \yii\db\ActiveQuery
     */
    private function prepareQuery($filter = [])
    {
        $query = Quarantine::find()
            ->distinct()
            ->with([
                'localities',
                'focuses',
                'focuses.pet',
                'disease',
            ]);

        if (isset($filter['date_from'])) {
            $query->andFilterWhere(['>=', 'start_date', $filter['date_from']]);
        }
        if (isset($filter['date_to'])) {
            $query->andFilterWhere(['<=', 'start_date', $filter['date_to']]);
        }

        if (isset($filter['id_disease'])) {
            $query->andFilterWhere(['=', 'id_disease', $filter['id_disease']]);
        }

        if (isset($filter['status']) && $filter['status'] != 'all') {
            // all, active, finished
            if ($filter['status'] == 'active') {
                $query->andWhere(['fact_end_date' => null]);
            } elseif ($filter['status'] == 'finished') {
                $query->andWhere(['not', ['fact_end_date' => null]]);
            }
        }

        if (isset($filter['geometry'])) {
            try {
                $quantinesIds = (new Query())
                    ->select('id_quarantine')
                    ->from(QuarantineLocality::tableName())
                    ->where(new Expression('ST_Within(ST_GeomFromText(\'' . $filter['geometry'] . '\'), coords)'))
                    ->column();
                if (empty($quantinesIds)) {
                    $query->emulateExecution();
                } else {
                    $query->andWhere(['in', 'id', $quantinesIds]);
                }
            } catch (\Throwable $e) {
                // просто игнорим условие
                \Yii::error($e->getMessage());
            }
        } elseif (isset($filter['address'])) {
            $query->leftJoin(QuarantineFocus::tableName() . ' qf', 'qf.id_quarantine = quarantines.id');
            $query->andWhere(['ilike', 'qf.address', $filter['address']]);
        }

        if (isset($filter['with_localities_only'])) {
            $query->joinWith('localities');
            $query->andWhere('quarantines_localities.id is not null');
        }

        return $query;
    }

    /**
     * @param \app\models\db\Quarantine $quarantine
     * @param int                       $page
     * @param int                       $limit
     * @param array                     $filter
     * @return array
     */
    public function listAnimals(Quarantine $quarantine, int $page, int $limit, array $filter)
    {
        $expression = $this->prepareWithinExpression($quarantine);
        if ($expression === false) {
            return [];
        }
        $query = $this
            ->preparePetsQuery($expression)
            ->addSelect('vcc.vaccination_valid_until, vcc.vaccine_name');

        if (isset($filter['pet_name'])) {
            $query->andFilterWhere(['ilike', 'p.name', $filter['pet_name']]);
        }

        if (isset($filter['id_species'])) {
            $query->andFilterWhere(['=', 'p.id_species', $filter['id_species']]);
        }

        // добавим данные о вакцинации

        $isRabies = $quarantine->disease->name == 'Бешенство (Rabies)';
        $vaccinesIds = $isRabies
            ? []
            : (new Query())
                ->select('id_tmc')
                ->from(TmcToDiseases::tableName())
                ->where(['id_disease' => $quarantine->id_disease])
                ->distinct()
                ->column();

        if (!$isRabies && !count($vaccinesIds)) {
            return [
                'pets' => [],
                'total_count' => 0,
                'pages_count' => 0,
            ];
        }

        if ($isRabies) {
            $subSubqVaccDetails = (new Query())
                ->select([
                    'id_pet' => new Expression('DISTINCT ON (id_pet) COALESCE(p.id_main_pet, p.id)'),
                    'vaccination_valid_until' => 'valid_until',
                    'vaccine_name' => 'drug_name'
                ])
                ->from(PetRabiesVaccination::tableName() . ' prv')
                ->innerJoin(Pets::tableName() . ' p', 'p.id = prv.id_pet')
                ->orderBy([
                    'id_pet' => SORT_ASC,
                    'valid_until' => SORT_DESC,
                ]);
            $query->leftJoin(['vcc' => $subSubqVaccDetails], 'vcc.id_pet = p.id')
                ->leftJoin('species s', 's.id = p.id_species')
                ->andWhere(['IN', 's.tech_name', [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG]])
            ;
        } else {
            $subSubqVaccDetails = (new Query())
                ->select(new Expression('id_pet, max(valid_until) as vaccination_valid_until'))
                ->from(PetOtherVaccinations::tableName())
                ->andWhere(['in', 'id_vaccine', $vaccinesIds])
                ->groupBy('id_pet');
            $subqVaccDetails = (new Query())
                ->select('vccd.id_pet, vccd.vaccination_valid_until, pv.drug_name as vaccine_name');
            $subqVaccDetails->from(['vccd' => $subSubqVaccDetails]);
            $subqVaccDetails->leftJoin(
                PetOtherVaccinations::tableName() . ' pv',
                'pv.id_pet = vccd.id_pet and pv.valid_until = vccd.vaccination_valid_until'
            );
            $query->leftJoin(['vcc' => $subqVaccDetails], 'vcc.id_pet = p.id');
        }

        // фильтр по состоянию вакцинации
        switch (ArrayHelper::getValue($filter, 'vaccination_status')) {
            case 'valid':
                $query->andWhere(['>', 'vcc.vaccination_valid_until', date('Y-m-d')]);
                break;
            case 'invalid':
                $query->andWhere(['<=', 'vcc.vaccination_valid_until', date('Y-m-d')]);
                break;
            case 'none':
                $query->andWhere(['is', 'vcc.vaccination_valid_until', null]);
                break;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $limit,
                'page' => ($page - 1),
            ],
        ]);

        return [
            'pets' => $dataProvider->getModels(),
            'total_count' => $dataProvider->getTotalCount(),
            'pages_count' => $dataProvider->pagination->getPageCount(),
        ];
    }

    /**
     * @param string $point
     * @return array
     */
    public function suggestAnimals($point)
    {
        $expression = new Expression('ST_DWithin(fa.coords, ST_GeomFromText(\'' . $point . '\'), 1000)');
        $query = $this->preparePetsQuery($expression);

        return [
            'pets' => $query->all(),
        ];
    }

    /**
     * @param \yii\db\Expression $expression
     * @return \yii\db\ActiveQuery
     */
    private function preparePetsQuery(Expression $expression)
    {
        return $query = Pets::find()
            ->alias('p')
            ->select('p.*')
            ->distinct()
            ->where(['reg_expire_date' => null])
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('species')
            ->with('breeds')
            ->with(['pets_to_owner' => function ($q) {
                /* @var $q \yii\db\ActiveQuery */
                $q->orderBy([
                    'id_owner_type' => SORT_ASC,
                ]);
            }])
            ->with('pets_to_owner.owner_type')
            ->with('pets_to_owner.owner')
            ->with('pets_to_owner.owner.fias_addresses')
            ->leftJoin('pets_to_owner pto', "p.id = pto.id_pet")
            ->innerJoin('pet_owner_type pot', 'pto.id_owner_type = pot.id AND pot.is_owner = true')
            ->leftJoin('pet_owners ptopo', 'ptopo.id = pto.id_owner')
            ->innerJoin(FiasAddresses::tableName() . ' fa', 'fa.id = '
                . 'CASE '
                . 'WHEN p.id_fias_address IS NOT NULL THEN p.id_fias_address '
                . 'WHEN ptopo.id_fact_fias_address IS NOT NULL THEN ptopo.id_fact_fias_address '
                . 'WHEN ptopo.id_fias_address IS NOT NULL THEN ptopo.id_fias_address '
                . 'END')
            ->joinWith(['fias_address'])
            ->andWhere(['id_reg_expire_reason' => null])
            ->andWhere(['id_main_pet' => null])
            ->andWhere($expression)
            ->andWhere(['or',
                ['p.is_main' => true],
                ['p.is_main' => null]
            ])
            ->asArray();
    }

    /**
     * @param int[] $ids_pet
     * @param int $id_organization
     * @return array|bool
     */
    public function check(array $ids_pet, int $id_owner, int $id_organization)
    {
        /** @var Pets[] $pets */
        $pets = Pets::find()->where(['id' => $ids_pet])->all();
        if (count($pets) === 0) {
            $this->addError('id_pet', 'Указанное животное не найдено');

            return false;
        }

        $organization = Organizations::findOne(['id' => $id_organization]);
        if ($organization === null) {
            $this->addError('id_organization', 'Указанная организация не найдена');

            return false;
        }

        $result = [
            'organization' => [
                'quarantine' => false,
                'address' => null,
                'disease' => null,
            ],
        ];

        foreach($pets as $i => $pet) {
            $result['pets'][$i]['id'] = $pet->id;
            foreach($this->addressTypes as $addressType) {
                $result['pets'][$i][$addressType] = [
                    'quarantine' => false,
                    'address' => null,
                    'disease' => null,
                ];
            }
        }

        $query = $this->prepareQuery(['status' => 'active']);
        $models = $query->asArray()
            ->all();

        if (empty($models)) {
            return $result;
        }

        if ($organization->fias_addresses !== null && !empty($organization->fias_addresses->coords)) {
            $result['organization']['address'] = $organization->fias_addresses->full_address;
            foreach ($models as $model) {
                if ($this->inQuarantine($model, $organization->fias_addresses)) {
                    $result['organization']['quarantine'] = true;
                    $result['organization']['disease'] = ArrayHelper::getValue($model, 'disease.name');
                    break;
                }
            }
        }

        foreach ($pets as $i => $pet) {
            if ($pet->owner && $pet->owner->fias_addresses !== null && !empty($pet->owner->fias_addresses->coords)) {
                $result = $this->applyQuarantines($i, $result, $models, $pet->owner->fias_addresses, self::ADDRESS_TYPE_OWNER);
            }
            if ($pet->owner && $pet->owner->fact_fias_addresses !== null && !empty($pet->owner->fact_fias_addresses->coords)) {
                $result = $this->applyQuarantines($i, $result, $models, $pet->owner->fact_fias_addresses, self::ADDRESS_TYPE_OWNER);
            }
            if ($pet->fias_address !== null && !empty($pet->fias_address->coords)) {
                $result = $this->applyQuarantines($i, $result, $models, $pet->fias_address, self::ADDRESS_TYPE_PET);
            }
            if (!$pet->owner || $id_owner !== $pet->owner->id) {
                $representative = PetOwners::findOne(['id' => $id_owner]);
                if ($representative->fias_addresses !== null && !empty($representative->fias_addresses->coords)) {
                    $result = $this->applyQuarantines($i, $result, $models, $representative->fias_addresses, self::ADDRESS_TYPE_REPRESENTATIVE);
                }
                if ($representative->fact_fias_addresses !== null && !empty($representative->fact_fias_addresses->coords)) {
                    $result = $this->applyQuarantines($i, $result, $models, $representative->fact_fias_addresses, self::ADDRESS_TYPE_REPRESENTATIVE);
                }
            }
        }

        return $result;
    }

    /**
     * @param $result
     * @param $models
     * @param Pets $pet
     * @param FiasAddresses $address
     * @param string $addressType
     * @return array
     */
    private function applyQuarantines($i, $result, $models, FiasAddresses $address, string $addressType)
    {
        foreach ($models as $model) {
            if ($this->inQuarantine($model, $address)) {
                $result['pets'][$i][$addressType] = [
                    'quarantine' => true,
                    'address' => $address->full_address,
                    'disease' => ArrayHelper::getValue($model, 'disease.name'),
                ];
                break;
            }
        }
        return $result;
    }

    /**
     * @param \app\models\db\Quarantine|array    $quarantine
     * @param \app\models\db\FiasAddresses|array $fiasAddress
     * @return bool
     */
    private function inQuarantine($quarantine, $fiasAddress)
    {
        if (empty($quarantine['localities'])) {
            return false;
        }

        foreach ($quarantine['localities'] as $locality) {
            if (empty($locality['geometry'])) {
                continue;
            }
            // select ST_Within(
            //     ST_GeomFromText(ST_AsText('010100000054CAC6DEE6CF4240C14FC1864CE94B40')),
            //     ST_GeomFromText('POLYGON((37.3464821 55.637038,37.4183744 55.8544176,37.6575015 55.8572143,37.6420248 55.7643898,37.3464821 55.637038))')
            // )
            $sql = 'select ST_Within(ST_GeomFromText(ST_AsText(\'' . $fiasAddress['coords'] . '\')), ST_GeomFromText(\'' . $locality['geometry'] . '\'))';
            $result = \Yii::$app->db
                ->createCommand($sql)
                ->queryScalar();
            if ($result === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \app\models\db\Quarantine $quarantine
     * @return \yii\db\Query|false
     */
    public function prepareOwnersQuery(Quarantine $quarantine)
    {
        $expression = $this->prepareWithinExpression($quarantine);
        if ($expression === false) {
            return false;
        }

        $query = (new Query)
            ->select('po.id AS id_owner, p.id AS id_pet')
            ->from(Pets::tableName() . ' p')
            ->leftJoin(PetsToOwner::tableName() . ' pto', ' p.id = pto.id_pet')
            ->leftJoin(PetOwners::tableName() . ' po', 'po.id = pto.id_owner')
            ->leftJoin(PetOwnerType::tableName() . ' pot', 'pot.id = pto.id_owner_type')
            ->innerJoin(FiasAddresses::tableName() . ' fa', 'fa.id = '
            . 'CASE '
            . 'WHEN NOT p.id_fias_address IS NULL THEN p.id_fias_address '
            . 'WHEN NOT po.id_fact_fias_address IS NULL THEN po.id_fact_fias_address '
            . 'WHEN NOT po.id_fias_address IS NULL THEN po.id_fias_address '
            . 'END')
            ->where($expression)
            ->andWhere(['=', 'po.is_deleted', false])
            ->andWhere(['pot.is_owner' => true]);

        return $query;
    }

    /**
     * @param \app\models\db\Quarantine $quarantine
     * @return \yii\db\Expression|false
     */
    private function prepareWithinExpression(Quarantine $quarantine)
    {
        if (empty($quarantine->localities)) {
            return false;
        }

        $areas = [];
        foreach ($quarantine->localities as $locality) {
            if (!empty($locality->coords)) {
                $areas[] = 'ST_Within(fa.coords, ST_GeomFromText(\'' . $locality->getGeometry() . '\'))';
            }
        }

        if (empty($areas)) {
            return false;
        }

        return new Expression(implode(' OR ', $areas));
    }
}
