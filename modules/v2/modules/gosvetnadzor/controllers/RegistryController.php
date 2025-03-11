<?php

namespace app\modules\v2\modules\gosvetnadzor\controllers;

use app\common\websocket\asurWebsocketClient;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\Registry;
use app\models\db\asur\Task;
class RegistryController extends BaseController
{
    /**
     * @param $id_owner
     * @param string $type
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGet($id_owner, string $type, $doc_id = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new Registry())->createTask($id_owner, $type, Task::TYPE_FILE_REQUEST, $doc_id)
        ];
    }

    /**
     * @param string $snils
     * @return array
     */
    public function actionGetPassportData(string $snils)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new asurWebsocketClient())->requestDataFromAsur($snils)
        ];
    }

    /**
     * @param $id_owner
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionStatus($id_owner)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new Registry())->getStatus($id_owner)
        ];
    }
}
