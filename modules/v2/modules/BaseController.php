<?php

namespace app\modules\v2\modules;

use app\common\components\JwtHttpBearerAuth;
use app\modules\v2\common\rbac\AccessTrait;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class BaseController
 * @package app\modules\v2\modules
 */
class BaseController extends Controller
{
    use AccessTrait;

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'cors' => [
                'class' => Cors::class,
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/vnd.api+json' => Response::FORMAT_JSON,
                ],
            ],
            'http_authenticator' => [
                'class' => JwtHttpBearerAuth::class,
                'except' => ['options_bumper'],
            ],
        ];
    }

    public function actionOptions_bumper(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function bindActionParams($action, $params): array
    {
        $request = \Yii::$app->getRequest();
        $incomingData = $request->getBodyParams();

        $reflection = new \ReflectionMethod(\get_class($this), $action->actionMethod);
        $method_params = $reflection->getParameters();
        $sorted_params = [];

        if (!empty($method_params)) {

            foreach ($method_params as $param) {

                $name = $param->getName();

                if (array_key_exists($name, $incomingData)) {// Значение в массиве

                    $sorted_params[$name] = $incomingData[$name];

                } elseif (isset($params[$name])) {// Значение в поле объекта

                    $sorted_params[$name] = $params[$name];

                } elseif ($param->isDefaultValueAvailable()) { // Значение по-умолчанию

                    $sorted_params[$name] = $param->getDefaultValue();

                } else {
                    throw new BadRequestHttpException("Параметр $name не передан");
                }
            }
        }

        $this->actionParams = $sorted_params;

        return $sorted_params;
    }

    /**
     * @param \yii\base\Model $model
     * @param string|null     $defaultMessage
     * @throws \yii\web\BadRequestHttpException
     */
    protected function errorResponse(\yii\base\Model $model, string $defaultMessage = null): void
    {
        $defaultMessage = $defaultMessage ?? 'Ошибка';
        $errors = $model->getErrorSummary(true);
        throw new BadRequestHttpException(empty($errors) ? $defaultMessage : implode("\n", array_values($errors)));
    }
}
