<?php

namespace app\common\soap;

use yii\base\BaseObject;

class MessageParser extends BaseObject
{
    /**
     * @param $xml
     * @return bool|\SimpleXMLElement
     */
    public static function parse($xml)
    {
        if (!is_string($xml) or empty($xml)) {
            trigger_error('$xml must be a non-empty string.', E_USER_WARNING);
            return false;
        }

        if (stripos($xml, 'xmlns=') !== false) {
            $xml = preg_replace('~[\s]+xmlns=[\'"].*?[\'"]~i', null, $xml);
        }

        // Остаются скринящие слэши, которые вызываеют падение функции simplexml_load_string()
        $xml = str_replace('\\', '', $xml);

        if (preg_match_all('~xmlns:([a-z0-9]+)=~i', $xml, $matches)) {
            foreach (($namespaces = array_unique($matches[1])) as $namespace) {
                $escaped_namespace = preg_quote($namespace, '~');
                $xml = preg_replace('~[\s]xmlns:'.$escaped_namespace.'=[\'].+?[\']~i', null, $xml);
                $xml = preg_replace('~[\s]xmlns:'.$escaped_namespace.'=["].+?["]~i', null, $xml);
                $xml = preg_replace('~([\'"\s])'.$escaped_namespace.':~i', '$1'.$namespace.'_', $xml);
            }
        }

        $regexfrom = sprintf('~<([a-z0-9]+):%s~is', null);
        $xml = preg_replace($regexfrom, '<', $xml);
        $xml = preg_replace('~</([a-z0-9]+):~is', '</', $xml);

        return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    }

    /**
     * @param $xml
     * @return array
     */
    public static function xmlAsArray($xml) : array
    {
        $root = self::parse($xml);
        return [
            $root->getName() => json_decode(json_encode($root), true)
        ];
    }

    /**
     * @param $xml
     * @return string
     */
    public static function xmlAsJson($xml)
    {
        return json_encode(self::parse($xml));
    }
}
