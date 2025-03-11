<?php

namespace app\modules\adminfstek\controllers;

use app\models\db\admin\AdminUser;
use app\models\db\admin\SecuritySettings;
use yii\filters\AccessControl;

/**
 * Class SecuritySettingsController
 * @package app\modules\adminfstek\controllers
 */
class SecuritySettingsController extends AdminController
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
                        'roles' => [AdminUser::ROLE_ADMIN, AdminUser::ROLE_SECURITY],
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
        $model = SecuritySettings::findOne(['id' => SecuritySettings::DEFAULT_ID]);
        if ($model === null) {
            $model = new SecuritySettings();
        }
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                \Yii::$app->session->setFlash('success', 'Настройки безопасности сохранены');
            } else {
                \Yii::$app->session->setFlash('error', 'Ошибка при сохранении настроек безопасности');
            }
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
