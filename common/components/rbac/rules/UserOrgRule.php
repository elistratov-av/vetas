<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\Rule;
use app\models\db\Organizations;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * Class UserOrgRule
 * @package app\common\components\rbac\rules
 *
 * Доступно пользователям организации в части, связанной с организацией
 */
class UserOrgRule extends Rule
{
    /**
     * @var string
     */
    public $name = 'UserOrgRule';

    /**
     * @param \app\common\models\UserModel $user
     * @param \yii\rbac\Item               $item   the role or permission that this rule is associated with
     * @param array                        $params параметры, переданные в ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if ($user->specialist === null || empty($user->specialist->id_organization) || $user->specialist->organization === null) {
            return false;
        }

        $id_organization = $this->extractIdOrganization($params);

        if ($id_organization === null) {
            return false;
        }

        if (is_array($id_organization)) {
            return in_array($user->specialist->id_organization, $id_organization);
        }

        return $user->specialist->id_organization == $id_organization;
    }

    /**
     * @param array $params
     * @return int|array|null
     */
    protected function extractIdOrganization($params)
    {
        $id_organization = null;

        if (isset($params['model'])) {
            $model = $params['model'];
            if (is_array($model)) {
                $id_organization = ArrayHelper::getValue($model, 'id_organization');
            } elseif (isset($model->id_organization)) {
                /* @var $model \yii\base\Model */
                $id_organization = $model->id_organization;
            } elseif ($model instanceof BaseObject && $model->canGetProperty('organization')) {
                if ($model->organization !== null) {
                    return $model->organization->id;
                }
            } elseif ($model instanceof Organizations) {
                return $model->id;
            }
        }

        if ($id_organization === null) {
            if (isset($params['id_organization'])) {
                $id_organization = $params['id_organization'];
            } elseif (isset($params['filter']['id_organization'])) {
                $id_organization = $params['filter']['id_organization'];
            } elseif (isset($params['filters']['id_organization'])) {
                $id_organization = $params['filters']['id_organization'];
            }
        }

        return $id_organization;
    }
}
