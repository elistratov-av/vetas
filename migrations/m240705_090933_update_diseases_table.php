<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m240705_090933_update_diseases_table
 */
class m240705_090933_update_diseases_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('public.diseases', 'cod', 'code');
        $diseases = (new Query())
            ->select(['id', 'id_gost_disease'])
            ->from('public.diseases')
            ->orderBy('name')
            ->all();
        $existingCodes = [];
        foreach ($diseases as $disease) {
            $code = (new Query())
                ->select(['gost_code'])
                ->from('gost_diseases')
                ->where(['id' => $disease['id_gost_disease']])
                ->scalar();
            if ($code !== false) {
                $originalCode = $code;
                $suffix = 1;
                while (in_array($code, $existingCodes)) {
                    $code = "$originalCode.$suffix";
                    $suffix++;
                }
                $existingCodes[] = $code;
                $this->update('public.diseases', ['code' => $code], ['id' => $disease['id']]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('public.diseases', ['code' => null]);
        $this->renameColumn('public.diseases', 'code', 'cod');
        return true;
    }
}
