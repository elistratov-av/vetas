<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\CompositeRule;

/**
 * Class AllOrgsCompositeRule2
 * @package app\common\components\rbac\rules
 *
 * Пользователь является пользователем ГОЛОВНОЙ организации.
 * Ему доступны данные, связанные с действующим местом работы и дочерними организациями
 */
class AllOrgsCompositeRule3 extends CompositeRule
{
    /**
     * @var string
     */
    public $name = 'AllOrgsCompositeRule3';
    /**
     * @var array
     */
    public $rules = [
        'app\common\components\rbac\rules\UserRootOrgRule',
        'app\common\components\rbac\rules\UserAllOrgsRule',
    ];
}
