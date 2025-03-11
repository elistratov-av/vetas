<?php

use app\commands\migrate\Migration;

/**
 * Class m210917_084157_cancel_violation_for_reg_expired_pets
 */
class m210917_084157_cancel_violation_for_reg_expired_pets extends Migration
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
comment = \'Автоматическая отмена нарушения для животного снятого с учета\',
cancellation_details = \'животное снято с учета\'
where id_violation IN (SELECT v.id_violation FROM violation v
left join pets p on p.id = v.id_pet
where p.id_reg_expire_reason is not null and v.state not in (\'F\',\'C\')
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
