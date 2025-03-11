<?php

use app\commands\migrate\Migration;
use app\models\db\Organizations;
use app\models\db\Addresses;
/**
 * Class m230928_132524_insert_newsletter_reception_table
 */
class m231008_132524_insert_newsletter_reception_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $res = Organizations::find()->select(['id','name','id_address'])->all();
        $result = [];
        foreach ($res as $k=>$value)
        {
            $result[$k] = [
                'name' => 'Вы записаны на прием <дата, время>',
                'name_organizations' => $value['name'],
                'organization_id' => $value['id'],
                'text' => '<p>Вы записаны на прием <дата, время> в клинику <наименование огранизации> по адресу 
<адрес организации> к специалисту <ФИО врача> на следующие услуги</p>
<ul><li><наименование услуги>;</li><li><наименование услуги>;</li><li><наименование услуги>;</li></ul><Комментарий>',
                'status' => false,
                'address' => null
            ];
            if(isset($value['id_address']) && $value['id_address'] !== 0) {
                $reception = Addresses::findOne((int)$value['id_address']);
                $name = $reception['name'];

                $result[$k]['address'] = $name;
            }

        }
        $this->batchInsert('newsletter_reception', ['name', 'name_organizations', 'organization_id', 'text', 'status', 'address'], $result);



    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230928_132524_insert_newsletter_reception_table cannot be reverted.\n";

        return false;
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230928_132524_insert_newsletter_reception_table cannot be reverted.\n";

        return false;
    }
    */
}
