<?php

use app\commands\migrate\Migration;

/**
 * Class m181008_135359_add_breeds
 */
class m181008_135359_add_breeds extends Migration
{
    protected $breeds = [
        ["другая", 25],
        ["другая", 9],
        ["беспородная", 25],
        ["метис", 25]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $command = $this->db->createCommand("insert into breeds(name, species_id) values(:name, :species_id)
            on conflict do nothing");

        foreach ($this->breeds as $breed) {
            $command->bindValues([
                'name' => $breed[0],
                'species_id' => $breed[1]
            ])->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
