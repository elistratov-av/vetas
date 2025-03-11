<?php

namespace app\common\components\signService\models;

use yii\base\Model;
use SimpleXMLElement;

/**
 * Class ValidateAdapter
 * @package app\common\components\sign
 */
class ValidateAdapter extends Model
{
    protected $xml = null;

    public function setXml(SimpleXMLElement $xml)
    {
        if (empty($this->xml)) {
            $this->xml = $xml;
            $this->xml->registerXPathNamespace('tccs', 'http://www.roskazna.ru/eb/sign/types/sgv');
            $this->xml->registerXPathNamespace('cst', 'http://www.roskazna.ru/eb/sign/types/cryptoserver');
        }
    }

    public function __debugInfo()
    {
        return [
            'globalStatus' => $this->getGlobalStatus(),
            'inn' => $this->getInn(),
            'ogrn' => $this->getOgrn(),
            'address' => $this->getAddress(),
            'email' => $this->getEmail(),
            'organization' => $this->getOrganization(),
            'organizationName' => $this->getOrganizationName(),
        ];
    }

    /**
     * Результат валидации подписанного документа.
     * @return string
     */
    public function getGlobalStatus()
    {
        $globalStatus = $this->xml->xpath('//tccs:globalStatus');

        return empty($globalStatus)
            ? 'invalid'
            : (string)array_shift($globalStatus);
    }

    /**
     * ИНН из подписанного документа.
     * @return string
     */
    public function getInn()
    {
        $inn = $this->xml->xpath('//cst:reference//cst:INN/cst:numeric');

        return empty($inn)
            ? ''
            : (string)array_shift($inn);
    }

    /**
     * ОГРН из подписанного документа.
     * @return string
     */
    public function getOgrn()
    {
        $ogrn = $this->xml->xpath('//cst:reference//cst:OGRN/cst:numeric');

        return empty($ogrn)
            ? ''
            : (string)array_shift($ogrn);
    }

    /**
     * Адрес из подписанного документа.
     * @return string
     */
    public function getAddress()
    {
        $address = $this->xml->xpath('//cst:reference//cst:StreetAddress/cst:UTF8String');

        return empty($address)
            ? ''
            : (string)array_shift($address);
    }

    /**
     * Email из подписанного документа.
     * @return string
     */
    public function getEmail()
    {
        $email = $this->xml->xpath('//cst:reference//cst:EmailAddress');

        return empty($email)
            ? ''
            : (string)array_shift($email);
    }

    /**
     * Название организации (юридическое) из подписанного документа.
     * @return string
     */
    public function getOrganization()
    {
        $org = $this->xml->xpath('//cst:reference//cst:OrganizationName/cst:UTF8String');

        return empty($org)
            ? ''
            : (string)array_shift($org);
    }

    /**
     * Общее название организации из подписанного документа.
     * @return string
     */
    public function getOrganizationName()
    {
        $org = $this->xml->xpath('//cst:reference//cst:CommonName/cst:UTF8String');

        return empty($org)
            ? ''
            : (string)array_shift($org);
    }


}
