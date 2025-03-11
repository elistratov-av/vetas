<?php

namespace app\modules\v2\modules\visit\models;

use app\models\db\{
    GovServices, GovServicesParams, Params, VisitServiceParamValues
};
use app\common\components\visitServiceReport\helpers\ParamsTrait;
use yii\base\Model;

/**
 * Класс для работы с параметрами
 * https://jira.altarix.ru/browse/VETAIS-920
 *
 * Class VisitServiceParamModel
 * @package app\modules\v2\modules\visit\entity
 */
class VisitServiceParamModel extends Model
{
    use ParamsTrait;

    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $id_param;
    /**
     * @var mixed
     */
    public $value;
    /**
     * @var int
     */
    public $idService;
    /**
     * @var \app\models\db\Params
     */
    private $paramRecord;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->paramRecord = Params::find()
            ->where([Params::tableName() . '.id' => $this->id_param])
            ->one();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['id_param'], 'required'],
            [['id_param'], 'integer'],
            [['id_param'], 'validateId'],
            [['value'], 'validateValue'],
        ];
    }

    /**
     * @param string $attribute
     */
    public function validateId($attribute): void
    {
        if ($this->paramRecord === null) {
            $this->addError($attribute, "Переданного параметра {$attribute} {$this->$attribute} не существует");

            return;
        }

        $param = GovServicesParams::find()
            ->joinWith(['service'])
            ->where([
                'id_param' => $this->$attribute,
                'id_service' => $this->idService,
            ])
            ->one();

        $exists = ($param->service->type != GovServices::TYPE_MOSRU) ? ($param->req_in == true) : true;
        if (!$exists) {
            $this->addError($attribute, "Переданный параметр {$attribute} {$this->$attribute} не привязан  к указанной услуге {$this->idService}");
        }
    }

    /**
     * @param $attribute
     * @throws \yii\base\InvalidConfigException
     */
    public function validateValue($attribute): void
    {
        if ($this->paramRecord === null) {
            return;
        }

        $this->validateParam($this->value, [
            'datatype' => $this->paramRecord->datatype,
            'datatype_details' => $this->paramRecord->datatype_details,
            'tech_name' => $this->paramRecord->tech_name,
        ]);
    }

    /**
     * @param int $idVisitService
     * @return bool
     */
    public function save(int $idVisit, int $idVisitService, ?int $idPet): bool
    {
        $columnName = $this->resolveColumnName($this->paramRecord->datatype);
        $attributes = [
            'id_visit' => $idVisit,
            'id_visitservice' => $idVisitService,
            'id_pet' => $idPet,
            $columnName => $this->value,
            'id_param' => $this->id_param,
        ];

        $model = ($this->id !== null)
            ? VisitServiceParamValues::findOne(['id' => $this->id])
            : new VisitServiceParamValues();
        if ($model === null) {
            // если не найден - создадим
            $model = new VisitServiceParamValues();
        }

        if ($model->load($attributes, '') && $model->save()) {
            return true;
        }

        $this->addErrors($model->getErrors());

        return false;
    }
}
