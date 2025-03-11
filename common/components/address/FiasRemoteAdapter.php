<?php

namespace app\common\components\address;

use yii\base\Component;

class FiasRemoteAdapter extends Component implements AdapterInterface
{
    public $host = '';
    public $port = '';

    protected $url;
    protected $curl;

    public function init()
    {
        parent::init();
        $this->curl = curl_init();

        $this->url = $this->host;
        curl_setopt($this->curl, CURLOPT_TIMEOUT_MS, 2000);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
    }

        /**
     * @return string
     */
    public function getRegion($q = null, $aoid = null)
    {
        return $this->fiasGet('region', ['q' => $q, 'aoid' => $aoid]);
    }

    /**
     * @return string
     */
    public function getCity($q = null, $region = null)
    {
        return $this->fiasGet('city', ['q' => $q, 'region' => $region]);
    }

    /**
     * @return string
     */
    public function getStreet($q = null, $region = null, $city = null)
    {
        return $this->fiasGet('street', ['q' => $q, 'region' => $region, 'city' => $city]);
    }

    /**
     * @return string
     */
    public function getHouse($q = null, $street = null, $city = null)
    {
        return $this->fiasGet('house', ['q' => $q, 'street' => $street, 'city' => $city]);
    }

    /**
     * @param string[] $guid
     * @return mixed|null
     */
    public function getHouseByGuid($guid)
    {
        return $this->fiasGet('house-by-guid', ['guids' => $guid]);
    }

    /**
     * @return string
     */
    public function getRoom($q = null, $house = null)
    {
        return $this->fiasGet('room', ['q' => $q, 'house' => $house]);
    }

    /**
     * @return string
     */
    public function getFullAddress($aoguid)
    {
        return $this->fiasGet('object', ['id' => $aoguid]);
    }

    /**
     * @return mixed
     */
    public function getHistory()
    {
        return $this->fiasGet('history', []);
    }

    public function postBatchStreet(array $q)
    {
        return $this->fiasPOST('batch/street', $q);
    }

    public function postBatchHouse(array $q)
    {
        return $this->fiasPOST('batch/house', $q);
    }

    /**
     * @return mixed
     */
    public function getHi()
    {
        return $this->fiasGet('', []);
    }

    private function fiasGet($endpoint,array $params){
        $get = $this->url.'/'.$endpoint;
        if(!empty($params))
            $get .= '?'.http_build_query($params);
        curl_setopt($this->curl, CURLOPT_URL, $get);
        curl_setopt($this->curl, CURLOPT_PORT, $this->port);

        $result = curl_exec($this->curl);
        return $result ? json_decode($result, true) : null;
    }

    private function fiasPOST($endpoint,array $params){
        if(empty($params))
            return null;

        $send_data = json_encode($params);

        $get = $this->url.'/'.$endpoint;

        curl_setopt($this->curl, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($this->curl, CURLOPT_URL, $get);
        curl_setopt($this->curl, CURLOPT_PORT, $this->port);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $send_data);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . mb_strlen($send_data))
        );

        $result = curl_exec($this->curl);
        return $result ? json_decode($result, true) : null;
    }
}
