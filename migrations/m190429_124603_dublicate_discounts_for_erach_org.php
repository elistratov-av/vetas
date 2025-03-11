<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m190424_124603_dublicate_discounts_for_erach_org
 */
class m190429_124603_dublicate_discounts_for_erach_org extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function safeUp()
    {
        $this->dropIndex('partial_uniq_name_in_discount', 'discount');

        /** Только для неудаленных уникальность по имени внутри организации **/
        $create_partial_uniq_name_index = '
        CREATE UNIQUE INDEX partial_uniq_name_in_discount
        ON discount ("name", "id_organization", is_deleted) 
        WHERE is_deleted = FALSE';

        $this->execute($create_partial_uniq_name_index);

        $orgs = (new \yii\db\Query())
            ->from('visit_price as vp')
            ->join('left join', 'visits as vs', 'vs.id = vp.id_visit')
            ->join('left join', 'discount as ds', 'ds.id = vp.id_discount')
            ->where('vp.id_discount is not null and vs.id_organization is null')
            ->groupBy(['ds.id', 'vs.id_organization'])
            ->select('ds.id as ds_id, vs.id_organization as id_org')
            ->all();

        foreach ($orgs as $org) {
            $old_discount = \app\models\db\Discount::findOne($org['ds_id']);
            $new_discount = new \app\models\db\Discount();
            $new_discount->attributes = $old_discount->attributes;
            $new_discount->id_organization = $org['id_org'];

            if (!$new_discount->save()){
                Console::output(Console::ansiFormat(implode(' ', $new_discount->errors), [Console::FG_RED]));
                return 0;
            }


            $nid = $new_discount->getPrimaryKey();
            Console::output("Copy old discount $old_discount->id to new $nid with org $new_discount->id_organization");
        }

        $this->delete('discount', ['id_organization' => null]);

        $this->db->createCommand()->update(
            'auth_item',
            ['rule_name' => 'AllOrgsCompositeRule1'],
            'name = \'data.pricelist.services\' or name = \'data.pricelist.services.W\'')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190424_124603_dublicate_discounts_for_erach_org cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190424_124603_dublicate_discounts_for_erach_org cannot be reverted.\n";

        return false;
    }
    */
}
