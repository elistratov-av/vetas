<?php

namespace app\modules\v2\common\rbac;

use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * Trait AccessTrait
 * @package app\modules\v2\common\rbac
 */
trait AccessTrait
{
    /**
     * @param string                            $actionId
     * @param \app\models\db\ActiveRecord|array $model
     * @param array                             $params
     * @param bool                              $throwException
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    protected function checkAccess($actionId, $model = null, $params = [], $throwException = true)
    {
        $authItems = $this->findAuthItemsForAction($actionId);

        if (empty($authItems)) {
            return true;
        }

        $this->fixQuirks($actionId, $params);

        if (isset($model)) {
            $params['model'] = $model;
        }

        foreach ($authItems as $authItem) {
            $result = \Yii::$app->user->can($authItem, $params);
            if ($result === true) {
                return true;
            }
        }

        if ($throwException === false) {
            return false;
        }

        throw new ForbiddenHttpException('Недостаточно прав доступа');
    }

    /**
     * @param string $actionId
     * @return array|null
     */
    private function findAuthItemsForAction($actionId)
    {
        $map = $this->loadActionMap();

        return ArrayHelper::getValue($map, $actionId);
    }

    /**
     * @return array
     */
    private function loadActionMap()
    {
        $map = require __DIR__ . '/action_map.php';

        return $map;
    }

    /**
     * Фиксим несоответствия для отдельных методов и параметров.
     * @param string $actionId
     * @param array $params
     */
    private function fixQuirks($actionId, &$params)
    {
        if ($actionId == 'v2/timesheet/shift/list' && isset($params['org_id'])) {
            // \app\modules\v2\modules\timesheet\controllers\ShiftController::actionList
            $params['id_organization'] = $params['org_id'];
        }
    }
}
