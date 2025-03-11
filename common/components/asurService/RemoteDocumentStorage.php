<?php

namespace app\common\components\asurService;

use GuzzleHttp\Client;
use yii\base\Component;

class RemoteDocumentStorage extends Component
{
    /** @var Client */
    private $client;

    /** @var string */
    public $url;

    /** @var string */
    public $login;

    /** @var string */
    public $password;

    public function init()
    {
        parent::init();

        $this->client = new Client();
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getDocumentProperties(string $id) :array
    {
        return (array)json_decode($this->sendRequest("/document/{$id}/properties")->getBody()->getContents());
    }

    /**
     * @param string $id
     * @return \Psr\Http\Message\StreamInterface
     * @throws \Exception
     */
    public function getDocument(string $id)
    {
        return $this->sendRequest("/document/{$id}/data")->getBody();
    }

    /**
     * @param $action
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function sendRequest($action)
    {
        $response = $this->client->request(
            'GET',
            $this->url . $action,
            [
                'auth' => [$this->login, $this->password]
            ]
        );

        if ($response->getStatusCode() != 200) {
            throw new \Exception('');
        }

        return $response;
    }
}
