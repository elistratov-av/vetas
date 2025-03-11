<?php

use app\commands\migrate\Migration;
use app\common\components\inform\jobs\RefreshSubscriptionsJob;

/**
 * Class m191007_082224_refresh_subscriptions
 */
class m191007_082224_refresh_subscriptions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
select pet_owners.id as id_owner, pet_owners.sso_id from contacts
join pet_owners on pet_owners.id = contacts.entity_id
where 
    contacts.id in (select id_contact from subscription.subscriptions where subscription_id is not null)
    and pet_owners.sso_id is not null
SQL;

        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                \Yii::$app->subscription_queue->push(new RefreshSubscriptionsJob([
                    'id_owner' => $row['id_owner'],
                    'sso_id' => $row['sso_id']
                ]));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191007_082224_refresh_subscriptions cannot be reverted.\n";

        return false;
    }

}
