<?php

namespace app\modules\v2\modules\user\models;

use app\common\components\Jwt;
use app\common\components\rbac\Role;
use app\models\db\Specialists;
use app\modules\adminfstek\traits\PasswordTrait;
use app\modules\v2\common\rbac\FrontendAccessHelper;
use Yii;
use yii\base\Model;
use app\common\models\UserModel;
use yii\db\Expression;
use yii\di\Instance;
use yii\httpclient\Client;

/**
 * Class SudirLoginModel
 * @package app\modules\v2\modules\user\models
 */
class SudirLoginModel extends Model
{
    use PasswordTrait;

    /**
     * @var string
     */
    public $code;
    /**
     * @var UserModel
     */
    private $user;
    /**
     * @var bool
     */
    private $requireSelectOrganization = false;

    /**
     * @return UserModel
     */
    public function getUser(): UserModel
    {
        return $this->user;
    }

    /**
     * @param UserModel $user
     */
    public function setUser(UserModel $user): void
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'trim'],
            [['code'], 'string', 'skipOnEmpty' => false],
        ];
    }

    /**
     * @return bool
     */
    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        $basic = base64_encode($_ENV['SUDIR_CLIENT_ID'] . ':' . $_ENV['SUDIR_SECRET']);
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($_ENV['SUDIR_URL'] . '/blitz/oauth/te?grant_type=authorization_code&code=' . $this->code . '&redirect_uri=' . $_ENV['SUDIR_REDIRECT_URI'])
            ->setHeaders([
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'ru-RU,ru;q=0.8,en;q=0.5,en-US;q=0.3',
                'Authorization' => 'Basic ' . $basic,
            ])
            ->send();

        if (empty($response->data['access_token'])) {
            $this->addError('code', 'Не получен токен от Судир ');
            return;
        }

        $accessToken = $response->data['access_token'];
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($_ENV['SUDIR_URL'] . '/blitz/oauth/me')
            ->setHeaders([
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'ru-RU,ru;q=0.8,en;q=0.5,en-US;q=0.3',
                'Authorization' => 'Bearer ' . $accessToken,
            ])
            ->send();

        if (empty($response->data['uid'])) {
            $this->addError('code', 'Не получен Uid от Судир ');
            return;
        }

        $this->user = UserModel::findBySudirUid($response->data['uid']);

        if (empty($this->user)) {
            $this->addError('code', 'Обратитесь в тех. поддержку, нет аккаунта для Uid: ' . $response->data['uid']);
            return;
        }

        $this->checkUser('login');

        return $this->hasErrors() ? false : $this->prepareOutput();
    }

    /**
     * @param string $action
     * @return void
     */
    private function checkUser($action)
    {
        if ($this->hasErrors()) {
            return;
        }
        $attribute = 'user';
        if ($this->user === null) {
            $this->addError('code', 'Введен неверный логин или пароль');

            return;
        }
        if ($this->user->is_blocked) {
            $this->addError('code', 'Ваша учетная запись заблокирована');

            return;
        }
        if ($this->user->isBlockedUntil()) {
            $this->addError('code', 'Ваша учетная запись заблокирована до ' . \DateTime::createFromFormat('Y-m-d H:i:s', $this->user->block_until)->format('H:i:s d.m.Y'));

            return;
        }

        // Очищаем лог попыток входа
        $this->user->clearLoginAttempts();

        $specialists = $this->user->specialists;
        if (empty($specialists)) {
            $this->addError($attribute, 'Отсутствуют права доступа. Не указано место работы');

            return;
        }

        $errors = [];

        foreach ($specialists as $i => $specialist) {
            if (empty($specialist->id_organization)) {
                $errors[] = 'Отсутствуют права доступа. Не указано место работы';
                unset($specialists[$i]);
                continue;
            }
            if ($specialist->organization === null) {
                $errors[] = 'Отсутствуют права доступа. Не указано место работы';
                unset($specialists[$i]);
                continue;
            }
            if (!empty($specialist->expel_date) && $specialist->isExpelledAtDate()) {
                $errors[] = 'Специалист был уволен ' . $specialist->expel_date . ' из "' . $specialist->organization->short_name . '"';
                unset($specialists[$i]);
                continue;
            }
            if (!$this->hasRolesInOrganization($this->user->id, $specialist->id)) {
                $errors[] = 'Отсутствуют права доступа. Не указана роль на месте работы "' . $specialist->organization->short_name . '"';
                unset($specialists[$i]);
                continue;
            }
        }

        if (empty($specialists)) {
            $this->addErrors([$attribute => $errors]);
            // блокируем попытку логина
            if ($this->user->block()) {
                $this->addError($attribute, 'Ваша учетная запись заблокирована. Обратитесь к администратору');
            }

            return;
        }

        if (count($specialists) != count($this->user->specialists)) {
            $this->user->populateRelation('specialists', $specialists);
        }

        if ($action != 'login') {
            return;
        }

        if (count($specialists) == 1) {
            $specialist = reset($specialists);
            $this->user->setId_specialist($specialist->id);
            Yii::$app->user->login($this->user);
        } else {
            $this->requireSelectOrganization = true;
        }
    }

    /**
     * @param int $id_organization
     * @return array|bool
     */
    public function selectOrganization($id_organization)
    {
        $specialist = Specialists::findOne([
            'id_user' => $this->user->id,
            'id_organization' => $id_organization,
        ]);

        if ($specialist === null) {
            $this->addError('id_specialist', 'Отсутствуют права доступа');

            return false;
        }
        if ($specialist->organization === null) {
            $this->addError('id_specialist', 'Отсутствуют права доступа. Не указано место работы');

            return false;
        }
        if (!empty($specialist->expel_date) && $specialist->isExpelledAtDate()) {
            $this->addError('id_specialist', 'Специалист был уволен ' . $specialist->expel_date . ' из "' . $specialist->organization->short_name . '"');

            return false;
        }
        if (!$this->hasRolesInOrganization($this->user->id, $specialist->id)) {
            $this->addError('id_specialist', 'Отсутствуют права доступа. Не указана роль на месте работы "' . $specialist->organization->short_name . '"');

            return false;
        }

        $this->checkUser('select-organization');
        if ($this->hasErrors()) {
            return false;
        }

        $this->user->setId_specialist($specialist->id);
        Yii::$app->user->login($this->user);

        return $this->prepareOutput();
    }

    /**
     * @return array
     */
    private function prepareOutput()
    {
        $token = $this->createToken();

        $result = [
            'id' => $this->user->id,
            'login' => $this->user->login,
            'f_fio' => $this->user->f_fio,
            'i_fio' => $this->user->i_fio,
            'o_fio' => $this->user->o_fio,
            'fullname' => $this->user->fullname,
            'birthday' => $this->user->birthday,
            'sex' => $this->user->sex,
            'photo' => $this->user->photo,
            'token' => [
                'token' => (string)$token,
                'expired' => $token->getClaim('exp', 0),
            ],
            'require_select_organization' => $this->requireSelectOrganization,
        ];

        if ($this->requireSelectOrganization === false) {
            $result['specialist'] = $this->user->specialist->toArray();
            $result['organizations'] = $this->user->specialist->getAllOrganizations(!(\Yii::$app->user->can(Role::ROLE_SHELTER_MANAGEMENT) || \Yii::$app->user->can(Role::ROLE_SHELTER_SPECIALIST)));

            $result['access'] = (new FrontendAccessHelper())->prepareOutput($this->user);
        }
        if (count($this->user->specialists) > 1) {
            $result['organization_options'] = $this->user->organizationOptions();
        }

        $result['need_change_password'] = $this->user->needsChangePassword();

        // логируем псевдосессию юзера
        /* @var $sessionManager \app\modules\adminfstek\components\UserSessionManager */
        $sessionManager = Yii::$app->get('userSessionManager');
        $sessionManager->logSession($token);

        return $result;
    }

    /**
     * @return \Lcobucci\JWT\Token
     * @throws \yii\base\InvalidConfigException
     */
    private function createToken()
    {
        /** @var \app\common\components\Jwt $jwt */
        $jwt = Instance::ensure('jwt', Jwt::class);
        $token = $jwt->createToken($this->user);

        $this->user->updateAttributes([
            'last_login' => new Expression('NOW()'),
        ]);

        return $token;
    }

    /**
     * @param int $id_user
     * @param int $id_specialist
     * @return bool
     */
    private function hasRolesInOrganization($id_user, $id_specialist)
    {
        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->authManager;
        $roles = $auth->getRolesByUser($id_user, $id_specialist);

        return !empty($roles);
    }
}
