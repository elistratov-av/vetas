<?php

namespace app\common\components\address;


interface AdapterInterface
{

    /**
     * @return string
     */
    public function getRegion($q = null, $aoid = null);

    /**
     * @return string
     */
    public function getCity($q = null, $region = null);

    /**
     * @return string
     */
    public function getStreet($q = null, $region = null, $city = null);

    /**
     * @return string
     */
    public function getHouse($q = null, $street = null, $city = null);

    /**
     * @return string
     */
    public function getRoom($q = null, $house = null);

    /**
     * @return string
     */
    public function getFullAddress($aoguid);



}
