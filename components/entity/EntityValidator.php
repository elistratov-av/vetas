<?php

namespace app\common\components\entity;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

class EntityValidator extends Validator
{
    /**
     * @param $value
     * @param null $schema
     * @param int $checkMode
     * @return int
     */
    public function validate(
        &$value,
        $schema = null,
        $checkMode = Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_VALIDATE_SCHEMA
    ) {
        $conf = $value;

        $this->validateConfig($conf);

        return parent::validate($value, $this->getSchemaObject($schema), $checkMode);
    }

    /**
     * @param $scheme
     * @return mixed
     */
    protected function getSchemaObject($scheme) {
        return json_decode(file_get_contents($scheme));
    }

    /**
     * @param $config
     */
    protected function validateConfig($config)
    {
        foreach ($config as $entity) {
            if (!isset($entity->relations) || !isset($entity->plural_relations)) {
                continue;
            }

            foreach ($entity->relations as $relation) {
                $this->checkEntityConfigExists($relation->link, $config);
            }

            foreach ($entity->plural_relations as $relation) {
                $this->checkEntityConfigExists($relation->link, $config);
            }
        }
    }

    /**
     * @param $name
     * @param $config
     */
    protected function checkEntityConfigExists($name, $config)
    {
        if (!isset($config->$name)) {
            $this->errors[] = "Не оределен конфиг для сущности {$name}";
        }
    }
}
