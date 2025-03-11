<?php

use app\commands\migrate\Migration;

/**
 * Class m190812_135449_change_subscriptions_on_delete_restriction
 */
class m190812_135449_change_subscriptions_on_delete_restriction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE subscription.subscriptions ALTER COLUMN id_contact DROP NOT NULL");
        $this->execute('ALTER TABLE subscription.subscriptions DROP CONSTRAINT "fk-subscriptions-id_contact"');

        $sql = <<<SQL
ALTER TABLE subscription.subscriptions
  ADD CONSTRAINT "fk-subscriptions-id_contact" FOREIGN KEY (id_contact)
      REFERENCES public.contacts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE SET NULL;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
