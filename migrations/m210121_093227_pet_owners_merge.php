<?php

use app\commands\migrate\Migration;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class m210121_093227_pet_owners_merge
 */
class m210121_093227_pet_owners_merge extends Migration
{
    private static $table = 'public.pet_owners';

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $insert = $this
            ->query([
                'id' => 'id_main_owner',
                'sso_id' => new Expression('ARRAY_AGG(sso_id) FILTER (WHERE sso_id IS NOT NULL)'),
                'snils' => new Expression('ARRAY_AGG(snils) FILTER (WHERE snils IS NOT NULL)'),
            ])
            ->where([
                'and',
                ['is_deleted' => false],
                [
                    'or',
                    ['is not', 'sso_id', null],
                    ['is not', 'snils', null],
                ],
                // Select owners where parent has empty sso_id | snils
                [
                    'in',
                    'id_main_owner',
                    $this->query(['id'])->where([
                        'and',
                        ['is_main' => true],
                        [
                            'or',
                            ['is', 'sso_id', null],
                            ['is', 'snils', null],
                        ],
                    ]),
                ],
            ])
            ->groupBy('id_main_owner')
            // Reduce owners that have more that one sso_id | snils
            ->having([
                'and',
                ['<=', new Expression('COUNT(sso_id)'), 1],
                ['<=', new Expression('COUNT(snils)'), 1],
            ]);

        if (!$this->query([], ['t' => $insert])->count()) {
            return;
        }
        $tmpTable = 'tmp_' . uniqid('', false);
        $this->createTable($tmpTable, [
            'id' => $this->integer()->notNull(),
            'sso_id' => $this->string(),
            'snils' => $this->string(),
        ]);
        try {
            $this->execute(sprintf(
                'INSERT INTO %s %s',
                $tmpTable,
                $this
                    ->query(['id', 'sso_id' => new Expression('sso_id[1]'), 'snils' => new Expression('snils[1]')], ['t' => $insert])
                    ->createCommand()
                    ->getRawSql()
            ));
            // Import sso_id
            $this->execute(sprintf(
                'UPDATE %s po SET sso_id = tmp.sso_id FROM %s tmp WHERE po.id = tmp.id AND po.sso_id IS NULL',
                self::$table,
                $tmpTable
            ));
            // Import snils
            $this->delete($tmpTable, [
                'or',
                ['is', 'snils', null],
                [
                    'in',
                    'id',
                    $this->query(['id'])->where([
                        'and',
                        ['is_deleted' => false],
                        ['is_main' => true],
                        ['is not', 'snils', null],
                    ]),
                ],
            ]);
            $this->update(self::$table, ['snils' => null], [
                'in',
                'snils',
                $this->query(['snils'], $tmpTable),
            ]);
            $this->execute(sprintf(
                'UPDATE %s po SET snils = tmp.snils FROM %s tmp WHERE po.id = tmp.id AND tmp.snils IS NOT NULL',
                self::$table,
                $tmpTable
            ));
            // Drop sso_id duplicates
            $this->delete($tmpTable);
            $this->execute(sprintf(
                'INSERT INTO %s %s',
                $tmpTable,
                $this
                    ->query(['id', 'sso_id'])
                    ->where([
                        'and',
                        ['is_deleted' => false],
                        ['is_main' => true],
                        ['is not', 'sso_id', null],
                    ])
                    ->createCommand()
                    ->getRawSql()
            ));
            $this->update(self::$table, ['sso_id' => null], [
                'in',
                new Expression('(id_main_owner,sso_id)'),
                $this->query(['id', 'sso_id'], $tmpTable),
            ]);
        } finally {
            $this->dropTable($tmpTable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
    }

    /**
     * @param array $select
     * @param mixed $table
     * @return Query
     */
    private function query(array $select = [], $table = null): Query
    {
        $q = (new Query())->from($table ?? self::$table);
        if (!count($select)) {
            return $q;
        }

        return $q->select($select);
    }
}
