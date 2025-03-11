<?php

use app\commands\migrate\Migration;

/**
 * Class m200828_040823_create_found_pet_queue
 */
class m200828_040823_create_found_pet_queue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
create table if not exists found_pet.queue
(
	id serial not null
		constraint queue_pkey
			primary key,
	channel varchar(255) not null,
	job jsonb not null,
	pushed_at integer not null,
	reserved_at integer,
	done_at integer,
	delay integer not null,
	ttr integer not null,
	attempt integer,
	priority integer default 1024 not null
);
SQL;

        $this->execute($sql);

        $sql = <<<SQL
create index if not exists "idx-found_pet_queue-channel"
	on found_pet.queue (channel);
SQL;

        $this->execute($sql);

        $sql = <<<SQL
create index if not exists "idx-found_pet_queue-priority"
	on found_pet.queue (priority);
SQL;

        $this->execute($sql);

        $sql = <<<SQL
create index if not exists "idx-found_pet_queue-reserved_at"
	on found_pet.queue (reserved_at);
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = 'drop table if exists found_pet.queue';
        $this->execute($sql);
    }
}
