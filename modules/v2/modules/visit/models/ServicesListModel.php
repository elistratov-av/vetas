<?php

namespace app\modules\v2\modules\visit\models;

use app\models\db\etp\ETPMessage;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use app\models\db\ServiceMeasures;
use app\models\db\ServiceTypes;
use app\models\db\VisitParamValues;
use app\models\db\VisitPets;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitsGovServices;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class ServicesListModel
 * 
 * @package app\modules\v2\modules\visit\models
 * 
 * TODO:multiple-pets-services Учитывать услуги для неск. животных
 */
class ServicesListModel
{
    public $services;
    public $service_types;
    public $service_measures;
    public $service_params;
    public $visits_gov_services = [];
    public $visit_param_values = [];
    public $visit_service_param_values = [];

    private $id_visit;
    private $id_service_types;
    private $id_service_measures;
    private $visitGovServiceId;

    /**
     * ServicesListModel constructor.
     * @param int            $idOrganization
     * @param null           $idVisit
     * @param array|int|null $idPet
     * @param string|null    $code
     * @param string|null    $name
     */
    public function __construct(
        int $idOrganization,
        $idVisit = null,
        $idPet = null,
        string $code = null,
        string $name = null
    ) {
        $this->id_service_types = [];
        $this->id_service_measures = [];
        $govServicesTable = GovServices::tableName();

        $query = GovServices::find()
            ->select([
                $govServicesTable . '.id',
                'name',
                'price',
                'sort_by',
                'duration',
                'cod',
                'cooldown',
                new Expression('false AS unavailable'),
                new Expression('NULL AS unavailble_reason'),
                new Expression("(SELECT EXISTS (
                SELECT 1 FROM gov_services_params
                JOIN params ON gov_services_params.id_param = params.id
                WHERE req_in=True AND datatype = 'dict' AND id_service = gov_services.id LIMIT 1)) AS allow_dublicate"),
                'id_pricelist',
                $govServicesTable . '.id_service_type',
                'id_service_measure',
                '(alternative_name::text)',
                'for_broods',
                'for_multiple',
                'once_per_day',
            ])
            ->where([
                'id_pricelist' => new Expression(
                    "(select pricelist_id from organizations_tree where id = :id)",
                    ['id' => $idOrganization]
                )
            ])
            ->orderBy([$govServicesTable . '.name' => SORT_ASC]);

        if (is_numeric($idVisit)) {
            if (empty($idPet)) {
                $where = ['id_visit' => $idVisit];
            } else {
                $where = [
                    'AND',
                    ['id_visit' => $idVisit]
                ];
                if (is_array($idPet) || empty($idPet)) {
                    // услуги на выводок/несколько животных
                    $where[] = ['id_pet' => null];
                } else {
                    // индивидуальные услуги
                    $where[] = ['id_pet' => $idPet];
                }
            }
            $query->andWhere([
                'in',
                'id',
                (new Query())
                    ->select('id_service')
                    ->from('visits_gov_services')
                    ->where($where),
            ]);

            if (\is_string($code)) {
                $query->andWhere(['cod' => $code]);
            }
            if (\is_string($name)) {
                $query->andWhere(['ILIKE', 'name', $name]);
            }
        } else {
            $query->andWhere(['deleted' => false]);
        }

        $services = $query
            ->asArray()
            ->all();

        foreach ($services as $service) {
            $this->id_service_types[] = $service['id_service_type'];
            $this->id_service_measures[] = $service['id_service_measure'];
        }

        $this->id_service_types = array_unique($this->id_service_types);
        $this->id_service_measures = array_unique($this->id_service_measures);

