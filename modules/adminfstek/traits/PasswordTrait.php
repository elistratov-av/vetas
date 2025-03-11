<?php

namespace app\modules\adminfstek\traits;

use app\common\components\inform\events\OneTimePasswordEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\models\UserModel;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PasswordHistory;
use app\modules\adminfstek\helpers\MailHelper;
use app\modules\adminfstek\helpers\PasswordHelper;

/**
 * Trait PasswordTrait
 * @package app\modules\adminfstek\traits
 */
trait PasswordTrait
{
    /**
     * @param string $email
     * @param string $password
     * @return bool
     */
    protected function sendPassword($email, $password)
    {
        $emailType = ContactTypes::find()->where(['type' => ContactTypes::TYPE_EMAIL])->one();
        $contacts[] = new Contacts([
            'id_contact_type' => $emailType->id,
            'name' => $email,
        ]);
        
        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new OneTimePasswordEvent([
            'contacts' => $contacts,
            'password' => $password,
            'id_author' => \Yii::$app->user->id,
        ]));

        $dir = \Yii::getAlias('@webroot') . '/mailer.txt';
        $date = date('Y-m-d H:i:s');
        $data = "$date: $email - $password \r\n";
        $result = file_put_contents($dir, $data, FILE_APPEND);

        return ($result !== false);
    }

    /**
     * @return string
     */
    protected function generatePassword()
    {
        return PasswordHelper::generatePassword();
    }

    /**
     * @param \app\common\models\UserModel|\app\models\db\admin\AdminUser $user
     * @return bool
     */
    protected function savePasswordHistory($user)
    {
        $model = new PasswordHistory([
            'id_user' => $user->id,
            'target' => $this->resolveTarget($user),
            'password' => $user->password,
        ]);

        return $model->save();
    }

    /**
     * @param \app\common\models\UserModel|\app\models\db\admin\AdminUser $user
     * @return int
     */
    private function resolveTarget($user)
    {
        return ($user instanceof UserModel) ? PasswordHistory::TARGET_FRONTEND : PasswordHistory::TARGET_ADMIN;
    }
}
