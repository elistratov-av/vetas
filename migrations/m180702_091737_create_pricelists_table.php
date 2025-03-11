<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pricelists`.
 */
class m180702_091737_create_pricelists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pricelists', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->notNull()->comment('Связь с организацией')
        ]);

        $this->addCommentOnTable('pricelists', 'Прайслист организации');

        $this->addForeignKey(
            'fk-pricelists-id_ogranization',
            'pricelists',
            'id_organization',
            'organizations',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pricelists-id_ogranization', 'pricelists');
        $this->dropTable('pricelists');
    }
}
