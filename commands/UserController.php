<?php

namespace app\commands;

use app\common\components\Jwt;
use app\common\models\UserModel;
use app\models\db\admin\AdminUser;
use app\models\db\Specialists;
use app\modules\adminfstek\components\UserLogManager;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\di\Instance;

class UserController extends Controller
{
    /**
     * @param string $userName
     * @param string $password
     * @return int
     */
    public function actionCreate($userName, $password)
    {
        $account = UserModel::findByLogin($userName);
        if (!empty($account)) {
            $this->stdout(sprintf("User %s already exists.\n", $userName));

            return ExitCode::DATAERR;
        }

        $hash = \Yii::$app->getSecurity()->generatePasswordHash($password);
        $account = new UserModel();
        $account->login = $userName;
        $account->password = $hash;
        $account->f_fio = 'Тестов';
        $account->i_fio = 'Тест';
        $account->sex = 'f';
        $account->birthday = '2020-01-01';

        if (!$account->save()) {
            $this->stdout("Internal error. Could not create user.\n");

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout(
            sprintf(
                "User %s has been created with password \"%s\".\n",
                $userName,
                $password
            )
        );

        return ExitCode::OK;
    }

    /**
     * @param string $userName
     * @param string $password
     * @return int
     */
    public function actionChangePassword($userName, $password)
    {
        $account = UserModel::findByLogin($userName);
        if (empty($account)) {
            $this->stdout(sprintf("User %s not found.\n", $userName));

            return ExitCode::DATAERR;
        }

        $account->password = \Yii::$app->getSecurity()->generatePasswordHash($password);

        if (!$account->save()) {
            $this->stdout("Internal error.\n");

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout(
            sprintf(
                "Password has been changed for the user \"%s\".\n",
                $userName
            )
        );

        return ExitCode::OK;
    }

    /**
     * @param string $userName
     * @param string $password
     * @param int    $id_specialist
     * @param int    $duration
     * @return int
     */
    public function actionToken($userName, $password, $id_specialist, $duration = null)
    {
        $user = UserModel::findByLogin($userName);
        if (empty($user)) {
            $this->stdout(sprintf("User %s not found.\n", $userName));

            return ExitCode::DATAERR;
        }

        if (!\Yii::$app->getSecurity()->validatePassword($password, $user->password)) {
            $this->stdout(sprintf("Invalid password for user %s\n", $userName));

            return ExitCode::DATAERR;
        }

        $user->id_specialist = $id_specialist;

        /* @var Jwt $jwt */
        $jwt = Instance::ensure('jwt', Jwt::class);
        if (isset($duration)) {
            $jwt->timeToLife = $duration;
        }

        /* @var \Lcobucci\JWT\Token $token */
        $token = $jwt->createToken($user);

        $this->stdout((string)$token . "\n");

        return ExitCode::OK;
    }

    /**
     * блокировать тех, кто не логинился больше 90 дней
     *
     * @return int
     */
    public function actionBlockInactiveUsers()
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P90D'));
        $dateTo = $date->format('Y-m-d H:i:s');

        /* @var $users \app\common\models\UserModel[] */
        $users = UserModel::find()
            ->where([
                'or',
                ['is_blocked' => false],
                ['is_blocked' => null],
            ])
            ->andWhere(['<', 'last_login', $dateTo])
            ->all();

        foreach ($users as $user) {
            $user->is_blocked = true;
            $result = $user->save(true, ['is_blocked', 'updated_at']);
            UserLogManager::autoInactiveApiUserBlock($user, $result);
        }

        /* @var $users \app\models\db\admin\AdminUser[] */
        $users = AdminUser::find()
            ->where([
                'or',
                ['is_blocked' => false],
                ['is_blocked' => null],
            ])
            ->andWhere(['<', 'last_login', $dateTo])
            ->all();

        foreach ($users as $user) {
            $user->is_blocked = true;
            $result = $user->save(true, ['is_blocked', 'updated_at']);
            UserLogManager::autoInactiveAdminUserBlock($user, $result);
        }

        return ExitCode::OK;
    }

    /**
     * @param int    $id
     * @param int    $id_specialist
     * @param string $roleName
     * @return int
     */
    public function actionAddRole($id, $id_specialist, $roleName)
    {
        $user = UserModel::findOne(['id' => $id]);
        if ($user === null) {
            $this->stdout(sprintf("User %s not found.\n", $id));

            return ExitCode::DATAERR;
        }

        /* @var $specialist \app\models\db\Specialists */
        $specialist = Specialists::findOne([
            'id' => $id_specialist,
            'id_user' => $id,
        ]);
        if ($specialist === null) {
            $this->stdout(sprintf("Specialist %s not found.\n", $id));

            return ExitCode::DATAERR;
        }

        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->authManager;
        $role = $auth->getRole($roleName);

        if (empty($role)) {
            $this->stdout(sprintf("Role %s not found.\n", $roleName));

            return ExitCode::DATAERR;
        }

        if ($auth->getAssignment($roleName, $id, $id_specialist) !== null) {
            $this->stdout(sprintf("User %s already has role.\n", $id));

            return ExitCode::OK;
        }

        $auth->assign($role, $user->id, $id_specialist);
        $this->stdout(sprintf("Role %s assigned.\n", $roleName));

        return ExitCode::OK;
    }

    /**
     * @param int    $id
     * @param int    $id_specialist
     * @param string $roleName
     * @return int
     */
    public function actionRevokeRole($id, $id_specialist, $roleName)
    {
        $user = UserModel::findOne(['id' => $id]);
        if ($user === null) {
            $this->stdout(sprintf("User %s not found.\n", $id));

            return ExitCode::DATAERR;
        }

        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->authManager;
        $role = $auth->getRole($roleName);

        if (empty($role)) {
            $this->stdout(sprintf("Role %s not found.\n", $roleName));

            return ExitCode::DATAERR;
        }

        if ($auth->getAssignment($roleName, $id, $id_specialist) === null) {
            $this->stdout(sprintf("User %s does not have role.\n", $id));

            return ExitCode::OK;
        }

        $auth->revoke($role, $user->id, $id_specialist);
        $this->stdout(sprintf("Role %s revoked.\n", $roleName));

        return ExitCode::OK;
    }
}
