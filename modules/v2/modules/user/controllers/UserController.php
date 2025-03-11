<?php

namespace app\modules\v2\modules\user\controllers;

use app\common\components\JwtHttpBearerAuth;
use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\common\models\VisitStatus;
use app\models\db\ServiceTypes;
use app\models\db\ShiftType;
use app\models\db\Users;
use app\models\db\Visits;
use app\modules\adminfstek\components\UserLogManager;
use app\modules\adminfstek\helpers\PasswordHelper;
use app\modules\adminfstek\traits\PasswordTrait;
use app\modules\v2\common\rbac\FrontendAccessHelper;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\recoveryPassword\models\RecoveryPasswordModel;
use app\modules\v2\modules\user\models\AuthModel;
use app\modules\v2\modules\user\models\ChangePasswordModel;
use app\modules\v2\modules\user\models\LoginModel;
use app\modules\v2\modules\user\models\SudirLoginModel;
use app\modules\v2\modules\visit\models\VisitModel;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Class UserController
 * @package app\modules\v2\modules\user\controllers
 */
class UserController extends BaseController
{
    use PasswordTrait;

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'http_authenticator' => [
                    'except' => [
                        'token',
                        'site-online',
                        'recovery-password',
                    ],
                ],
            ]
        );
    }

    /**
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionRecoveryPassword($login)
    {
        $sameLogin = Users::findOne(['login' => $login]);
        if ($sameLogin) {
            $model = new RecoveryPasswordModel();
            return [
                'result' => $model->add($login),
            ];
        }
        else return [
            'result' => 'no user',
        ];
    }

/**
 * @param string $login
 * @param string $password
 * @return array
 */
    public function actionToken($login, $password, $code = '')
    {
        if (!empty($code)) {
            $model = new SudirLoginModel(compact('code'));
        }else{
            $model = new LoginModel(compact('login', 'password'));
        }

        $result = $model->login();

        if ($result === false) {
            // логируем ошибку логина
            UserLogManager::errorApiLogin($login);
            $this->errorResponse($model, 'Ошибка авторизации');
        } elseif(!empty($model->getErrors())) {
            foreach( $model->getErrors() as $error) {
                $this->errorResponse($model, $error[0]);

            }
        } else {
            UserLogManager::successApiLogin($model->getUser());
        }

        return [
            'result' => $result,
        ];
    }

/**
 * @param int $id_organization
 * @return array
 */
public function actionSelectOrganization($id_organization)
{
    $model = new LoginModel([
        'user' => \Yii::$app->user->getIdentity(),
    ]);

    $result = $model->selectOrganization($id_organization);
    if ($result === false) {
        $this->errorResponse($model, 'Ошибка выбора организации');
    }

    return [
        'result' => $result,
    ];
}

/**
 * @param int $id
 * @return array
 */
public
function actionGet($id)
{
    /* @var $user \app\common\models\UserModel */
    $user = \Yii::$app->user->getIdentity();

    if ($user->id !== (int)$id) {
        // до реализации RBAC пока не будем давать доступ другим пользователям
        throw new BadRequestHttpException('Некорректный ID пользователя');
    }

    $egip = array("login" => "is_vetas", "password" => "0#X.880i&cPR");
    //то вставь, то убери, для ИБ на следующий период
    $result = [
        'id' => $user->id,
        'login' => $user->login,
        'email' => $user->email,
        'f_fio' => $user->f_fio,
        'i_fio' => $user->i_fio,
        'o_fio' => $user->o_fio,
        'fullname' => $user->fullname,
        'birthday' => $user->birthday,
        'egip' => $egip,
        'sex' => $user->sex,
        'photo' => $user->photo,
        'specialist' => $user->specialist->toArray(),
        'organizations' => $user->specialist->getAllOrganizations(!(\Yii::$app->user->can(Role::ROLE_SHELTER_MANAGEMENT) || \Yii::$app->user->can(Role::ROLE_SHELTER_SPECIALIST))),
    ];
    if (count($user->specialists) > 1) {
        $result['organization_options'] = $user->organizationOptions();
    }

    $result['access'] = (new FrontendAccessHelper())->prepareOutput($user);
    $result['need_change_password'] = $user->needsChangePassword();

    return [
        'result' => $result,
    ];
}

