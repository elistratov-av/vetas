<?php

namespace app\modules\admin\controllers;

use app\models\db\AndroidBuild;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Class AndroidController
 * @package app\modules\admin\controllers
 */
class AndroidController extends AdminController
{
    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        if ($action->id == 'upload') {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            //$this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $model = new AndroidBuild();

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', 'Новый билд сохранен');
            return $this->refresh();
        }

        $query = AndroidBuild::find()
            ->orderBy(['created_at' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return array
     */
    public function actionUpload()
    {
        if (empty($_FILES) || $_FILES['upload']['error']) {
            throw new ServerErrorHttpException('Ошибка при загрузке файла [1]');
        }

        $finished = false;
        $chunk = (int)\Yii::$app->request->post('dzchunkindex', 0);
        $chunks = (int)\Yii::$app->request->post('dztotalchunkcount', 1);

        $fileName = $_FILES['upload']['name'];
        $dir = \Yii::getAlias('@webroot/android');
        FileHelper::createDirectory($dir);
        $filePath = FileHelper::normalizePath($dir . '/' . $fileName);
        $tempPath = $filePath . '.part';

        $out = @fopen($tempPath, $chunk == 0 ? "wb" : "ab");
        if ($out) {
            $in = @fopen($_FILES['upload']['tmp_name'], "rb");
            if ($in) {
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }
            } else {
                throw new ServerErrorHttpException('Ошибка при загрузке файла [2]');
            }

            @fclose($in);
            @fclose($out);

            @unlink($_FILES['upload']['tmp_name']);
        } else {
            throw new ServerErrorHttpException('Ошибка при загрузке файла [3]');
        }

        if ($chunk == $chunks - 1) {
            rename($tempPath, $filePath);
            $finished = true;
        }

        return [
            'success' => true,
            'finished' => $finished,
            'currentChunkIndex' => $chunk,
            'totalChunkCount' => $chunks,
            'progress' => round(($chunk + 1 ) * 100 / $chunks, 2),
        ];
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = AndroidBuild::findOne(['id' => $id]);

        if ($model === null) {
            throw new NotFoundHttpException();
        }

        if ($model->delete() && !empty($model->filename)) {
            $path = \Yii::getAlias('@webroot/android') . '/' . $model->filename;
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return $this->redirect(['index']);
    }
}
