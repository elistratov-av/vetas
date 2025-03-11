<?php

namespace app\common\helpers;

class StringHelper
{
    /**
     * Проверяет, является ли строка валидной строкой json-a
     *
     * @param $string
     * @return bool
     */
    public static function isJSON($string): bool
    {
        // decode the JSON data
        json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        return !($error);
    }

    /**
     * Форматирует строку из snake_case в CamelCase
     *
     * @param $value
     * @return string
     */
    public static function formatSnakeToCamelCase($value)
    {
        $result = '';

        foreach (explode('_', $value) as $value) {
            if ($value === '_') {
                continue;
            }

            $result .= ucfirst($value);
        }

        return lcfirst($result);
    }

    /**
     * Форматирует строку из CamelCase в snake_case
     *
     * @param string $camelCase
     * @return string
     */
    public static function formatCamelToSnakeCase(string $camelCase)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCase));
    }
}
