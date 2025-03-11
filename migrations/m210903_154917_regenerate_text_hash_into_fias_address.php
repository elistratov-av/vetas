<?php

use app\commands\migrate\Migration;

/**
 * Class m210903_154917_regenerate_text_hash_into_fias_address
 */
class m210903_154917_regenerate_text_hash_into_fias_address extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1.
        // Перегенирация хешей в fias_addresses
        Yii::$app->db->createCommand("
           UPDATE fias_addresses set text_hash = (
                SELECT upper(md5(concat_ws('|',
                                           aoguid,
                                           region,
                                           city,
                                           street,
                                           house,
                                           room,
                                           houseguid,
                                           roomguid,
                                           cityguid,
                                           streetguid,
                                           regionguid,
                                           description)))
                FROM fias_addresses as t1
                WHERE fias_addresses.id = t1.id
            )
        ")->execute();

        // 2.
        // Удаляем хэши в fias_addresses у тех адресов, что пренадлежат животному.
        // Это нужно для того, чтобы логика бэка не назначила этот адрес владельцу
        Yii::$app->db->createCommand("
            UPDATE fias_addresses
            SET text_hash = null
            WHERE id in (
                SELECT fias_addresses.id
                FROM fias_addresses
                         INNER JOIN pets ON fias_addresses.id = pets.id_fias_address
                where text_hash is not null
            )
        ")->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210903_154917_regenerate_text_hash_into_fias_address cannot be reverted.\n";

        return true;
    }
}
