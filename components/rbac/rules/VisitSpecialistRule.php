<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\Rule;
use yii\base\InvalidConfigException;
use yii\db\Query;

/**
 * Class VisitSpecialistRule
 * @package app\common\components\rbac\rules;
 */
class VisitSpecialistRule extends Rule
{
    /**
     * @var string
     */
    public $name = 'VisitSpecialistRule';

    /**
     * Пользователю доступны приемы, где он является специалистом приема
     *
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

        $model = $params['model'];
        $id_visit = is_array($model) ? $model['id'] : $model->id;

        return (new Query())
            ->from('visits_specialists')
            ->where([
                'id_visit'      => $id_visit,
                'id_specialist' => $user->specialist->id,
            ])
            ->exists();
    }
}