/**
 * @return array
 * @throws \Throwable
 */
public
function actionOrganizations()
{
    /* @var $user \app\common\models\UserModel */
    $user = \Yii::$app->user->getIdentity();

    $result = ($user->specialist === null || empty($user->specialist->id_organization))
        ? []
        : [
            'organizations' => $user->specialist->getAllOrganizations(!(\Yii::$app->user->can(Role::ROLE_SHELTER_MANAGEMENT) || \Yii::$app->user->can(Role::ROLE_SHELTER_SPECIALIST))),
        ];

    if (count($user->specialists) > 1) {
        $result['organization_options'] = $user->organizationOptions();
    }

    return [
        'result' => $result,
    ];
}

/**
 * @param string $old_password
 * @param string $new_password
 * @return array
 */
public
function actionChangePassword($old_password, $new_password)
{
    /* @var $user \app\common\models\UserModel */
    $user = \Yii::$app->user->getIdentity();
    $model = new ChangePasswordModel(compact('old_password', 'new_password', 'user'));
    if (!$model->changePassword()) {
        $this->errorResponse($model, 'Ошибка при изменении пароля пользователя');
    }

    return [
        'result' => true,
    ];
}

/**
 * @param array $urls
 * @return array
 */
public
function actionCheckAccess($urls)
{
    return [
        'result' => [
            'urls' => (new FrontendAccessHelper())->checkUrlAccess($urls),
        ],
    ];
}

/**
 * Используется только для удаления записи из таблицы мониторинга активных сессий для ФСТЭК
 * Запрос должен отправляться фронтом до удаления токена из хранилища браузера
 * @param string $login
 * @return array
 */
public
function actionLogout($login)
{
    $user = UserModel::findByLogin($login);
    if ($user !== null) {
        $token = (new JwtHttpBearerAuth())->extractTokenFromHeaders();
        if ($token !== null) {
            // удаляем псевдосессию пользователя
            /* @var $sessionManager \app\modules\adminfstek\components\UserSessionManager */
            $sessionManager = \Yii::$app->get('userSessionManager');
            $sessionManager->deleteCurrentSession($user->id, $token);
            // логируем выход
            UserLogManager::successApiLogout($user);
        }
    }

    return [
        'result' => true,
    ];
}

/**
 * @return array
 */
public
function actionPasswordPattern()
{
    return [
        'result' => [
            'pattern' => Html::escapeJsRegularExpression(PasswordHelper::pattern()),
            'message' => PasswordHelper::errorMessage(),
        ],
    ];
}

/**
 * @return array
 */
public
function actionSiteOnline()
{
    return [
        'result' => true,
    ];
}

/**
 * Метод предназначенный подготавливать оповещения пользователю при логине
 *
 * @return array[]
 * @throws BadRequestHttpException
 * @throws \Throwable
 */
