<?php

use yii\db\Migration;

/**
 * Class m180717_200059_update_visits_gov_services_change_pk
 *
 * Сущность "VisitService" дополнена атрибутом "id" являющий первичным ключом, ране первичный ключ был составным
 * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=91265804&focusedCommentId=91278203#comment-91278203
 */
class m180717_200059_update_visits_gov_services_change_pk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('visits_gov_services_pkey', 'visits_gov_services');
        $this->addColumn('visits_gov_services', 'id', $this->primaryKey());
        $this->createIndex(
            'visits_gov_services_unique',
            'visits_gov_services',
            ['id_visit', 'id_service'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('visits_gov_services_pkey', 'visits_gov_services');
        $this->dropColumn('visits_gov_services', 'id');
        $this->addPrimaryKey(
            'visits_gov_services_pkey',
            'visits_gov_services',
            ['id_visit', 'id_service']
        );
        $this->dropIndex('visits_gov_services_unique', 'visits_gov_services');
    }
}
