<?php

use app\commands\migrate\Migration;
use app\common\models\UserModel;

/**
 * Class m190311_162334_add_sysadmin_user
 */
class m190311_162334_add_sysadmin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $userName = 'KomitetVT';
        $password = 1234;

        $hash = \Yii::$app->getSecurity()->generatePasswordHash($password);
        $user = new UserModel();
        $user->login = $userName;
        $user->password = $hash;

        if (!$user->save(false)) {
            echo "Internal error. Could not create user.\n";
            return false;
        }

        $specialist = new \app\models\db\Specialists([
            'id_organization' => 445,
            'id_user' => $user->id,
            'reg_date' => '2019-01-01',
        ]);

        if (!$specialist->save(false)) {
            echo "Internal error. Could not create specialist.\n";
            return false;
        }

        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->authManager;
        $role = $auth->getRole(\app\common\components\rbac\Role::ROLE_SYSADMIN_GOS);
        $auth->assign($role, $user->id, $specialist->id);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190311_162334_add_sysadmin_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190311_162334_add_sysadmin_user cannot be reverted.\n";

        return false;
    }
    */
}
