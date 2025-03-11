<?php

use app\commands\migrate\Migration;
use app\models\db\Breeds;
use app\models\db\Species;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class m181126_033308_add_update_species_breeds_2
 */
class m181126_033308_add_update_species_breeds_2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $data = [
            'собаки' => [
                'сибирский хаски',
                'чихуахуа',
                'джек-рассел-терьер',
                'кане корсо',
                'кавказская овчарка',
                'немецкий шпиц',
                'немецкая овчарка',
                'японский хин',
                'бигль',
                'папильон',
                'басенджи',
                'бишон фризе',
                'бедлингтон терьер',
                'французский бульдог',
                'пудель миниатюрный',
                'русский спаниель',
                'метис',
                'другая',
            ],
            'кошки' => [
                'сиамская',
                'корниш-рекс',
                'невская маскарадная',
                'метис',
                'другая',
            ],
        ];

        $created_at = date('Y-m-d H:i:s');
        $created = 0;

        $species = (new Query())
            ->select(['id', 'name'])
            ->from(Species::tableName())
            ->where(['in', 'name', ['кошки', 'собаки']])
            ->all();
        $map = ArrayHelper::map($species, 'name', 'id');

        foreach ($data as $speciesName => $speciesBreeds) {
            $species_id = ArrayHelper::getValue($map, $speciesName);
            if (empty($species_id)) {
                Console::output($speciesName . ' not found');
                continue;
            }
            foreach ($speciesBreeds as $breedName) {
                $columns = [
                    'name' => $breedName,
                    'species_id' => $species_id,
                ];
                $exists = (new Query())
                    ->from(Breeds::tableName())
                    ->where($columns)
                    ->exists();
                if ($exists === true) {
                    continue;
                }
                $columns['created_at'] = $created_at;
                Yii::$app->db
                    ->createCommand()
                    ->insert(Breeds::tableName(), $columns)
                    ->execute();
                $created++;
            }
        }

        Console::output('Created: ' . $created);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
