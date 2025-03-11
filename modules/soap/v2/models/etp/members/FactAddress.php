<?php

namespace app\modules\soap\v2\models\etp\members;

use yii\base\Model;

/**
 * Class FactAddress
 * @package app\modules\soap\v2\models\etp\members
 */
class FactAddress extends Model
{
    /**
     * @var string
     */
    public $Locality;
    /**
     * @var string
     */
    public $Street;
    /**
     * @var string
     */
    public $House;
    /**
     * @var string
     */
    public $Flat;
    /**
     * @var string
     */
    public $POBox;

    /**
     * @var string
     */
    public $Country;
    /**
     * @var string
     */
    public $CountryCode;
    /**
     * @var string
     */
    public $PostalCode;
    /**
     * @var string
     */
    public $Region;
    /**
     * @var string
     */
    public $City;
    /**
     * @var string
     */
    public $Town;
    /**
     * @var string
     */
    public $Building;
    /**
     * @var string
     */
    public $Structure;
    /**
     * @var string
     */
    public $Facility;
    /**
     * @var string
     */
    public $Ownership;
    /**
     * @var string
     */
    public $Okato;
    /**
     * @var string
     */
    public $KladrCode;
    /**
     * @var string
     */
    public $KladrStreetCode;
    /**
     * @var string
     */
    public $BTIDistrictCode;
    /**
     * @var string
     */
    public $BTIRegionCode;
    /**
     * @var string
     */
    public $BTIStreetCode;
    /**
     * @var string
     */
    public $BTIBuildingCode;
    /**
     * @var string
     */
    public $BTIAltCode;
    /**
     * @var string
     */
    public $BTIFlatCode;
    /**
     * @var string
     */
    public $FiasCode;
    /**
     * @var string
     */
    public $Litera;
}
