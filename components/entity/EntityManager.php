<?php

namespace app\common\components\entity;


use yii\helpers\BaseInflector;

class EntityManager
{
    protected $config_manager;
    protected $config_data = [];

    /**
     * @var EntityNestedCollection $entityNestedCollection
     */
    protected $entityNestedCollection;

    /**
     * @var EntityNestedCollection[] $nestedCollections
     */
    protected $nestedCollections;

    /**
     * EntityManager constructor.
     * @param EntityConfigManager $config_manager
     * @param EntityNestedCollection $entityNestedCollection
     * @throws EntityException
     */
    public function __construct(EntityConfigManager $config_manager, EntityNestedCollection $entityNestedCollection)
    {
        $this->config_manager = $config_manager;
        $this->entityNestedCollection = $entityNestedCollection;

        $this->config_data = $config_manager->getConfig();
    }

    /**
     * @param string $name
     * @return null|EntityInstance
     * @throws EntityException
     */
    public function getEntity(string $name)
    {
        if (!isset($this->config_data[$name])) {
            throw new EntityException("Не найден конфиг для сущности {$name}");
        }

        if (isset($this->config_data[$name]['meta']['class'])) {
            $class = $this->config_data[$name]['meta']['class'];
            return new $class;
        }
        $entity_data = $this->config_data[$name];

        if(isset($entity_data['meta']['active']) && $entity_data['meta']['active'] === false){
            return null;
        }

        if (isset($entity_data['meta']['virtual']) && $entity_data['meta']['virtual'] === true) {
            throw new EntityException("Сущность {$name} объявлена как виртуальная");
        }

        while (isset($entity_data['meta']['parent']) && $entity_data['meta']['parent']) {
            $base = $entity_data['meta']['parent'];
            $entity_data = array_merge_recursive($entity_data, $this->config_data[$base]);
            unset($entity_data['meta']['parent']);
        }

        $entityInstance = new EntityInstance($entity_data, $name);

        foreach ($this->findNestedCollections($name, $entityInstance) as $col) {
            $entityInstance->addNestedCollection($col);
        }

        return $entityInstance;
    }

    public function getAllEntitiesNames()
    {
        $names = array_keys($this->config_data);
        foreach ($names as $index => $name){
            if (isset($this->config_data[$name]['meta']['active']) && $this->config_data[$name]['meta']['active'] === false)
                unset($names[$index]);

            if (isset($this->config_data[$name]['meta']['virtual']) && $this->config_data[$name]['meta']['virtual'] === true)
                unset($names[$index]);
        }
        return $names;
    }

    public function getRawConfig()
    {
        return $this->config_data;
    }

    /**
     * @param $entityName
     * @param EntityInstance $entityInstance
     * @return array
     */
    protected function findNestedCollections($entityName, EntityInstance $entityInstance)
    {
        $cols = [];
        $names = $this->getAllEntitiesNames();

        foreach ($names as $entname) {
            $colname = $entityName . '-' . $entname;
            $inverseName = $entname . '-' . $entityName;

            if (isset($this->config_data[$colname])) {
                $collectionData = $this->config_data[$colname];
            } elseif (isset($this->config_data[$inverseName])) {
                if ($this->excludeInversion($inverseName)) {
                    continue;
                }
                $collectionData = $this->invertRelations($this->config_data[$inverseName]);
            } else {
                continue;
            }

            $collectionData['meta']['entityName'] = $entityName;
            $collectionData['meta']['collectionName'] = $entname;

            $colInstance = new $this->entityNestedCollection;
            $colInstance->init([$colname => $collectionData]);

            $cols[] = $colInstance;
        }

        if ($pluralRelations = $entityInstance->getPluralRelations()) {
            foreach ($pluralRelations as $relation) {
                /** @var EntityNestedCollection $colInstance */
                $colInstance = new $this->entityNestedCollection;
                $colInstance->init($relation, true);

                $cols[] = $colInstance;
            }
        }

        return $cols;
    }

    /**
     * @param array $collectionData
     * @return mixed
     */
    private function invertRelations($collectionData)
    {
        $inverse = array_reverse($collectionData['relations']);
        $collectionData['relations'] = $inverse;
        $collectionData['meta']['inverse'] = true;

        return $collectionData;
    }

    /**
     * Фикс бага с нехваткой памяти для некоторых автоматических инверсивных relations (например gov_services-visits)
     * @param string $name
     * @return bool
     */
    private function excludeInversion($name)
    {
        $excluded = [
            'visits-gov-services',
            'pet-owners-contact-types',
            'organizations-contact-types',
        ];

        return in_array($name, $excluded, true);
    }
}
