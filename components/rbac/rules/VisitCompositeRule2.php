<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\CompositeRule;
use app\common\components\rbac\Role;
use yii\base\InvalidConfigException;

/**
 * Class VisitCompositeRule2
 * @package app\common\components\rbac\rules
 *
 * Регистратуре доступны данные связанные с действующим местом работы.
 * Вет.специалисту доступны приемы, где он является специалистом приема
 */
class VisitCompositeRule2 extends CompositeRule
{
    /**
     * @var string
     */
    public $name = 'VisitCompositeRule2';
    /**
     * @var array
     */
    public $rules = [
        'app\common\components\rbac\rules\UserOrgRule',
        'app\common\components\rbac\rules\VisitSpecialistRule',
    ];

    /**
     * @param \app\common\models\UserModel $user
     * @param \yii\rbac\Item               $item   the role or permission that this rule is associated with
     * @param array                        $params параметры, переданные в ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (!isset($params['model'])) {
            throw new InvalidConfigException('Для применения правила ' . static::class . ' необходимо передать в параметрах в качестве model модель приема');
        }

        if (\Yii::$app->user->can(Role::ROLE_REGISTRY_GOS)) {
            $ruleName = $this->rules[0];
            /* @var $rule \yii\rbac\Rule */
            $rule = \Yii::createObject($ruleName);

            return $rule->execute($user, $item, $params);
        }

        $ruleName = $this->rules[1];
        /* @var $rule \yii\rbac\Rule */
        $rule = \Yii::createObject($ruleName);

        return $rule->execute($user, $item, $params);
    }
}
