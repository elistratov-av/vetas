<?php

declare(strict_types = 1);

namespace app\common\api\messages;

use ReflectionClass;
use yii\base\BaseObject;
use yii\web\HeaderCollection;

/**
 * Message represents a base HTTP message.
 *
 * @property HeaderCollection $headers The header collection. Note that the type of this property differs in
 * getter and setter. See [[getHeaders()]] and [[setHeaders()]] for details.
 */
class Message extends BaseObject implements \JsonSerializable
{
    /**
     * @var HeaderCollection headers.
     */
    protected $headers = [];

    /**
     * Sets the HTTP headers associated with HTTP message.
     *
     * @param array|HeaderCollection $headers headers collection or headers list in format: [headerName => headerValue]
     * @return $this self reference.
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Returns the header collection.
     * The header collection contains the HTTP headers associated with HTTP message.
     *
     * @return HeaderCollection the header collection
     */
    public function getHeaders(): HeaderCollection
    {
        if (!is_object($this->headers)) {
            $headerCollection = new HeaderCollection();
            if (is_array($this->headers)) {
                foreach ($this->headers as $name => $value) {
                    if (!is_int($name)) {
                        $headerCollection->set($name, $value);
                        continue;
                    }
                    // parse raw header :
                    $rawHeader = $value;
                    if (strpos($rawHeader, 'HTTP/') === 0) {
                        $parts = explode(' ', $rawHeader, 3);
                        $headerCollection->add('http-code', $parts[1]);
                    } elseif (($separatorPos = strpos($rawHeader, ':')) !== false) {
                        $name = strtolower(trim(substr($rawHeader, 0, $separatorPos)));
                        $value = trim(substr($rawHeader, $separatorPos + 1));
                        $headerCollection->add($name, $value);
                    } else {
                        $headerCollection->add('raw', $rawHeader);
                    }
                }
            }
            $this->headers = $headerCollection;
        }

        return $this->headers;
    }

    /**
     * Adds more headers to the already defined ones.
     *
     * @param array $headers additional headers in format: [headerName => headerValue]
     * @return $this self reference.
     */
    public function addHeaders(array $headers): self
    {
        $headerCollection = $this->getHeaders();
        foreach ($headers as $name => $value) {
            $headerCollection->add($name, $value);
        }

        return $this;
    }

    /**
     * Checks of HTTP message contains any header.
     * Using this method you are able to check cookie presence without instantiating [[HeaderCollection]].
     *
     * @return bool whether message contains any header.
     */
    public function hasHeaders(): bool
    {
        if (is_object($this->headers)) {
            return $this->headers->getCount() > 0;
        }

        return !empty($this->headers);
    }

    /**
     * Composes raw header lines from [[headers]].
     * Each line will be a string in format: 'header-name: value'.
     *
     * @return array raw header lines.
     */
    public function composeHeaderLines(): array
    {
        if (!$this->hasHeaders()) {
            return [];
        }
        $headers = [];
        foreach ($this->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }

        return $headers;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $class = new ReflectionClass($this);
        $values = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $name = $property->getName();
                $values[$name] = $this->$name;
            }
        }

        return $values;
    }
}
