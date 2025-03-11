<?php

namespace app\modules\v1\controllers;

use app\common\components\Jwt;
use app\common\controllers\ApiController;
use app\modules\v1\models\TokenResource;
use app\modules\v1\models\UserResource;
use Lcobucci\JWT\Token;
use Yii;
use yii\di\Instance;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class UserController extends ApiController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['http_authenticator']['except'][] = 'token';

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return array_merge(
            parent::verbs(),
            [
                'change-password' => ['PUT', 'HEAD', 'OPTIONS'],
            ]
        );
    }

    /**
     * @return TokenResource
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionToken()
    {
        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();

        /**
         * Input data validation
         */
        if (empty($data['User'])) {
            throw new BadRequestHttpException();
        }

        $data = $data['User'];
        if (empty($data['login'])) {
            throw new BadRequestHttpException();
        }

        if (empty($data['password'])) {
            throw new BadRequestHttpException();
        }

        $user = UserResource::findByLogin($data['login']);

        if (empty($user)) {
            throw new UnauthorizedHttpException();
        }

        if ($user->is_blocked) {
            throw new UnauthorizedHttpException('Пользователь заблокирован');
        }

        if ($user->isBlockedUntil()) {
            throw new UnauthorizedHttpException('Пользователь заблокирован на 30 минут');
        }

        if (!Yii::$app->getSecurity()->validatePassword($data['password'], $user->password)) {
            $user->addLoginAttempt();
            if (!$user->checkLoginAttempts()) {
                $user->blockUntil();
                throw new UnauthorizedHttpException('Пользователь заблокирован на 30 минут');
            }
            throw new UnauthorizedHttpException();
        }

        /**
         * @var Jwt $jwt
         */
        $jwt = Instance::ensure('jwt', Jwt::class);
        /**
         * @var Token $token
         */
        $token = $jwt->createToken($user);

        $resource = new TokenResource();
        $resource->token = (string)$token;
        $resource->expired = $token->getClaim('exp', 0);
        $resource->setResourceRelationship('user', $user);

        $user->last_login = new Expression('NOW()');
        $user->update();

        return $resource;
    }

    /**
     * @param $id
     * @return null|UserResource
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        if (!empty($id)) {
            $resource = UserResource::findOne($id);

            if (!empty($resource)) {
                return $resource;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionChangePassword()
    {
        /** @var UserResource $user */
        $user = Yii::$app->user->identity;

        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();

        if (empty($data['User'])) {
            throw new BadRequestHttpException();
        }

        $data = $data['User'];

        if (empty($data['old_password']) || empty($data['new_password'])) {
            throw new BadRequestHttpException();
        }

        if (!Yii::$app->getSecurity()->validatePassword($data['old_password'], $user->password)) {
            throw new UnauthorizedHttpException("Неверно указан текущий пароль пользователя");
        }

        $user->password = \Yii::$app->getSecurity()->generatePasswordHash($data['new_password']);

        if ($user->save()) {
            return UserResource::findOne(['id' => $user->id]);
        }elseif (!$user->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $user;
    }
}
