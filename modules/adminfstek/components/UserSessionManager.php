<?php

namespace app\modules\adminfstek\components;

use app\common\models\UserModel;
use app\models\db\admin\Session;
use yii\base\Component;

/**
 * Class UserSessionManager
 * @package app\modules\adminfstek\components
 */
class UserSessionManager extends Component
{
    /**
     * @param \Lcobucci\JWT\Token $token
     */
    public function logSession($token)
    {
        $id_user = $token->getClaim('uid');
        $token_hash = md5((string)$token);
        $valid_until = date('Y-m-d H:i:s', $token->getClaim('exp'));
        $last_active_at = date('Y-m-d H:i:s');
        $ip = \Yii::$app->request->getUserIP();
        $ua = \Yii::$app->request->getUserAgent();
        $ua_hash = empty($ua) ? null : md5($ua);

        $this->deleteExpiredUserSessions($id_user);

        $sessions = $this->findAllUserSessions($id_user);

        $columns = compact('token_hash', 'valid_until', 'last_active_at', 'ip', 'ua', 'ua_hash');

        $found = false;
        foreach ($sessions as $session) {
            if ($session->ip == $ip && $session->ua_hash == $ua_hash) {
                $found = true;
                $session->setAttributes($columns);
                $session->update(false);
            }
        }

        if ($found === false) {
            $columns['id_user'] = $id_user;
            $session = new Session($columns);
            $session->save(false);
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function terminateSession($id)
    {
        $session = $this->findSession($id);

        if ($session === null) {
            return false;
        }

        $user = $this->findUser($session->id_user);

        if ($user === null) {
            $this->deleteAllUserSessions($session->id_user);
            return true;
        }

        $user->setAuthKey();
        if ($user->update(false, ['auth_key', 'updated_at'])) {
            $this->deleteAllUserSessions($user->id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function deleteAllExpiredSessions()
    {
        return Session::deleteAll(['<=', 'valid_until', date('Y-m-d H:i:s')]);
    }

    /**
     * @param int    $id_user
     * @param string $token
     */
    public function deleteCurrentSession($id_user, $token)
    {
        Session::deleteAll([
            'id_user' => $id_user,
            'token_hash' => md5($token),
        ]);
    }

    /**
     * @param int $id
     * @return \app\common\models\UserModel|null
     */
    private function findUser($id)
    {
        return UserModel::findOne(['id' => $id]);
    }

    /**
     * @param int $id
     * @return \app\models\db\admin\Session|null
     */
    private function findSession($id)
    {
        return Session::findOne(['id' => $id]);
    }

    /**
     * @param int $id_user
     * @return \app\models\db\admin\Session[]
     */
    private function findAllUserSessions($id_user)
    {
        return Session::findAll(['id_user' => $id_user]);
    }

    /**
     * @param int $id_user
     * @return int
     */
    private function deleteAllUserSessions($id_user)
    {
        return Session::deleteAll(['id_user' => $id_user]);
    }

    /**
     * @param int $id_user
     * @return int
     */
    private function deleteExpiredUserSessions($id_user)
    {
        return Session::deleteAll([
            'and',
            ['id_user' => $id_user],
            ['<=', 'valid_until', date('Y-m-d H:i:s')],
        ]);
    }
}
