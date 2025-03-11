<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\CompositeRule;
use app\common\components\rbac\Role;

/**
 * Class AllOrgsCompositeRule5
 * @package app\common\components\rbac\rules
 *
 * @issue VETAIS-3228
 * Гос вет специалистам доступны данные, связанные с сетью организаций, к которой относится организация пользователя.
 */
class AllOrgsCompositeRule5 extends CompositeRule
{
    /**
     * @var string
     */
    public $name = 'AllOrgsCompositeRule5';
    /**
     * @var array
     */
    public $rules = [
        'app\common\components\rbac\rules\UserOrgTreeRule',
    ];

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (
            \Yii::$app->user->can(Role::ROLE_VET_SPECIALIST_GOS) !== true &&
            \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS) !== true &&
            \Yii::$app->user->can(Role::ROLE_REGISTRY_GOS) !== true
        ) {
            return false;
        }

        return parent::execute($user, $item, $params);
    }
}
