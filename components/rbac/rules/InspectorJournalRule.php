<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\Role;
use app\common\components\rbac\Rule;
use app\models\db\Reports;
use yii\base\InvalidConfigException;
use yii\rbac\Item;

/**
 * Class InspectorJournalRule
 * @package app\common\components\rbac\rules
 *
 * Пользователю роли "Инспектор" доступен только "Журнал регистрации и вакцинации"
 */
class InspectorJournalRule extends Rule
{
    /**
     * @var string
     */
    public $name = 'InspectorJournalRule';

    /**
     * @param \app\common\models\UserModel $user
     * @param \yii\rbac\Item               $item   the role or permission that this rule is associated with
     * @param array                        $params параметры, переданные в ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (!\Yii::$app->user->can(Role::ROLE_INSPECTOR) || \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            // Светлана, 11:53
            // у сотрудника комитета может быть сразу две роди "инспектор"и "системный администратор"
            return true;
        }

        if (!isset($params['model'])) {
            throw new InvalidConfigException('Для применения правила ' . static::class . ' необходимо передать в параметрах в качестве model модель приема');
        }

        /* @var $model \app\models\db\Reports */
        $model = $params['model'];
        if ($model->report_type != Reports::TYPE_JOURNAL) {
            return true;
        }

        return $model->id == 23;
    }
}
