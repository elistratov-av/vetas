<?php

use app\commands\migrate\Migration;

/**
 * Class m240617_085325_create_new_table_death_reason
 */
class m240617_085325_create_new_table_death_reason extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.death_reason', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'description' => $this->text()->notNull(),
        ]);

        $this->batchInsert('public.death_reason', ['name', 'description'], [
            ['D-1', 'онкология'],
            ['D-2', 'сердечная недостаточность'],
            ['D-3', 'заворот желудка'],
            ['D-4', 'ВИК(ВИЧ), лейкоз'],
            ['D-5', 'травма, не совместимая с жизнью'],
            ['D-6', 'ДВС синдром, несвертываемость крови'],
            ['D-7', 'лимфома'],
            ['D-8', 'гнойная пневмония'],
            ['D-9', 'физиологическая старость'],
            ['D-10', 'почечная недостаточность'],
            ['D-11', 'инфекции'],
            ['D-12', 'сепсис'],
            ['D-13', 'внезапная смерть'],
            ['D-14', 'генерализованные судороги, инфаркт'],
            // Добавьте остальные данные здесь
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.death_reason');
    }

}
