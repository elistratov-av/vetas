<?php

use app\commands\migrate\Migration;

/**
 * Class m190814_034921_update_subscriptions_confirm_add_contact_value
 */
class m190814_034921_update_subscriptions_confirm_add_contact_value extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('subscription.confirm', 'contact_value', $this->string()->comment('Значение контакта (contacts.name) для проверки что контакт не изменился при подтверждении контакта'));

        $this->execute('update "subscription"."confirm" set "contact_value" = "c"."name" from "public"."contacts" "c" where "c"."id" = "subscription"."confirm"."id_contact"');

        $this->execute('delete from "subscription"."confirm" where "contact_value" isnull');

        $this->execute('alter table "subscription"."confirm" alter column "contact_value" set not null');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('subscription.subscriptions', 'contact_value');
    }
}
