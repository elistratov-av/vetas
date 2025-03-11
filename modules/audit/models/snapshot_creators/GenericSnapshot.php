<?php

namespace app\modules\audit\models\snapshot_creators;

abstract class GenericSnapshot
{

    /**
     * Версия создателя снимков
     *
     * @return int
     */
    public function getVersion()
    {
        return 2;
    }

    /**
     * Возвращает слепок
     *
     * @param integer $parent_entity_id
     * @return string JSON
     */
    function create($parent_entity_id)
    {
        return $this->queryFromBD($parent_entity_id);
    }

    /**
     * Возвращает слепок из бд
     *
     * @param integer $parent_entity_id
     * @return array|null
     */
    abstract protected function queryFromBD($parent_entity_id);

}
