<?php


namespace app\modules\foundPet\models;

/**
 * Class CurlRequest
 * @package app\modules\foundPet\queue
 * @property-read $url
 * @property-read $request_headers
 * @property-read $request
 * @property-read $response
 * @property-read $response_headers
 * @property-read $response_code
 * @property-read $exec_uid UID заданный пользователем при инициализации или случайно сгенерированный, если не был задан
 * @property-read $exec_type
 * @property-read $curl_error
 *
 */
class CurlRequest
{
    protected $url;
    protected $request_headers;
    protected $request;
    protected $response;
    protected $response_headers = [];
    protected $response_code;
    protected $curl_error;
    protected $exec_uid;
    protected $exec_type;

    /**
     * @var resource|false|CurlHandle
     */
    protected $ch;


    /**
     * CurlRequest constructor.
     *
     * @param string $url
     * @param string[] $headers
     * @param string $post
     * @param bool $exec_uid
     * @param string $exec_type
     */
    public function __construct($url, $headers, $post = '', $exec_uid = false, $exec_type='')
    {
        $this->url = $url;
        $this->request_headers = $headers;
        $this->request = $post;

        // EXEC UID and type
        $this->exec_uid = empty($exec_uid) ? str_replace('.', '', uniqid('', true)) : $exec_uid;
        $this->exec_type = $exec_type;

        $this->ch = curl_init();
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
    }

    public function __isset($name)
    {
        if (property_exists($this, $name) == false) {
            return false;
        }
        return isset($this->{$name});
    }

    public function dump()
    {
        return [
            'exec_uid' => $this->exec_uid,
            'exec_type' => $this->exec_type,
            'url' => $this->url,
            'request_headers' => $this->request_headers,
            'request' => $this->request,
            'response_code' => $this->response_code,
            'response' => $this->response,
            'response_headers' => $this->response_headers,
            'curl_error' => $this->curl_error,

        ];
    }

    public function exec()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->request_headers);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->request);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this,'callbackHeaderFunction'));

        $this->response = curl_exec($this->ch);
        $this->curl_error = curl_error($this->ch);
        $this->response_code = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE);

    }


    public function close()
    {
        return curl_close($this->ch);
    }

    /**
     * @param $resource
     * @param $headerString
     * @return false|int
     */
    protected function callbackHeaderFunction($resource, $headerString)
    {
        $header = trim($headerString, "\n\r");
        if ($header !== '') {
            $this->response_headers[] = $header;
        }
        return mb_strlen($headerString, '8bit');
    }
}