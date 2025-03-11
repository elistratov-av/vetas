<?php

namespace app\common\soap;

use app\modules\soap\models\etp\response\nillable;
use app\modules\soap\models\etp\response\XmlItemInterface;
use DOMDocument;
use DOMElement;
use DOMException;
use DOMText;
use yii\base\Arrayable;
use yii\helpers\StringHelper;

class XmlFormatter
{
    /**
     * @var string the XML version
     */
    public $version = '1.0';

    /**
     * @var string the name of the elements that represent the array elements with numeric keys.
     */
    public $itemTag = 'item';

    /**
     * @param string $rootTag
     * @param string $ns
     * @param array $attributes
     * @param array $data
     * @return string
     */
    public function makeXml(string $rootTag, string $ns, array $attributes = [], array $data)
    {
        $dom = new DOMDocument($this->version, 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $root = $dom->createElementNS($ns, $rootTag);
        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $root->setAttribute($name, $value);
            }
        }
        $dom->appendChild($root);
        $this->buildXml($root, $data);

        return $root->C14N();
    }

    /**
     * @param DOMElement $element
     * @param mixed $data
     */
    protected function buildXml($element, $data)
    {
        if (is_array($data) ||
            ($data instanceof \Traversable && !$data instanceof Arrayable)
        ) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement($this->getValidXmlElementName($name));
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = new DOMElement($this->getValidXmlElementName($name));
                    $element->appendChild($child);
                    $child->appendChild(new DOMText($this->formatScalarValue($value)));
                }
            }
        } elseif (is_object($data)) {
            if ($data instanceof nillable) {
                $element->setAttribute('xsi:nil', 'true');
                return;
            } elseif($data instanceof XmlItemInterface) {
                $child = new DOMElement(StringHelper::basename($data->getXmlTagName()));
                $element->appendChild($child);
            } else {
                $child = new DOMElement(StringHelper::basename(get_class($data)));
                $element->appendChild($child);
            }

            if ($data instanceof Arrayable) {
                $this->buildXml($child, $data->toArray());
            } else {
                $array = [];
                foreach ($data as $name => $value) {
                    $array[$name] = $value;
                }
                $this->buildXml($child, $array);
            }
        } else {
            $element->appendChild(new DOMText($this->formatScalarValue($data)));
        }
    }

    /**
     * Formats scalar value to use in XML text node.
     *
     * @param int|string|bool|float $value a scalar value.
     * @return string string representation of the value.
     * @since 2.0.11
     */
    protected function formatScalarValue($value)
    {
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_float($value)) {
            return StringHelper::floatToString($value);
        }
        return (string) $value;
    }

    /**
     * Returns element name ready to be used in DOMElement if
     * name is not empty, is not int and is valid.
     *
     * Falls back to [[itemTag]] otherwise.
     *
     * @param mixed $name
     * @return string
     * @since 2.0.12
     */
    protected function getValidXmlElementName($name)
    {
        if (empty($name) || is_int($name) || !$this->isValidXmlName($name)) {
            return $this->itemTag;
        }

        return $name;
    }

    /**
     * Checks if name is valid to be used in XML.
     *
     * @param mixed $name
     * @return bool
     * @see http://stackoverflow.com/questions/2519845/how-to-check-if-string-is-a-valid-xml-element-name/2519943#2519943
     * @since 2.0.12
     */
    protected function isValidXmlName($name)
    {
        try {
            new DOMElement($name);
            return true;
        } catch (DOMException $e) {
            return false;
        }
    }
}
