<?php

use app\commands\migrate\Migration;

/**
 * Class m200812_114759_update_multiple_visits_tables
 */
class m200812_114759_update_multiple_visits_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addCommentOnColumn('public.visits', 'variety', 'Разновидность приема: SINGLE - прием с одним животным, MULTIPLE - прием с несколькими животными, BROOD - прием с выводком');
        $this->addCommentOnColumn('public.pets', 'id_brood', 'ID выводка');

        $this->addColumn('public.visit_pets', 'created_by', $this->integer());
        $this->addColumn('public.visit_pets', 'updated_by', $this->integer());
        $this->addColumn('public.visit_pets', 'created_at', $this->dateTime(0));
        $this->addColumn('public.visit_pets', 'updated_at', $this->dateTime(0));

        $this->addColumn('public.broods', 'created_by', $this->integer());
        $this->addColumn('public.broods', 'updated_by', $this->integer());
        $this->addColumn('public.broods', 'created_at', $this->dateTime(0));
        $this->addColumn('public.broods', 'updated_at', $this->dateTime(0));

        $this->dropForeignKey('visit_fk', 'public.visit_pets');
        $this->dropForeignKey('pet_fk', 'public.visit_pets');

        $this->addForeignKey('fk_visit_pets_id_visit', 'public.visit_pets', 'id_visit', 'public.visits', 'id', 'CASCADE', 'NO ACTION');
        $this->addForeignKey('fk_visit_pets_id_pet', 'public.visit_pets', 'id_pet', 'public.pets', 'id', 'CASCADE', 'NO ACTION');

        $this->addForeignKey('fk_pets_id_brood', 'public.pets', 'id_brood', 'public.broods', 'id', 'SET NULL', 'NO ACTION');

        $this->createIndex('idx_visits_variety', 'public.visits', 'variety');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200812_114759_update_multiple_visits_tables cannot be reverted.\n";

        return false;
    }
}
