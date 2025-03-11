<?php

use app\commands\migrate\Migration;

/**
 * Class m181112_071501_delete_regcert_dublicates
 */
class m181112_071501_delete_regcert_dublicates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //удаляем дубликаты регистрационных удостоверений по ид животного
        $sql = <<<SQL
DELETE FROM reg_certificates
WHERE id_pet IN (
 SELECT id_pet   FROM reg_certificates
 GROUP BY id_pet
 HAVING count(*)
 >1
)
RETURNING reg_certificates.id;
SQL;
        //удаляем связанные с удосоверениями, записи из таблицы файлов.
        $deleted_ids = $this->db->createCommand($sql)->queryColumn();
        foreach ($deleted_ids as $id){
            $path = $this->db->createCommand("delete from files where entity_type = 'reg_certificate' and entity_id = $id returning path")->queryScalar();
            print($path . PHP_EOL);
            if($path)
                unlink(\Yii::getAlias('@app/web').DIRECTORY_SEPARATOR.$path);
        }

        $this->dropIndex('idx-reg_certificates-id_pet', 'reg_certificates');
        $this->createIndex('idx-reg_certificates-id_pet', 'reg_certificates', 'id_pet', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181112_071501_delete_regcert_dublicates cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181112_071501_delete_regcert_dublicates cannot be reverted.\n";

        return false;
    }
    */
}
