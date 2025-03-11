<?php

namespace app\common\toolkit;

use yii\web\IdentityInterface;

class User extends \app\common\components\rbac\User
{
    public function login(IdentityInterface $identity, $duration = 0)
    {
        if ($this->beforeLogin($identity, false, $duration)) {
            $this->switchIdentity($identity, $duration);
            $id = $identity->getId();
            $ip = '127.0.0.1';
            if ($this->enableSession) {
                $log = "User '$id' logged in from $ip with duration $duration.";
            } else {
                $log = "User '$id' logged in from $ip. Session not enabled.";
            }

            $this->regenerateCsrfToken();

            \Yii::info($log, __METHOD__);
            $this->afterLogin($identity, false, $duration);
        }

        return !$this->getIsGuest();
    }

    /**
     * Regenerates CSRF token
     *
     * @since 2.0.14.2
     */
    protected function regenerateCsrfToken()
    {
    }
}
