<?php

namespace app\modules\adminfstek\controllers;

use app\models\db\admin\AdminUser;
use app\modules\adminfstek\models\search\SessionSearch;
use app\modules\adminfstek\components\DbSession as AdminfSession;
use app\modules\adminv\components\DbSession as VetadminSession;
use yii\filters\AccessControl;

/**
 * Class SessionsController
 * @package app\modules\adminfstek\controllers
 */
class SessionsController extends AdminController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [AdminUser::ROLE_SECURITY],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $sessionManager = $this->getSessionManager();
        $sessionManager->deleteAllExpiredSessions();

        $searchModel = new SessionSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('_index_inner', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param int|string $id
     * @param int        $target
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDelete($id, $target)
    {
        switch ($target) {
            case SessionSearch::TARGET_API;
            case SessionSearch::TARGET_ANDROID;
                $sessionManager = $this->getSessionManager();
                if (!$sessionManager->terminateSession($id)) {
                    \Yii::$app->session->setFlash('error', 'Ошибка при удалении сессии пользователя');
                }
                break;
            case SessionSearch::TARGET_VETADMIN;
                (new VetadminSession())->destroySession($id);
                break;
            case SessionSearch::TARGET_ADMIN;
                (new AdminfSession())->destroySession($id);
                break;
            default:
                break;
        }

        return $this->redirect(\Yii::$app->request->getReferrer());
    }

    /**
     * @return \app\modules\adminfstek\components\UserSessionManager
     * @throws \yii\base\InvalidConfigException
     */
    private function getSessionManager()
    {
        return \Yii::$app->get('userSessionManager');
    }
}
