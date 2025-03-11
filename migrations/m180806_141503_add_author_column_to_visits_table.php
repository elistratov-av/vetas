<?php

use yii\db\Migration;

/**
 * Handles adding author to table `visits`.
 */
class m180806_141503_add_author_column_to_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'author', $this->integer());

        $this->createIndex(
            'idx-visits-author',
            'visits',
            'author'
        );

        // add foreign key for table `tmc_types`
        $this->addForeignKey(
            'fk-visits-author',
            'visits',
            'author',
            'specialists',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'author');
    }
}
