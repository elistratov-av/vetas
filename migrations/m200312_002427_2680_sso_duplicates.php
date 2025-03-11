<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m200312_002427_2680_sso_duplicates
 */
class m200312_002427_2680_sso_duplicates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // удаляем дублирующиеся sso_id из pet_owners

        $sql = 'select sso_id from public.pet_owners group by sso_id having count(sso_id) > 1';
        $sso_ids = $this->db
            ->createCommand($sql)
            ->queryColumn();

        Console::output('Found sso_id duplicates in pet_owners: ' . count($sso_ids));

        $totalRows = 0;
        $removedSso = 0;

        foreach ($sso_ids as $sso_id) {
            $rows = (new Query())
                ->from('public.pet_owners')
                ->where(['sso_id' => $sso_id])
                ->orderBy(['created_at' => SORT_ASC])
                ->all();
            $totalRows += count($rows);
            foreach ($rows as $row) {
                $elkOwner = (new Query())
                    ->from('elk.owners')
                    ->where([
                        'id_owner' => $row['id'],
                        'sso_id' => $sso_id,
                    ])
                    ->one();
                if (empty($elkOwner)) {
                    $this->db
                        ->createCommand()
                        ->update('public.pet_owners', ['sso_id' => null], ['id' => $row['id']])
                        ->execute();
                    $removedSso++;
                    $this->logRemoved($row);
                }
            }
        }

        Console::output('Total pet_owners with sso_id duplicates: ' . $totalRows);
        Console::output('Removed sso_id duplicates from pet_owners: ' . $removedSso);

        // проставляем sso_id тем владельцам, которые есть в elk.owners, но у которых в pet_owners не проставлены sso_id

        $sql = 'select po.id, eo.sso_id from pet_owners po inner join elk.owners eo on po.id = eo.id_owner where po.sso_id isnull';

        $rows = $this->db
            ->createCommand($sql)
            ->queryAll();

        Console::output('Found empty sso_id in pet_owners: ' . count($rows));

        $updatedSso = 0;

        foreach ($rows as $row) {
            $this->db
                ->createCommand()
                ->update('public.pet_owners', ['sso_id' => $row['sso_id']], ['id' => $row['id']])
                ->execute();
            $updatedSso++;
            $this->logUpdated($row);
        }

        Console::output('Updated empty sso_id in pet_owners: ' . $updatedSso);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200312_002427_2680_sso_duplicates cannot be reverted.\n";

        return false;
    }

    /**
     * @param array $row
     */
    private function logRemoved($row)
    {
        $str = $row['id'] . ';' . $row['sso_id'] . ';' . $row['snils'] . ';' . $row['f_fio'] . ';' . $row['i_fio'] . ';' . $row['o_fio'] . PHP_EOL;
        file_put_contents(\Yii::getAlias('@runtime') . '/logs/sso_duplicates.csv', $str, FILE_APPEND | FILE_TEXT | LOCK_EX);
    }

    /**
     * @param array $row
     */
    private function logUpdated($row)
    {
        $str = implode(';', $row) . PHP_EOL;
        file_put_contents(\Yii::getAlias('@runtime') . '/logs/sso_updated.csv', $str, FILE_APPEND | FILE_TEXT | LOCK_EX);
    }
}
