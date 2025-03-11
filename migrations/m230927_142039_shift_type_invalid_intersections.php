<?php

use app\commands\migrate\Migration;

/**
 * Handles the creation of table `{{%shift_type_invalid_intersections}}`.
 */
class m230927_142039_shift_type_invalid_intersections extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('shift_type_invalid_intersections', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->notNull(),
            'id_type_first' => $this->integer()->notNull(),
            'id_type_second' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-shift_type_invalid_intersections_id_organization_organizations_id',
            'shift_type_invalid_intersections',
            'id_organization',
            'organizations',
            'id'
        );

        $this->addForeignKey(
            'fk-shift_type_invalid_intersections_id_type_first_shift_type_id',
            'shift_type_invalid_intersections',
            'id_type_first',
            'shift_type',
            'id'
        );

        $this->addForeignKey(
            'fk-shift_type_invalid_intersections_id_type_second_shift_type_id',
            'shift_type_invalid_intersections',
            'id_type_second',
            'shift_type',
            'id'
        );

        $this->createIndex('idx_unique_shift_type_invalid_intersections',
            'shift_type_invalid_intersections',
            [
                'id_organization',
                'id_type_first',
                'id_type_second',
            ],
            true);

        $this->addCommentOnTable('shift_type_invalid_intersections', 'Недопустимые пересечения типов смен');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('shift_type_invalid_intersections');
    }

}
