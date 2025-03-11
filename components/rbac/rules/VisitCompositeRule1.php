<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\CompositeRule;
use app\common\models\VisitStatus;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class VisitCompositeRule1
 * @package app\common\components\rbac\rules
 *
 * Применяется для "Управление приемом: редактирование приема в состоянии "Новый", "Изменен", "К переносу""
 * "Пользователю доступны данные связанные с действующим местом работы.
 * Приемы с каналом "направление" также доступны автору направления."
 */
class VisitCompositeRule1 extends CompositeRule
{
    /**
     * @var string
     */
    public $name = 'VisitCompositeRule1';
    /**
     * @var array
     */
    public $rules = [
        'app\common\components\rbac\rules\UserOrgRule',
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

        $model = $params['model'];
        if (is_array($model)) {
            $status = ArrayHelper::getValue($model, 'status');
            $source = ArrayHelper::getValue($model, 'source');
            $author = ArrayHelper::getValue($model, 'author');
        } else {
            /* @var $model \app\models\db\Visits */
            $status = $model->status;
            $source = $model->source;
            $author = $model->author;
        }

        if (!in_array($status, [VisitStatus::NEW, VisitStatus::CHANGED, VisitStatus::TRANSFER])) {
            return false;
        }

        if (!empty($source) && !empty($author)) {
            if ($user->specialist->id == $author) {
                return true;
            }
        }

        return parent::execute($user, $item, $params);
    }
}
