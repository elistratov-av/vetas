<?php

namespace app\common\dto;

use app\common\helpers\StringHelper;
use ArrayAccess;

/**
 * Class AbstractDto
 *
 * @package App\Dto\Response
 * @author Aleksandr Roik
 */
abstract class AbstractDto implements ArrayAccess
{
    /**
     * AbstractDto constructor.
     *
     * @param array|object $data
     */
    public function __construct($data = [])
    {
        $this->mergeData($data);
    }

    /**
     * Обновляет/устанавливает свойства новыми значениями
     *
     * @param array|object $data
     * @return $this
     */
    public function mergeData($data): self
    {
        if (!$data) {
            return $this;
        }

        //Идем по входящих данных
        foreach ($data as $property => $value) {
            $methodName = 'set' . StringHelper::formatSnakeToCamelCase($property);
            $camelProperty = StringHelper::formatSnakeToCamelCase($property);
            $value = $this->formatValue($value);

            //1. Ищем метод по маске "set" + "НазваниеПараметра"
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($value);
                //2. Ищем свойство в формате camelCase
            } elseif (property_exists($this, $camelProperty)) {
                $this->{$camelProperty} = $value;
                //3. Ищем свойство в с тем же названием
            } elseif (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }

        return $this;
    }

    /**
     * @param mixed $value
     * @return string|int
     */
    protected function formatValue($value)
    {
        if ($value === "") {
            $value = null;
        }

        return $value;
    }

    /**
     * @return array
     */
    public function toAttributes(): array
    {
        $attr = [];
        foreach (get_object_vars($this) as $key => $value) {
            $attr[StringHelper::formatCamelToSnakeCase($key)] = $value;
        }

        return $attr;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        if (property_exists($this, $offset)) {
            return $this->{$offset};
        }
    }

    public function offsetSet($offset, $value)
    {
        if (property_exists($this, $offset)) {
            return $this->{$offset} = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            return $this->{$offset} = null;
        }
    }
}
