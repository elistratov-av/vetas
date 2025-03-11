<?php

namespace app\common\components\jcpSign;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use yii\base\Component;

class JcpSignService extends Component
{
    public $uri;

    /**
     * @param $document
     * @return string
     * @throws \Exception
     */
    public function sign($document)
    {
        $data = [
            "xmldata" => $document,
            //"version" => "SOAP 1.2 Protocol",
        ];

        $client = new Client();
        $request = new Request(
            'POST',
            $this->uri,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($client->send($request)->getBody()->getContents(), true);
        if (isset($response['errorCode']) && (int)$response['errorCode'] !== 0) {
            throw new \Exception($response['errorMessage'] ?? 'SignService: sign error');
        }

        return $this->cropEnvTags($response['result']);
    }

    /**
     * @param $document
     * @return string
     * @throws \Exception
     */
    public function getSign($document)
    {
        $data = [
            "xmldata" => $this->prepareXml($document),
            "tag" => "CoordinateTaskDataMessage"
        ];

        $client = new Client();
        $request = new Request(
            'POST',
            $this->uri,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        $response = json_decode($client->send($request)->getBody()->getContents(), true);
        if (isset($response['errorCode']) && (int)$response['errorCode'] !== 0) {
            throw new \Exception($response['errorMessage'] ?? 'SignService: sign error');
        }

        return $response['result'];
    }

    /**
     * @param string $xml
     * @return string
     */
    protected function prepareXml(string $xml) : string
    {
        $template = <<<XML
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
    <s:Header></s:Header>
    <s:Body>%xml%</s:Body>
</s:Envelope>
XML;

        return str_replace('%xml%', $xml, $template);
    }

    /**
     * @param $xml
     * @return string
     */
    protected function cropEnvTags($xml)
    {
        $sign = $this->getCertToken($xml);

        // TODO: переделать на нормальный вариант
        $startTag = <<<XML
<CoordinateTaskMessage xmlns="http://asguf.mos.ru/rkis_gu/coordinate/v6_1/" xmlns:ns1="http://asguf.mos.ru/rkis_gu/coordinate/v6_1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
XML;

        $bstStart = strpos($xml, '<s:Envelope');
        $bstLength = strpos($xml, '<CoordinateTaskMessage', 5) - $bstStart;
        $xml = substr_replace($xml, '', $bstStart, $bstLength);
        $xml = str_replace($startTag, '', $xml);
        $xml = str_ireplace('</s:Body>', '', $xml);
        $xml = str_ireplace('</s:Envelope>', '', $xml);

        $result = $startTag . $sign .$xml;
        return $result;
    }

    /**
     * @param $xml
     * @return mixed
     */
    protected function getCertToken($xml) {
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $xml = str_ireplace('s:', '', $xml);
        $xml = str_replace('SOAP-ENV:', '', $xml);
        $xml = str_replace('wsse:', '', $xml);
        $xml = str_replace('wsu:', '', $xml);
        $xml = str_replace('wsu:Id="CoordinateTaskDataMessage"', '', $xml);
        $xml = str_replace('wsu:Id="CertId"', '', $xml);
        $simple = simplexml_load_string($xml);
        $children = $simple->xpath('/Envelope/Header/Security/BinarySecurityToken');
        foreach ($children as $i) {
            $tok = (string)$i;
        }
        $signInfo = $simple->xpath('/Envelope/Header/Security/Signature/KeyInfo');
        $X509 = $signInfo[0]->addChild('X509Data');
        $X509->addChild('X509Certificate', $tok);
        $signature = $simple->xpath('/Envelope/Header/Security/Signature');
        $res = $signature[0]->asXML();
        return $res;
    }

    public function signOdopm($document)
    {
        $data = [
            'xmldata' => $document,
        ];

        $client = new Client();
        $request = new Request(
            'POST',
            $this->uri,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($client->send($request)->getBody()->getContents(), true);
        if (isset($response['errorCode']) && (int)$response['errorCode'] !== 0) {
            throw new \Exception($response['errorMessage'] ?? 'SignService: sign error');
        }
        return $response['result'];
    }
}
