<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m181030_135219_insert_3_addresses
 */
class m181030_135219_insert_3_addresses extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        if (!($dist = $this->findRecord('districts', ['name' => 'Краснопахорское']))) {
            $this->insert('districts', ['name' => 'Краснопахорское', 'id_area' => 9]);
            $id_kras = $this->db->getLastInsertID('districts_id_seq');
        } else {
            $id_kras = $dist['id'];
        }
        if (!($dist = $this->findRecord('districts', ['name' => 'Марушкинское']))) {
            $this->insert('districts', ['name' => 'Марушкинское', 'id_area' => 8]);
            $id_maru = $this->db->getLastInsertID('districts_id_seq');
        } else {
            $id_maru = $dist['id'];
        }
        if (!($dist = $this->findRecord('districts', ['name' => 'Кленовское']))) {
            $this->insert('districts', ['name' => 'Кленовское', 'id_area' => 9]);
            $id_klen = $this->db->getLastInsertID('districts_id_seq');
        } else {
            $id_klen = $dist['id'];
        }

        $addr1 = 'деревня Красная Пахра, дом 37а';
        if (!$this->findRecord('addresses', ['name' => $addr1])) {
            $this->insert('addresses', ['name' => $addr1, 'id_district' => $id_kras, 'id_area' => 9,
                'latitude' => 0, 'longitude' => 0]);
        }

        $addr2 = 'деревня Анкудиново, вл.43';
        if (!$this->findRecord('addresses', ['name' => $addr2])) {
            $this->insert('addresses', ['name' => $addr2, 'id_district' => $id_maru, 'id_area' => 8,
                'latitude' => 0, 'longitude' => 0]);
        }

        $addr3 = 'деревня Кленово, ул.Центральная, вл.11';
        if (!$this->findRecord('addresses', ['name' => $addr3])) {
            $this->insert('addresses', ['name' => $addr3, 'id_district' => $id_klen, 'id_area' => 9,
                'latitude' => 0, 'longitude' => 0]);
        }


    }

    /**
     * @param string $tableName
     * @param array $condition
     *
     * @return array
     */
    private function findRecord($tableName, $condition)
    {
        return (new Query())
            ->from($tableName)
            ->where($condition)
            ->limit(1)
            ->one();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181030_135219_insert_3_addresses cannot be reverted.\n";

        return false;
    }
    */

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181030_135219_insert_3_addresses cannot be reverted.\n";

        return false;
    }
}
