<?php

namespace app\common\components\inform;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Трейт для событий работающих с паролем
 * Необходимо зашифровать пароль перед отправлением в очередь, a перед отправкой в СПК расшифровываем в sendEventJob
 */
trait EncryptPasswordTrait
{
    /** @var string */
    public $password;

    /**
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->password = $this->encryptPassword();
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function encryptPassword()
    {
        $keyString = ArrayHelper::getValue(\Yii::$app->params, 'passwordEncryptionKey');
        if (empty($keyString)) {
            throw new InvalidConfigException('You should specify passwordEncryptionKey');
        }
        $key = base64_decode($keyString);

        return base64_encode(\Yii::$app->security->encryptByKey($this->password, $key));
    }
}
