<?php

use app\commands\migrate\Migration;

class m231006_121212_create_table_newsletter_info_organization extends Migration
{
    private const TABLE_NEWSLETTER = 'newsletter_info';
    private const TABLE = 'newsletter_info_organization';
    private const TABLE_ORGANIZATION = 'public.organizations';

    public function safeUp()
    {
        $this->createTable(self::TABLE, [
            'organization_id' => $this->integer()->notNull(),
            'newsletter_info_id' => $this->integer()->notNull(),
        ]);

        $this->addColumn(self::TABLE_NEWSLETTER, 'user_id', $this->integer());
        $this->dropColumn(self::TABLE_NEWSLETTER, 'user');
        $this->dropColumn(self::TABLE_NEWSLETTER, 'id_organizations');

        $this->addForeignKey(
            'fk-newsletter_info_organization-newsletter_info_id',
            self::TABLE,
            'newsletter_info_id',
            self::TABLE_NEWSLETTER,
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-newsletter_info_organization-organization_id',
            self::TABLE,
            'organization_id',
            self::TABLE_ORGANIZATION,
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable(self::TABLE);
        $this->dropColumn(self::TABLE_NEWSLETTER, 'user_id');
        $this->addColumn(self::TABLE_NEWSLETTER, 'user', $this->integer());
        $this->addColumn(self::TABLE_NEWSLETTER, 'id_organizations', $this->string());
    }
}
