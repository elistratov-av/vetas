<?php

namespace app\modules\audit\models\snapshot_creators;

use app\models\db\Organizations;
use yii\db\ActiveQuery;

class SnapshotOrganization extends GenericSnapshot
{
    /**
     * Возвращает слепок из бд
     *
     * @param integer $parent_entity_id
     * @return array|null
     */
    protected function queryFromBD($parent_entity_id)
    {
        return Organizations::find()
            ->select([
                'name',
                'short_name',
                'inn',
                'kpp',
                'ogrn',
                // Требуются для связей with
                'id',
                'id_fias_address',
            ])
            ->with(['contacts' => function ($query) {
                /** @var $query ActiveQuery **/
                $query
                    ->select([
                        'contacts.id',
                        'contacts.entity_id',
                        'contacts.name AS name',
                        'contacts.confirmed',
                        'contacts.main_flag',
                        'contact_types.name AS type',
                    ])
                    ->leftJoin(
                        'contact_types',
                        'contacts.id_contact_type = contact_types.id'
                    );
            }])
            ->with(['fias_addresses' => function ($query) {
                /** @var $query ActiveQuery **/
                $query->select([
                    'id',
                    'full_address'
                ]);
            }])
            ->where([
                'id' => $parent_entity_id
            ])
            ->asArray()
            ->one();
    }
}
