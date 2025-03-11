<?php

namespace app\modules\soap\models\wsdl;

use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\ServiceTypes;
use app\modules\soap\models\Species;
use app\modules\soap\skeletons\orgs\OrgsList;
use app\modules\soap\skeletons\orgs\OrgsResponse;
use app\modules\soap\skeletons\services\ServicesResponse;
use app\modules\soap\skeletons\services\ServicesTypeList;
use app\modules\soap\skeletons\species\SpeciesList;
use app\modules\soap\skeletons\species\SpeciesResponse;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class ReferencesHandler
 * @package app\modules\soap\models\wsdl
 */
class ReferencesHandler
{
    /**
     * @return SpeciesResponse
     */
    public static function getSpecies()
    {
        $list = new SpeciesList();
        $list->species = self::findSpecies();

        $response = new SpeciesResponse();
        $response->species_list = $list;

        return $response;
    }

    /**
     * @return OrgsResponse
     */
    public static function getOrgs()
    {
        $list = new OrgsList();
        $list->org = self::findOrganizations();

        $response = new OrgsResponse();
        $response->orgs_list = $list;

        return $response;
    }

    /**
     * @param integer $SpeciesId
     * @return ServicesResponse
     */
    public static function getServices($SpeciesId)
    {
        $list = new ServicesTypeList();
        $list->services_type = self::findServiceTypes($SpeciesId);

        $response = new ServicesResponse();
        $response->services_type_list = $list;

        return $response;
    }

    /**
     * @return array
     */
    protected static function findSpecies()
    {
        $species = Species::find()
            ->select(['id', 'name' => 'lower(name)'])
            ->with(['breeds_list' => function ($query) {
                /** @var $query ActiveQuery * */
                $query
                    ->select(['id', 'species_id', 'name' => 'lower(name)'])
                    ->orderBy('sort_by ASC, lower(name) ASC');
            }])
            ->where(['flag_mos_ru' => true])
            ->orderBy([
                new Expression("(lower(name) = 'иные животные')"),
                'name' => SORT_ASC,
            ])
            ->asArray()
            ->all();

        return $species;
    }

    /**
     * @return array
     */
    protected static function findOrganizations()
    {
        $result = MosruOrganizations::find()
            ->alias('mo')
            ->innerJoinWith(['address' => function (ActiveQuery $query) {
                $query->select([
                    'id',    // For activequery
                    'name',
                    'latitude',
                    'longitude',
                ]);
            }])
            ->innerJoinWith(['specialists' => function (ActiveQuery $query) {
                $query
                    ->select([
                        'specialists.id_organization', // For activequery
                        'id' => 'specialists.id_specialist', // For activequery
                        'id_user' => 'specialists.id_user',
                        'specialists.name',
                    ])
                    ->innerJoin(
                        '(
                            SELECT DISTINCT id_organization, id_specialist FROM mosru.timesheets
                            UNION
                            SELECT DISTINCT id_organization, id_specialist FROM mosru.timesheets_call_to_home
                        ) AS tsh',
                        'tsh.id_specialist = specialists.id_specialist'
                    )
                    ->orderBy(['specialists.name' => SORT_ASC]);
            }])
            ->orderBy(['mo.name' => SORT_ASC])
            ->asArray()
            ->all()
        ;

        $orgs = [];
        // заменяем у специалистов id на id_user
        foreach ($result as $item) {
            $specialists = $item['specialists'];
            unset($item['specialists']);
            foreach ($specialists as $spec) {
                $item['specialists'][] = ['id' => $spec['id_user'], 'name' => $spec['name']];
            }

            $orgs[] = $item;
        }

        return $orgs;
    }

    /**
     * @param int $species_id
     * @return array
     */
    protected static function findServiceTypes($species_id)
    {
        $result = ServiceTypes::find()
            ->distinct(true)
            ->select([
                'service_types.id',   // For activequery
                'type_id' => 'service_types.id',
                'type_value' => 'service_types.name',
                'id_service_goal',
            ])
            ->with(['service_list' => function ($sub_query) use ($species_id) { // mosru_services
                /** @var $sub_query ActiveQuery * */
                $sub_query
                    ->distinct(true)
                    ->select([
                        'service_id' => 'mosru.services.id',
                        'id_service_type',
                        'service_value' => 'mosru.services.name',
                        'at_home',
                        'at_clinic',
                        'price',
                        'rating' => 'statistic.mosru_services_rating.sort_by',
                        'mosru.services.sort_by',
                        'mosru.services.name',
                        'hint' => 'mosru.services_hints.text',
                    ])
                    ->leftJoin('statistic.mosru_services_rating', 'statistic.mosru_services_rating.mosru_services_id = mosru.services.id')
                    ->leftJoin('species_services', 'mosru.services.id = species_services.id_service')
                    ->leftJoin('mosru.services_hints', 'mosru.services_hints.id_service = mosru.services.id')
                    ->where([
                        'species_services.id_species' => $species_id,
                    ])
                    ->orderBy([
                        'mosru.services.sort_by' => SORT_ASC,
                        'mosru.services.name' => SORT_ASC,
                    ]);
            }])
            ->innerJoin('mosru.services', 'mosru.services.id_service_type = service_types.id')
            ->leftJoin('species_services', 'mosru.services.id = species_services.id_service')
            ->where([
                'species_services.id_species' => $species_id,
            ])
            ->orderBy('service_types.name ASC')
            ->asArray()
            ->all();

        return $result;
    }
}
