<?php

namespace app\common\soap\v2;

/**
 * Class WsdlGenerator
 * @package app\common\soap\v2
 */
class WsdlGenerator extends \subdee\soapserver\WsdlGenerator
{
    /**
     * @param \ReflectionMethod $method method
     */
    protected function processMethod($method)
    {
        $comment = $method->getDocComment();
        if (strpos($comment, '@soap') === false) {
            return;
        }
        $comment = strtr(
            $comment,
            array("\r\n" => "\n", "\r" => "\n")
        ); // make line endings consistent: win -> unix, mac -> unix

        $methodName = $method->getName();
        $comment = preg_replace('/^\s*\**(\s*?$|\s*)/m', '', $comment);
        $params = $method->getParameters();
        $message = array();
        $headers = array();
        $n = preg_match_all('/^@param\s+([\w\.\\\]+(\[\s*\])?)\s*?(.*)$/im', $comment, $matches);
        if ($n > count($params)) {
            $n = count($params);
        }

        if ($this->bindingStyle === self::STYLE_RPC) {
            for ($i = 0; $i < $n; ++$i) {
                $type = preg_replace('/\\\\+/', '\\', $matches[1][$i]);
                $message[$params[$i]->getName()] = array(
                    'type' => $this->processType($type),
                    'doc' => trim($matches[3][$i]),
                );
            }
        } else {
            $this->elements[$methodName] = array();
            for ($i = 0; $i < $n; ++$i) {
                $type = preg_replace('/\\\\+/', '\\', $matches[1][$i]);
                $this->elements[$methodName][$params[$i]->getName()] = array(
                    'type' => $this->processType($type),
                    'nillable' => $params[$i]->isOptional(),
                );
            }
            $message['parameters'] = array('element' => 'tns:' . $methodName);
        }

        $this->messages[$methodName . 'Request'] = $message;

        $n = preg_match_all('/^@header\s+([\w\.\\\]+(\[\s*\])?)\s*?(.*)$/im', $comment, $matches);
        for ($i = 0; $i < $n; ++$i) {
            $name = $matches[1][$i];
            $type = preg_replace('/\\\\+/', '\\', $matches[1][$i]);
            $type = $this->processType($type);
            $doc = trim($matches[3][$i]);
            if ($this->bindingStyle === self::STYLE_RPC) {
                $headers[$name] = array($type, $doc);
            } else {
                $this->elements[$name][$name] = array('type' => $type);
                $headers[$name] = array('element' => $type);
            }
        }

        if ($headers !== array()) {
            $this->messages[$methodName . 'Headers'] = $headers;
            $headerKeys = array_keys($headers);
            $firstHeaderKey = reset($headerKeys);
            $firstHeader = $headers[$firstHeaderKey];
        } else {
            $firstHeader = null;
        }

        if ($this->bindingStyle === self::STYLE_RPC) {
            if (preg_match('/^@return\s+([\w\.\\\]+(\[\s*\])?)\s*?(.*)$/im', $comment, $matches)) {
                $type = preg_replace('/\\\\+/', '\\', $matches[1]);
                $return = array(
                    'type' => $this->processType($type),
                    'doc' => trim($matches[2]),
                );
            } else {
                $return = null;
            }
            $this->messages[$methodName . 'Response'] = array('return' => $return);
        } else {
            if (preg_match('/^@return\s+([\w\.\\\]+(\[\s*\])?)\s*?(.*)$/im', $comment, $matches)) {
                $type = preg_replace('/\\\\+/', '\\', $matches[1]);
                $this->elements[$methodName . 'Response'][$methodName . 'Result'] = array(
                    'type' => $this->processType($type),
                );
            }
            $this->messages[$methodName . 'Response'] = array('parameters' => array('element' => 'tns:' . $methodName . 'Response'));
        }

        $doc = '';
        if (preg_match('/^\/\*+\s*([^@]*?)\n@/', $comment, $matches)) {
            $doc = trim($matches[1]);
        }
        $this->operations[$methodName] = array(
            'doc' => $doc,
            'headers' => $firstHeader === null ? null : array(
                'input' => array(
                    $methodName . 'Headers',
                    $firstHeaderKey
                )
            ),
        );
    }

    /**
     * @inheritDoc
     */
    protected function createPortElement($dom, $name, $doc)
    {
        $operation = $dom->createElement('wsdl:operation');
        $operation->setAttribute('name', $name);

        $input = $dom->createElement('wsdl:input');
        $input->setAttribute('message', 'tns:' . $name . 'Request');
        $output = $dom->createElement('wsdl:output');
        $output->setAttribute('message', 'tns:' . $name . 'Response');

        $operation->appendChild($dom->createElement('wsdl:documentation', $doc));
        $operation->appendChild($input);
        $operation->appendChild($output);

        return $operation;
    }
}
