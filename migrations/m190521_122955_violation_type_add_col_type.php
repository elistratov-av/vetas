<?php

use app\commands\migrate\Migration;

/**
 * Class m190521_122955_violation_type_add_col_type
 */
class m190521_122955_violation_type_add_col_type extends Migration
{

    const TYPE_IDENT_VIOLATION = 'ident_violation';
    const TYPE_VACCINATION_VIOLATION = 'vaccination_violation';
    const TYPE_OTHER_VIOLATION = 'other_violation';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'violation_type',
            'type',
            $this->string(32)
        );

        $array = [
            self::TYPE_IDENT_VIOLATION => [
                "отсутствие идентификации",
                "отказ от идентифкации",
            ],
            self::TYPE_VACCINATION_VIOLATION => [
                "нарушение сроков вакцинации",
                "отказ от вакцинации",
            ],
            self::TYPE_OTHER_VIOLATION => [
                "нарушение правил карантина и других вет. сан. правил",
                "сокрытие падежа или массового заболевания животных",
                "нарушение правил/ порядка провоза",
                "нарушение правил обращения с биологическими отходами",
            ]
        ];

        foreach ($array as $type => $subarray){
            foreach ($subarray as $text){
                $this->update(
                    'violation_type',
                    ['type' => $type],
                    'name = :name',
                    [':name' => $text]
                );
            }
        }

        $this->execute('ALTER TABLE violation_type ALTER COLUMN type SET NOT NULL');
        $this->addCommentOnColumn('violation_type', 'type', 'Константа: тип');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('violation_type', 'type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190521_122955_violation_type_add_col_type cannot be reverted.\n";

        return false;
    }
    */
}
