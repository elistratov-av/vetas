<?php
/**
 * @author Serge Postrash <jexy.ru@gmail.com>
 */

namespace app\common\components\entity;

use Yii;
use yii\base\ActionFilter;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\common\behaviors\EntityBehavior;
use app\modules\v1\models\EntityResource;

/**
 * Class EntityAccessChecker
 * @package app\common\components\entity
 */
class EntityAccessChecker
{
    /**
     * Права доступа задаются в конфиге сущности в meta, например:
     * ```
     *  ...
     *  'access' => [
     *      'R' => ['data.classificators.active_substances'],
     *      'W' => ['data.classificators.active_substances.W'],
     *  ]
     *  ...
     * ```
     *
     * @param string $entity
     * @param string $action
     * @param array  $params
     * @param object $caller
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function checkAccess($entity, $action, $params = [], $caller)
    {
        if ($caller instanceof EntityBehavior) {
            $map = self::modelEventsMap();
        } elseif ($caller instanceof ActionFilter) {
            $map = self::controllerActionsMap();
        } else {
            throw new InvalidCallException('Для метода checkAccess не зарегистрирован данный класс вызывающего объекта: ' . get_class($caller));
        }

        if (!array_key_exists($action, $map)) {
            return true;
        }

        $accessRules = $this->getEntityAccessRules($entity);

        if (empty($accessRules)) {
            // разрешаем доступ к сущностям, для которых не задан access в конфиге
            return true;
        }

        if (!isset($params['model']) && isset($params['id'])) {
            $model = EntityResource::findOne(['id' => $params['id']]);
            if ($model !== null) {
                $params['model'] = $model;
            }
        }

        if (!isset($params['id_organization']) && $entity == 'organizations' && !empty($params['model'])) {
            $params['id_organization'] = ArrayHelper::getValue($params['model'], 'id');
        }

        $params['action'] = $action;

        $accessTypes = $map[$action];
        foreach ($accessTypes as $accessType) {
            $rules = ArrayHelper::getValue($accessRules, $accessType, []);
            foreach ($rules as $rule) {
                if (Yii::$app->user->can($rule, $params)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $entity
     * @return array
     */
    private function getEntityAccessRules($entity)
    {
        $config = $this->getEntityConfig($entity);

        if ($config === null) {
            throw new InvalidConfigException('Не задан конфиг для сущности ' . $entity);
        }

        $accessRules = ArrayHelper::getValue($config, 'meta.access', []);

        if (!is_array($accessRules)) {
            throw new InvalidConfigException('Некорректный формат access rules в конфиге сущности ' . $entity);
        }

        return $accessRules;
    }

    /**
     * @param string $entity
     * @return array
     */
    private function getEntityConfig($entity)
    {
        return ArrayHelper::getValue((new EntityConfigManager())->getConfig(), $entity);
    }

    /**
     * @return array
     */
    public static function modelEventsMap()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => ['W'],
            ActiveRecord::EVENT_BEFORE_UPDATE => ['W'],
            ActiveRecord::EVENT_BEFORE_DELETE => ['W'],
        ];
    }

    /**
     * @return array
     */
    public static function controllerActionsMap()
    {
        return [
            'index'            => ['W', 'R'],
            'view'             => ['W', 'R'],
            'create'           => ['W'],
            'update'           => ['W'],
            'delete'           => ['W'],
            'getrel'           => ['W', 'R'],
            'addrel'           => ['W'],
            'delrel'           => ['W'],
            'add-rel-by-id'    => ['W'],
            'update-rel-by-id' => ['W'],
        ];
    }
}
