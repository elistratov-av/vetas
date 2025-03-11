<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.07.19
 * Time: 11:48
 */

namespace app\modules\adminv\controllers;

use app\models\db\ChangeRequest;
use app\models\db\Organizations;
use app\modules\admin\models\search\RequestSearch;
use app\modules\admin\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class RequestsController
 * @package app\modules\adminv\controllers
 */
class RequestsController extends AdminController
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
                        'permissions' => ['data.change_requests.manage'],
                        'actions' => ['index', 'info'],
                    ],
                    [
                        'allow' => true,
                        'permissions' => ['data.change_requests.process'],
                        'actions' => ['accept', 'reject'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new RequestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        $fullnames = User::find()
            ->select('fullname')
            ->joinWith('specialist')
            ->where(['in', 'specialists.id_organization', Organizations::orgTreeIds(Organizations::GOS_ROOT_ID)])
            ->orderBy(['fullname' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();

        $organizations = Organizations::find()
            ->select('short_name')
            ->where(['in', 'id', Organizations::orgTreeIds(Organizations::GOS_ROOT_ID)])
            ->orderBy(['short_name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();

        return $this->render('index', [
            'organizations' => $organizations,
            'fullnames' => $fullnames,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionInfo($id)
    {
        $model = ChangeRequest::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('Запрос не найден');
        }

        return $this->render('info', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \Throwable
     */
    public function actionAccept($id)
    {
        if (!$changeRequest = ChangeRequest::findOne(['id' => $id])) {
            throw new NotFoundHttpException('Запрос не найден');
        }

        $changeRequest->state = 'A';
        $changeRequest->admin = Yii::$app->user->getId();
        if (!$changeRequest->save()) {
            Yii::$app->session->setFlash('error', 'Ошибка при изменении статуса запроса');

            return $this->redirect(['info', 'id' => $id]);
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionReject($id)
    {
        if (!$changeRequest = ChangeRequest::findOne(['id' => $id])) {
            throw new NotFoundHttpException('Запрос не найден');
        }

        $changeRequest->state = 'D';
        $changeRequest->admin = Yii::$app->user->getId();
        if (!$changeRequest->save()) {
            Yii::$app->session->setFlash('error', 'Ошибка при изменении статуса запроса');

            return $this->redirect(['info', 'id' => $id]);
        }

        return $this->redirect(['index']);
    }
}