public
function actionNotifications()
{
    /* @var $user \app\common\models\UserModel */
    $user = \Yii::$app->user->getIdentity();
    $specId = $user->specialist->id;

    $sql = "SELECT brigades_specialists.id_brigade 
        FROM brigades_specialists 
        WHERE brigades_specialists.id_specialist = :specid";
    $command = \Yii::$app->db->createCommand($sql);
    $command->bindValue(':specid', (int)$specId);
    $brigadeId = $command->queryAll();

    $brigadeIds = array_column($brigadeId, 'id_brigade');

    $notifications = [];

    $now = new \DateTime();
    $visitsAmbulance = Visits::find()
        ->where(['in', 'id_brigade', $brigadeIds])
        ->andWhere(['type' => 'AMBULANCE', 'status' => 'N'])
        ->andWhere(['>', 'lower(visits.time_range)', $now->format('Y-m-d G:H:i')])
        ->all();
    $visitsAmbulanceList = [];
    foreach ($visitsAmbulance as $visit) {
        $visitsAmbulanceList[] = $visit->toArray();
    }

    if (count($visitsAmbulanceList)) {
        $visitsAmbulanceArr = [];

        foreach ($visitsAmbulanceList as $visitAmbulance) {
            $brigade_name = $visitAmbulance["brigade_name"] ?? '';
            $start_dttm = substr($visitAmbulance["start_dttm"], 0, -3);
            $idVisit = $visitAmbulance["id"];

            $object = (object)[
                'idVisit' => $idVisit,
                'visitStrAlert' => "На бригаду $brigade_name назначена заявка на «ВПД» на $start_dttm",
            ];
            $visitsAmbulanceArr[] = $object;
        }

        $notifications['visitsAmbulance'] = $visitsAmbulanceArr;
    }

    $access = (new FrontendAccessHelper())->prepareOutput($user);

    $isAdmin = array_filter($access['roles'], function ($role) {
        return $role['name'] === Role::ROLE_SYSADMIN_GOS;
    });

    if ($isAdmin) {
        $visitsToTransferList = (new VisitModel)
            ->list(
                $user->specialist->id_organization,
                [],
                date('Y-m-d', strtotime('-30 days')),
                60,
                1,
                1000,
                [
                    'not_assigned' => true,
                    'status' => [VisitStatus::TRANSFER]
                ],
            );

        if ($visitsToTransferList->total_count) {
            $notifications['visitsToTransfer'] = "Информируем Вас о том, что имеются приемы со статусом «К переносу» в количестве $visitsToTransferList->total_count";
        }
    }

    return ['notifications' => $notifications];
}

/**
 * @param int $minutes
 * @return array[]
 * @throws \Throwable
 */
public
function actionCheckScheduledVisits(int $minutes = 0)
{
    /* @var $user \app\common\models\UserModel */
    $user = \Yii::$app->user->getIdentity();

    $now = new \DateTime();
    $now->modify("+$minutes minutes");

    /** @var Visits $visit */
    $visits = Visits::find()
        ->leftJoin('visits_specialists vs', 'vs.id_visit = visits.id')
        ->leftJoin('visits_gov_services vgs', 'vgs.id_visit = visits.id')
        ->leftJoin('gov_services gs', 'gs.id = vgs.id_service')
        ->leftJoin('shift_type st', 'st.id = visits.channel')
        ->where(['vs.id_specialist' => $user->specialist->id])
        ->andWhere(['visits.status' => [VisitStatus::NEW, VisitStatus::CHANGED]])
        ->andWhere(['gs.id_service_type' => ServiceTypes::TYPE_TELE_VETERINARY])
        ->andWhere(['>=', 'visits.start_dttm', $now->format('Y-m-d H:i:s')])
        ->andWhere(['st.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])
        ->orderBy(['visits.start_dttm' => SORT_DESC])
        ->all();

    $notifications['upcomingMosruVisits'] = null;
    if ($visits) {
        foreach ($visits as $visit) {
            $services = [];
            foreach ($visit->services as $service) {
                $services[] = $service->name;
            }

            $visitTime = substr($visit->time_range, 2, 16);

            $notifications['upcomingMosruVisits'][] = [
                'time' => $visitTime,
                'services' => $services,
                'link' => "{$_ENV['VKS_SPECIALIST_URL']}/$visit->guid_video"
            ];
        }
    }

    return ['notifications' => $notifications];
}

/**
 * @return array
 * @throws \Throwable
 */
public
function actionRoles()
{
    $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

    $model = new AuthModel();
    $roles = $model->roles();
    foreach ($roles as $role) {
        $role["description"] = str_replace("\n", ', ', $role["description"]);
    }
    return [
        'result' => $roles,
    ];
}
}
