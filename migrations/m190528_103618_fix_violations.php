<?php

use app\commands\migrate\Migration;

/**
 * Class m190528_103618_fix_violations
 */
class m190528_103618_fix_violations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * Опечатка
         */
        $this->update(
            'violation_type',
            ['name' => 'отказ от идентификации'],
            ['name' => 'отказ от идентифкации']
        );

        /*
         * violation_type
         */
        $this->execute('ALTER TABLE violation_type ALTER COLUMN name SET NOT NULL');

        /*
         * violation_type - новое поле TECH_NAME
         */
        $this->addColumn(
            'violation_type',
            'tech_name',
            $this->string(64)
        );
        $this->addCommentOnColumn(
            'violation_type',
            'tech_name',
            'Текстовая константа типа нарушения - используется в логике приложения'
        );

        $array = [
                "отсутствие идентификации" => 'V01_IDENT_LACK',
                "отказ от идентификации" => 'V02_IDENT_REJECTION',
                "нарушение сроков вакцинации" => 'V03_VACCINATION_DEADLINE',
                "отказ от вакцинации" => 'V04_VACCINATION_REJECTION',

                "нарушение правил карантина и других вет. сан. правил" => 'V05_OTHER_QUARANTINE_OR_VETERINARY_RULES',
                "сокрытие падежа или массового заболевания животных" => 'V06_OTHER_CONCEALMENT_DEATH_OR_MASS_DISEASE',
                "нарушение правил/ порядка провоза" => 'V07_OTHER_TRANSPORTATION_RULES',
                "нарушение правил обращения с биологическими отходами" => 'V08_OTHER_RULES_OF_BIOLOGICAL_WASTE',
        ];

        foreach ($array as $name => $tech_name){
            $this->update(
                'violation_type',
                ['tech_name' => $tech_name],
                ['name' => $name]
            );
        }

        $this->execute('ALTER TABLE violation_type ALTER COLUMN tech_name SET NOT NULL');

        /*
         * Диагноз необязателен (в частности для идентификации)
         */
        $this->execute('ALTER TABLE violation ALTER COLUMN id_disease DROP NOT NULL');

        /*
         * violation_history
         */
        $this->delete('violation_history',[
            'OR',
            ['id_inspector' => null],
            ['description' => null],
        ]);
        $this->execute('ALTER TABLE violation_history ALTER COLUMN id_inspector SET NOT NULL');
        $this->execute('ALTER TABLE violation_history ALTER COLUMN description SET NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'violation_type',
            'tech_name'
        );
        $this->execute('ALTER TABLE violation ALTER COLUMN id_disease SET NOT NULL');
        $this->execute('ALTER TABLE violation_type ALTER COLUMN name DROP NOT NULL');

        $this->execute('ALTER TABLE violation_history ALTER COLUMN id_inspector DROP NOT NULL');
        $this->execute('ALTER TABLE violation_history ALTER COLUMN description DROP NOT NULL');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190528_103618_fix_violations cannot be reverted.\n";

        return false;
    }
    */
}
