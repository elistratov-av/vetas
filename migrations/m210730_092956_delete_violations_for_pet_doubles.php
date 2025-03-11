<?php

use app\commands\migrate\Migration;

/**
 * Class m210730_092956_delete_violations_for_pet_doubles
 */
class m210730_092956_delete_violations_for_pet_doubles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $cancelId = \app\models\db\ViolationCancellation::find()->where(['is_need_cancellation_details' => true])->one()->id_cancellation;

        $this->execute('update violation
set state = \'C\',
id_cancellation = '.$cancelId.',
comment = \'Автоматическая отмена нарушения для животного помеченого как дубль\',
cancellation_details = \'животное отмечено как дубль\'
where id_violation IN (SELECT v.id_violation FROM violation v
left join pets p on p.id = v.id_pet
where p.id_main_pet is not null and v.state not in (\'F\',\'C\')
)
');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
