<?php

use app\commands\migrate\Migration;

/**
 * Class m190725_093806_add_change_request_table
 */
class m190725_093806_create_change_request_table extends Migration
{
    private $table_name = 'change_request';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable( '{{%' . $this->table_name . '}}', [
            'id_request' => $this->primaryKey(),
            'entity_name' => $this->string(255)->notNull(),
            'author' => $this->integer()->notNull(),
            'author_org' => $this->integer()->notNull(),
            'create_date' => $this->date()->notNull(),
            'admin' => $this->integer(),
            'up_date' => $this->date(),
            'type' => $this->string(1)->notNull(),
            'description' => $this->text()->notNull(),
            'state' => $this->string(1)->notNull()->defaultValue('N'),

        ]);

        $this->addCommentOnColumn('{{%' . $this->table_name . '}}', 'entity_name', 'Наименование сущности справочника, по которому подается запрос изменений');
        $this->addCommentOnColumn('{{%' . $this->table_name . '}}', 'admin', 'Пользователь, обработавший запрос');
        $this->addCommentOnColumn('{{%' . $this->table_name . '}}', 'type', 'Возможные значения: С - создание, U - редактирование, D - удаление');
        $this->addCommentOnColumn('{{%' . $this->table_name . '}}', 'state', 'Возможные значения: N - новый, A - принят, D - отклонен. По умолчанию N.');

        $this->addForeignKey(
            'fk_' . $this->table_name . '_author_users_id',
            '{{%' . $this->table_name . '}}',
            'author',
            'users',
            'id',
            'CASCADE');

        $this->createIndex(
            'idx_' . $this->table_name . '_author',
            '{{%' . $this->table_name . '}}',
            'author'
        );

        $this->addForeignKey(
            'fk_' . $this->table_name . '_author_org_organizations_id',
            '{{%' . $this->table_name . '}}',
            'author_org',
            'organizations',
            'id',
            'CASCADE');

        $this->createIndex(
            'idx_' . $this->table_name . '_author_org',
            '{{%' . $this->table_name . '}}',
            'author_org'
        );

        $this->addForeignKey(
            'fk_' . $this->table_name . '_admin_users_id',
            '{{%' . $this->table_name . '}}',
            'admin',
            'users',
            'id',
            'CASCADE');

        $this->createIndex(
            'idx_' . $this->table_name . '_admin',
            '{{%' . $this->table_name . '}}',
            'admin'
        );

        $this->createIndex(
            'idx_' . $this->table_name . '_entity_name_type',
            '{{%' . $this->table_name . '}}',
            ['entity_name', 'type'],
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_' . $this->table_name . '_author', '{{%' . $this->table_name . '}}');
        $this->dropIndex('idx_' . $this->table_name . '_author_org', '{{%' . $this->table_name . '}}');
        $this->dropIndex('idx_' . $this->table_name . '_admin', '{{%' . $this->table_name . '}}');
        $this->dropIndex('idx_' . $this->table_name . '_entity_name_type', '{{%' . $this->table_name . '}}');
        $this->dropForeignKey('fk_' . $this->table_name . '_author_users_id', '{{%' . $this->table_name . '}}');
        $this->dropForeignKey('fk_' . $this->table_name . '_author_org_organizations_id', '{{%' . $this->table_name . '}}');
        $this->dropForeignKey('fk_' . $this->table_name . '_admin_users_id', '{{%' . $this->table_name . '}}');
        $this->dropTable('{{%' . $this->table_name . '}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190725_093806_add_change_request_table cannot be reverted.\n";

        return false;
    }
    */
}
