<?php

namespace app\common\components\rbac\rules;

use app\modules\v1\actions\EntityActionTrait;
use yii\helpers\ArrayHelper;

/**
 * Class UserAllOrgsRule
 * @package app\common\components\rbac\rules
 *
 * Доступно пользователям организации в части, связанной с организацией и дочерними организациями
 */
class UserAllOrgsRule extends UserOrgRule
{
    use EntityActionTrait;

    /**
     * @var string
     */
    public $name = 'UserAllOrgsRule';

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
        $organizations = $user->specialist->getAllOrganizations();
        $ids = ArrayHelper::getColumn($organizations, 'id');

        if ($id_organization === null) {
            // возможно это создание организации по v1? у новой модели id будет null
            // проверять права нужно по parent_id - на создание дочерних и дочерних-дочерних-...
            if (!$this->isV1OrganizationCreate()) {
                return false;
            }
            $parent_id = ArrayHelper::getValue($params['model'], 'parent_id');

            return in_array($parent_id, $ids);
        }

        // возможно это редактирование организации по v1?
        // проверяем, не изменили ли parent_id так что он не попадает в организации юзера
        // допускаем что parent_id может быть равен parent_id организации юзера (иначе невозможно будет редактировать свою организацию)
        if ($this->isV1OrganizationUpdate()) {
            $parent_id = ArrayHelper::getValue($params['model'], 'parent_id');
            $spec_org_parent_id = $user->specialist->organization->parent_id;

            return (($parent_id == $spec_org_parent_id || in_array($parent_id, $ids)) && in_array($id_organization, $ids));
        }

        return in_array($id_organization, $ids);
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function isV1OrganizationCreate()
    {
        $actionId = \Yii::$app->controller->action->getUniqueId();
        if ($actionId == 'v1/entity/create') {
            $entity_name = $this->getEntityNameFromRequest(\Yii::$app->request);
            return $entity_name == 'organizations';
        }

        return false;
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function isV1OrganizationUpdate()
    {
        $actionId = \Yii::$app->controller->action->getUniqueId();
        if ($actionId == 'v1/entity/update') {
            $entity_name = $this->getEntityNameFromRequest(\Yii::$app->request);
            return $entity_name == 'organizations';
        }

        return false;
    }
}