        $this->services = $services;
        $this->id_visit = $idVisit;
    }

    /**
     * @return $this
     */
    public function addServiceTypes(): self
    {
        $serviceTypes = ServiceTypes::find()
            ->select(['id', 'name', 'description'])
            ->where(['id' => $this->id_service_types])
            ->orderBy("name ASC")
            ->asArray()
            ->all();

        $this->service_types = $serviceTypes ?? [];
        return $this;
    }

    /**
     * @return $this
     */
    public function addServiceMeasures(): self
    {
        $serviceMeasures = ServiceMeasures::find()
            ->select(['id', 'name', "('') AS service_types", 'count_flag'])
            ->where(['id' => $this->id_service_measures])
            ->asArray()
            ->all();

        $this->service_measures = $serviceMeasures ?? [];
        return $this;
    }

    /**
     * @return bool
     */
    private function isMosruVisit()
    {
        return ETPMessage::find()
            ->where(['visit_id' => $this->id_visit])
            ->exists();
    }

    /**
     * @return $this
     */
    public function addServiceParams(): self
    {
        $govServicesParamsTable = GovServicesParams::tableName();
        $paramsTable = Params::tableName();

        $paramsQuery = GovServicesParams::find()
            ->select([
                "{$govServicesParamsTable}.id",
                "{$govServicesParamsTable}.id_param",
                "{$govServicesParamsTable}.id_service",
                "{$govServicesParamsTable}.sort_by",
                "{$paramsTable}.name",
                "{$paramsTable}.tech_name",
                "{$paramsTable}.datatype",
                "{$paramsTable}.datatype_details",
                "{$paramsTable}.config",
            ]);

        $paramsQuery->where([
            'flag_in' => true,
            'visit_flag' => false,
        ]);
        $paramsQuery->andWhere(['in', 'id_service', ArrayHelper::getColumn($this->services, 'id')]);
        if ($this->isMosruVisit()) {
            $paramsQuery->andWhere(new Expression("gov_services_params.id_service NOT IN(select id from gov_services where type = 'mosru' and at_home = true)"));
        }

        $this->service_params = $paramsQuery
            ->joinWith('param', false)
            ->with([
                'dictionaries' => function ($dictQuery) {
                    /* @var $dictQuery \yii\db\ActiveQuery */
                    $dictQuery->select([
                        'id',
                        'name',
                        'type',
                    ]);
                }
            ])
            ->asArray()
            ->all();

        return $this;
    }

    /**
     * @param int $idOrganization
     * @param array|int $idPet
     * @param int $idShiftType
     * @return $this
     */
    public function addVisitsGovServices($idOrganization = null, $idPet = null, $idShiftType = null): self
    {
        $query = VisitsGovServices::find()
            ->select([
                'public.visits_gov_services.id',
                'id_visit',
                'id_service',
                'public.visits_gov_services.id_pet',
                'count',
            ])
            ->joinWith('visit', false)
            ->where(['public.visits_gov_services.id_visit' => $this->id_visit]);

        if (is_numeric($idOrganization)) {
            $query->andWhere(['id_organization' => $idOrganization]);
        }
        if (is_numeric($idPet) || is_array($idPet)) {
            $query->andWhere(['public.visits_gov_services.id_pet' => $idPet]);
        }
        if (is_numeric($idShiftType)) {
            $query->andWhere(['channel' => $idShiftType]);
        }

        $visitsGovServices = $query->all();

        foreach ($visitsGovServices as $visitsGovService) {
            $this->visitGovServiceId[] = $visitsGovService->id;
            $row = $visitsGovService->toArray();
            $row['id_visitservice'] = $visitsGovService->id;
            $row['existence_report'] = $visitsGovService->service->hasReports();
            unset($row['id']);
            if ($row['id_pet']) {
                $row['id_pet'] = (array) $row['id_pet'];
            } else {
                $row['id_pet'] = $this->getPetsByVisitIdAsArray($row['id_visit']);
            }

            $this->visits_gov_services[] = $row;
        }

        return $this;
    }

    /**
     * @param $visitId
     * @return array
     */
    private function getPetsByVisitIdAsArray($visitId): array
    {
        $pets = VisitPets::find()
            ->where(['id_visit' => $visitId])
            ->asArray()
            ->all();
        return $pets ? ArrayHelper::getColumn($pets, 'id_pet') : [];
    }

    /**
     * @return $this
     */
    public function addVisitParamValues(): self
    {
        $visitParamValues = VisitParamValues::find()
            ->select([
                'id',
                'num_value',
                'char_value',
                'dict_value',
                'id_param'
            ])
            ->where(['id_visit' => $this->id_visit])
            ->asArray()
            ->all();

        $this->visit_param_values = $visitParamValues ?? [];
        return $this;
    }

    /**
     * @return $this
     */
    public function addVisitServiceParamValues(): self
    {
        $tableName = VisitServiceParamValues::tableName();
        $query = VisitServiceParamValues::find()
            ->select([
                "{$tableName}.id",
                "{$tableName}.id_param",
                "{$tableName}.id_visitservice",
                "{$tableName}.num_value",
                "{$tableName}.char_value",
                "{$tableName}.dict_value",
                "{$tableName}.date_value",
                "{$tableName}.complex_value"
            ])
            ->joinWith('govServicesParams', false)
            ->where('"visit_service_param_values"."id_param" = "gov_services_params"."id_param"')
            ->andWhere(['req_in' => true]);

        if ($this->isMosruVisit()) {
            $query->andWhere(new Expression("gov_services_params.id_service NOT IN(select id from gov_services where type = 'mosru' and at_home = true)"));
        }

        $query->andWhere(['id_visitservice' => $this->visitGovServiceId]);

        $visitServiceParamValues = $query->asArray()->all();
        $this->visit_service_param_values = $visitServiceParamValues ?? [];
        return $this;
    }
}