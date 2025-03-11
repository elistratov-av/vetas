<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m201110_095030_alter_table_colors
 */
class m201110_095030_alter_table_colors extends Migration
{
    private static $colorsTable = 'public.colors';

    private static $speciesTable = 'species';

    private static $adTable = 'found_pet.ads';

    private static $fkName = 'fk-color-to-specie-id';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete(self::$colorsTable);
        $this->update(self::$adTable, ['id_color' => null]);

        $this->addColumn(self::$colorsTable, 'species_id', $this->integer()->notNull()->after('id'));
        $this->addForeignKey(
            self::$fkName,
            self::$colorsTable,
            'species_id',
            self::$speciesTable,
            'id',
            'CASCADE',
            'CASCADE'
        );

        $specieColors = [
            'CAT' => [
                'арлекин',
                'белый',
                'биколор',
                'бледно-желтый',
                'голубо-кремовый черепаховый',
                'голубой',
                'голубой с белым',
                'другой',
                'дымчатый',
                'дымчатый золотистый',
                'золотой',
                'коричневый',
                'красный',
                'красный с белым',
                'кремовый',
                'лиловый',
                'мраморный',
                'пегий',
                'пегий',
                'светло-коричневый',
                'серебристый',
                'тигровый',
                'фавн (бежевый)',
                'циннамон (корица)',
                'черепаховый',
                'черно-красный-белый',
                'черный',
                'черный с белым',
                'шиншилла',
                'шоколадный',
            ],
            'DOG' => [
                'абрикосовый',
                'белый',
                'биколор (черный/красный)',
                'волчий',
                'голубой  с подпалом',
                'голубой с пятнами',
                'другой',
                'кремовый',
                'лиловый',
                'лиловый с белым',
                'молочный',
                'мраморный',
                'муругий',
                'палевый',
                'пегий',
                'перец с солью',
                'рыжий',
                'светло коричневый',
                'соболиный',
                'темно коричневый',
                'тигровый',
                'триколор (красный/черный/лиловый)',
                'чалый',
                'чепрачный',
                'черно-белый',
                'черный',
            ],
        ];

        foreach ($specieColors as $type => $colors) {
            $q = (new Query())->select('id')->from(self::$speciesTable)->where(['tech_name' => $type])->one();
            $id = $q['id'] ?? null;
            if (!$id) {
                continue;
            }
            $this->batchInsert(self::$colorsTable, ['name', 'species_id'], array_map(static function (string $name) use ($id) {
                return [$name, $id];
            }, $colors));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            self::$fkName,
            self::$colorsTable
        );
        $this->dropColumn(self::$colorsTable, 'species_id');

        $this->delete(self::$colorsTable);
        $this->update(self::$adTable, ['id_color' => null]);
        $this->batchInsert(self::$colorsTable, ['name'], [
            ['черный'],
            ['белый'],
            ['лиловый'],
            ['рыжий'],
            ['кремовый'],
            ['темно-коричневый'],
            ['светло-коричневый'],
            ['молочный'],
            ['чалый'],
            ['тигровый'],
            ['пегий'],
            ['черно-белый'],
            ['чепрачный'],
            ['мраморный'],
            ['абрикосовый'],
            ['палевый'],
        ]);
    }
}
