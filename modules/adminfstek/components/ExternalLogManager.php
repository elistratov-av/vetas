<?php

namespace app\modules\adminfstek\components;

use app\models\db\audit\LogExternalAuth;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class ExternalLogManager
 * @package app\modules\adminfstek\components
 */
class ExternalLogManager extends Component
{
    use LogNotificationsTrait;

    /**
     * @var string
     */
    public $serviceName;
    /**
     * @var string
     */
    public $protocol;
    /**
     * @var string
     */
    public $interface;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!isset($this->serviceName)) {
            throw new InvalidConfigException('Service name not specified for external service log');
        }
    }

    /**
     * @param bool   $is_success
     * @param string $protocol
     * @param string $interface
     */
    public function logAuth($is_success = true, $protocol = null, $interface = null)
    {
        try {
            $model = new LogExternalAuth([
                'service_name' => $this->serviceName,
                'is_success' => $is_success,
                'protocol' => ($protocol ?? $this->protocol),
                'interface' => ($interface ?? ($this->interface ?? \Yii::$app->controller->action->getUniqueId())),
                'ip' => \Yii::$app->request->getUserIP(),
            ]);
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error(
                'Failed to log external service auth: ' . $this->serviceName . "\n" . $e->getMessage(),
                'external-service-log'
            );
            self::notifyLogFailed(LogExternalAuth::tableName());
        }
    }
}
