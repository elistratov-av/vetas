<?php

namespace app\modules\soap\v2\models\wsdl;

use app\modules\soap\models\wsdl\ReferencesHandler as ReferencesHandlerV1;
use app\modules\soap\v2\skeletons\services\ServicesResponse;
use app\modules\soap\v2\skeletons\services\ServicesTypeList;
use app\modules\soap\v2\skeletons\orgs\OrgsList;
use app\modules\soap\v2\skeletons\orgs\OrgsResponse;
use app\modules\soap\v2\skeletons\species\SpeciesResponse;
use app\modules\soap\v2\skeletons\species\SpeciesList;

/**
 * Class ReferencesHandler
 * @package app\modules\soap\v2\models\wsdl
 */
class ReferencesHandler extends ReferencesHandlerV1
{
    use HandlerTrait;

    /**
     * @return \app\modules\soap\v2\skeletons\species\SpeciesResponse
     */
    public static function getSpecies()
    {
        $list = new SpeciesList();
        $species = self::findSpecies();
        $species = self::convertKeyCase($species);
        $list->Species = $species;
        $response = new SpeciesResponse();
        $response->SpeciesList = $list;

        return $response;
    }

    /**
     * @return \app\modules\soap\v2\skeletons\orgs\OrgsResponse
     */
    public static function getOrgs()
    {
        $list = new OrgsList();
        $orgs = self::findOrganizations();
        $orgs = self::convertKeyCase($orgs);
        $list->Orgs = $orgs;
        $response = new OrgsResponse();
        $response->OrgsList = $list;

        return $response;
    }

    /**
     * @param integer $SpeciesId
     * @return \app\modules\soap\v2\skeletons\services\ServicesResponse
     */
    public static function getServices($SpeciesId)
    {
        $list = new ServicesTypeList();
        $serviceTypes = self::findServiceTypes($SpeciesId);
        $serviceTypes = self::convertKeyCase($serviceTypes);
        $list->ServicesType = $serviceTypes;

        $response = new ServicesResponse();
        $response->ServicesTypeList = $list;

        return $response;
    }
}
