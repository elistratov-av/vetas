<?php

namespace app\common\components\entity;

use Symfony\Component\Finder\Finder;

class EntityConfigManager
{
    protected $validator;
    protected $config_path;
    protected $config_data = [];
    protected $schemePath;

    public function __construct()
    {
        $this->schemePath = realpath(\Yii::getAlias('@app') . '/config/entitys_schema.json');
        $this->config_path = realpath(\Yii::getAlias('@app') . '/config/entities');
        $this->validator = new EntityValidator();
    }

    /**
     * @return array
     * @throws EntityException
     */
    public function getConfig(): array
    {
        $finder = new Finder();
        $finder->files()->in($this->config_path)->name('*.php');

        $entities = [];
        foreach ($finder as $file) {
            $content = require $file;
            $entities = array_merge($entities, $content);
        }

        $values = json_decode(json_encode($entities));
        $this->validator->validate($values, $this->schemePath);
        if (!$this->validator->isValid()) {
            throw new EntityException(print_r($this->validator->getErrors(), true));
        }

        if (empty($entities)) {
            throw new EntityException("Не найден ни один конфиг сущности в дириктрории:$this->config_path");
        }

        return $entities;
    }
}
