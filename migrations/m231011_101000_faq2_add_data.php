<?php

use app\commands\migrate\Migration;

/**
 * Class m231011_101000_faq2_add_data
 */
class m231011_101000_faq2_add_data extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $query = new \yii\db\Query();
        $rows = $query
            ->select(['id'])
            ->from('public.faq2_group')
            ->where(['code' => 'general_group'])
            ->all();
        if (empty($rows)) {
            $command = Yii::$app->db->createCommand();
            $command
                ->insert('public.faq2_group', [
                    'code' => 'general_group',
                    'title' => 'Перечень вопросов',
                ])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $command = Yii::$app->db->createCommand();
        $command
            ->delete('public.faq2_group', [
                'code' => 'general_group',
            ])
            ->execute();

        return true;
    }
}
