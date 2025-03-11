<?php

namespace app\modules\adminfstek\components;

use yii\helpers\ArrayHelper;

/**
 * Class DbSession
 * @package app\modules\adminfstek\components
 */
class DbSession extends \yii\web\DbSession
{
    public $sessionTable = 'admin.sessions_admin';

    /**
     * @inheritDoc
     */
    protected function composeFields($id = null, $data = null)
    {
        $fields = parent::composeFields($id, $data);

        $id_user = \Yii::$app->getUser()->getId();
        $valid_until = date('Y-m-d H:i:s', isset($fields['expire']) ? $fields['expire'] : time() + 900);
        $last_active_at = date('Y-m-d H:i:s');
        $ip = \Yii::$app->request->getUserIP();
        $ua = \Yii::$app->request->getUserAgent();
        $ua_hash = empty($ua) ? null : md5($ua);
        $created_at = $last_active_at;
        $updated_at = $last_active_at;

        $fields = array_merge($fields, compact('id_user', 'valid_until', 'last_active_at', 'ip', 'ua', 'ua_hash', 'created_at', 'updated_at'));

        return $fields;
    }

    /**
     * @inheritDoc
     */
    public function getTimeout()
    {
        return (int) ArrayHelper::getValue(\Yii::$app->params, 'jwt_token_ttl', 86400);
    }
}
