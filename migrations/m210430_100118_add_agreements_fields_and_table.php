<?php

use app\commands\migrate\Migration;

/**
 * Class m210430_100118_add_agreements_fields_and_table
 */
class m210430_100118_add_agreements_fields_and_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $persComment = 'Флаг для отображения наличия согласия на обработку персональных данных в рамках визита';
        //Это не внешний ключ на agreements.is_agree. Данный фдаг нужен для того, чтобы в визите сохранялась
        //информация о данном согласии даже после его отмены

        $this->addColumn('visits', 'is_agreed_pers_data', $this->boolean()->defaultValue(null));
        $this->addCommentOnColumn('visits', 'is_agreed_pers_data', $persComment);

        // Промежуточная таблица для списка данных согласий на хирургическое вмешательство
//        $this->createTable('public.' . self::AGGREMENT_TYPES, [
//            'id' => $this->smallInteger(),
//            'name' => $this->string()->notNull()->unique(),
//            'created_at' => $this->dateTime(0),
//            'created_by' => $this->integer(),
//            'updated_at' => $this->dateTime(0),
//            'updated_by' => $this->integer(),
//            // Предотвращаем добавление автоинкремента
//            'PRIMARY KEY (id)'
//        ]);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropColumn('visits', 'is_agreed_pers_data');

//        echo "m210430_100118_add_agreements_fields_and_table cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210430_100118_add_agreements_fields_and_table cannot be reverted.\n";

        return false;
    }
    */
}
