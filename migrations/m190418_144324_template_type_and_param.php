<?php

use app\commands\migrate\Migration;

/**
 * Class m190418_144324_template_type_and_param
 */
class m190418_144324_template_type_and_param extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'descriptions_templates',
            'template_type',
            $this->string(50)
        );

        \app\models\db\DescriptionsTemplates::updateAll(
            ['template_type' => \app\models\db\DescriptionsTemplates::TYPE_DIAGNOSIS,]
        );

        $this->execute('ALTER TABLE public.descriptions_templates ALTER COLUMN template_type SET NOT NULL;');

        $this->addColumn(
            'descriptions_templates',
            'param_tech_name',
            $this->string(50)
        );

        $this->addColumn(
            'descriptions_templates',
            'param_tech_name',
            'Параметр отчета к которому относиться данный комментарий'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'descriptions_templates',
            'template_type'
        );

        $this->dropColumn(
            'descriptions_templates',
            'param_tech_name'
        );

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190418_144324_template_type_and_param cannot be reverted.\n";

        return false;
    }
    */
}
