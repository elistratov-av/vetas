<?php

use app\commands\migrate\Migration;
use app\common\models\UserModel;

/**
 * Class m210419_051612_add_new_violation_cancellation
 */
class m210419_051612_add_new_violation_cancellation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('violation_cancellation', 'tech_name', $this->string()->defaultValue(null)->comment('Техническое имя для причин отмены нарушений выполняемых системой'));
        $this->addColumn('users', 'is_system_user', $this->boolean()->defaultValue(false)->comment('Флаг определяющий пользователя как текущее приложение'));

        $userName = 'system_user';
        $password = 'ce90036a5573bdb37d503d99992d4f7f';
        $hash = \Yii::$app->getSecurity()->generatePasswordHash($password);
        $user = new UserModel();
        $user->login = $userName;
        $user->password = $hash;
        $user->is_system_user = true;

        if (!$user->save(false)) {
            echo "Internal error. Could not create user.\n";
            return false;
        }

        $this->insert(
            'violation_cancellation', [
                'description' => 'Нарушение не взято в работу',
                'is_need_cancellation_details' => false,
                'tech_name' => 'expired',
            ]
        );
        $this->insert(
            'violation_cancellation', [
                'description' => 'Внесены данные о вакцинации',
                'is_need_cancellation_details' => false,
                'tech_name' => 'vaccinated',
            ]
        );
        $this->insert(
            'violation_cancellation', [
                'description' => 'Внесены данные об идентификации',
                'is_need_cancellation_details' => false,
                'tech_name' => 'identified',
            ]
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Yii::$app->db->createCommand("DELETE FROM violation_cancellation WHERE tech_name IN('expired', 'vaccinated', 'identified')")->execute();
        Yii::$app->db->createCommand("DELETE FROM users WHERE login = 'system_user'")->execute();

        $this->dropColumn('violation_cancellation', 'tech_name');
        $this->dropColumn('users', 'is_system_user');
    }
}
