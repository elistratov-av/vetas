<?php
/**
 * @author Serge Postrash <jexy.ru@gmail.com>
 */

namespace app\common\components\entity;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use app\modules\v1\actions\EntityActionTrait;

/**
 * Class EntityAccessFilter
 * @package app\common\components\entity
 */
class EntityAccessFilter extends AccessControl
{
    use EntityActionTrait;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $checker = new EntityAccessChecker();
        $entity = $this->getEntityNameFromRequest(Yii::$app->request);

        if (empty($entity)) {
            // не применяем правило
            return true;
        }

        $params = [];
        $get = Yii::$app->request->get();

        if (isset($get['id'])) {
            $params['id'] = (int)$get['id'];
        }

        $allow = $checker->checkAccess($entity, $action->id, $params, $this);

        $relatedEntity = Yii::$app->request->get('entity');
        if ($allow === true && !empty($relatedEntity)) {
            ArrayHelper::remove($params, 'id');
            if (isset($get['entity_id'])) {
                $params['id'] = (int)$get['entity_id'];
            }
            $allow = $checker->checkAccess($relatedEntity, $action->id, $params, $this);
        }

        if ($allow === true) {
            return true;
        }

        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, null, $action);
        } else {
            $this->denyAccess(Yii::$app->user);
        }

        return false;
    }
}
