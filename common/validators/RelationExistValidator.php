<?php
namespace app\common\validators;

use Yii;
use yii\db\Query;
use yii\validators\ExistValidator;

class RelationExistValidator extends ExistValidator
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->skipOnEmpty = true;
        $this->skipOnError = true;

        $this->isEmpty = function ($value) {
            return $value === null || $value === [] || $value === '' || $value == 0;
        };
    }

    public function validateAttribute($model, $attribute)
    {
        $entityManager = Yii::$container->get('entityManager');
        $entityInstance = $entityManager->getEntity($this->targetRelation);

        $query = new Query();
        $query->from($entityInstance->getTableName())->where(['id' => $model->$attribute]);

        if (!$query->exists()) {
            $this->addError($model, $attribute, "Связанная сущность {relation} с указанным {attribute} не существует", [
                'attribute' => $attribute, 'relation' => $this->targetRelation
            ]);
        }
    }
}
