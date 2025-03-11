<?php

namespace app\modules\v2\modules\tmc\models;

use app\common\components\rbac\User;
use app\models\db\ActiveRecord;
use app\models\db\tmc\Dosages;
use app\models\db\tmc\TmcBase;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class DosagesModel
{
    public $id_organization;

    /**
     * DosageModel constructor.
     *
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function __construct()
    {
        $user = \Yii::$app->user->getIdentity();
        /** @var User $user */
        $this->id_organization = $user->specialist->id_organization;
        if ($this->id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }
    }

    /**
     * У некоторых существующих в БД ТМЦ не проставлены единицы измерения.
     * Принуждаем пользователя выставить их
     *
     * @param $id_tmc
     * @param $type_tmc
     * @return bool
     */
    protected function checkExistMeasure($id_tmc, $type_tmc)
    {
        $check = TmcBase::find()
            ->select('id_measure')
            ->where([
                'type' => $type_tmc,
                'id'   => $id_tmc,
            ])
            ->scalar();

        return ($check !== null);
    }

    /**
     * @param int $id_tmc
     * @param int $id_disease
     * @param int $id_species
     * @param int $birthday
     * @param int $weight
     * @return int|mixed
     * @throws BadRequestHttpException
     */
    public function calculate(int $id_tmc = null,
        int $id_disease = null,
        int $id_species = null,
        string $birthday = null,
        float $weight = null)
    {
        $age_months = 0;

        if (!$id_tmc) {
            throw new BadRequestHttpException('Не указан препарат');
        }
        if (!$id_disease) {
            throw new BadRequestHttpException('Не указано заболевание');
        }
        if (!$id_species) {
            throw new BadRequestHttpException('Не указан вид');
        }
        if (!$weight) {
            throw new BadRequestHttpException('Не указан вес');
        }

        if ($birthday) {
            $birhday_date = \DateTime::createFromFormat('Y-m-d', $birthday);
            if (!$birhday_date) {
                throw new BadRequestHttpException('Неверный формат даты birthday');
            }
            $age_diff_interval = \DateTime::createFromFormat('Y-m-d', date('Y-m-d'))->diff($birhday_date);
            $age_months = $age_diff_interval->y * 12 + $age_diff_interval->m;
        }

        if ($weight <= 0.0) {
            throw new BadRequestHttpException('Значение веса должно быть больше нуля');
        }
        $weight_gr = $weight * 1000;

        $dose_query = Dosages::find()
            ->joinWith('disease', false)
            ->joinWith('species', false)
            ->with('measure')
            ->andWhere(['id_tmc' => $id_tmc])
            ->andWhere(['diseases.id' => $id_disease])
            ->andWhere(['species.id' => $id_species])
            ->andWhere(new Expression("weight_range @> $weight_gr"));

        if (!$this->hasEmptyAgeRange($id_tmc, $id_disease, $id_species)) {
            $dose_query->andWhere(new Expression("age_range @> $age_months"));
        }

        $dose = $dose_query->asArray()->one();

        if (!$dose) {
            throw new BadRequestHttpException('Отсутствуют рекомендации по дозировке препарата');
        }

        return $dose;
    }

    /**
     * Создает настройку дозировки
     *
     * @param $name
     * @param null $id_tmc
     * @param null $type_tmc
     * @param null $id_diseases
     * @param null $id_species
     * @param null $age_from
     * @param null $age_to
     * @param null $weight_from
     * @param null $weight_to
     * @param string $pet_size
     * @param boolean $is_default
     * @param null $dosage
     * @param null $id_dosage_measure
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function create(
        $name,
        $id_tmc = null,
        $type_tmc = null,
        $id_diseases = null,
        $id_species = null,
        $age_from = null,
        $age_to = null,
        $weight_from = null,
        $weight_to = null,
        $pet_size = null,
        $dosage = null,
        $id_dosage_measure = null
    ) {
        if (!$this->checkExistMeasure($id_tmc, $type_tmc)) {
            throw new BadRequestHttpException('У данного ТМЦ не указана единица измерения. ' .
                'Перед настройкой дозировок задайте единицу измерения на основной вкладке.');
        }

        $model = DynamicModel::validateData([
            'Виды животного'         => $id_species,
            'Заболевания'            => $id_diseases,
            'Тип ТМЦ'                => $type_tmc,
            'Название для дозировки' => $name,
        ], [
            [['Название для дозировки'], 'required'],
            [['Название для дозировки'], 'string', 'max' => 50],

            [['Виды животного', 'Заболевания'], 'each', 'rule' => ['integer']],
            [['Виды животного', 'Заболевания'], 'required'],
            [
                ['Тип ТМЦ'],
                'in',
                'range'       => Dosages::ALLOWED_TYPES_TMC,
                'strict'      => true,
                'skipOnEmpty' => false,
                'skipOnError' => false
            ]
        ]);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        $id_diseases = array_map(function ($x) {
            return (int)$x;
        }, $id_diseases);
        $id_species = array_map(function ($x) {
            return (int)$x;
        }, $id_species);

        sort($id_diseases);
        sort($id_species);
        $id_diseases = array_unique($id_diseases);
        $id_species = array_unique($id_species);
        $id_species_r = $id_species;
        unset($id_species);
        $ids = [];

        foreach ($id_diseases as $id_disease) {
            foreach ($id_species_r as $id_species) {
                $ids[] = $this->manage(
                    $name,
                    $id_tmc,
                    $type_tmc,
                    $id_disease,
                    $id_species,
                    $age_from,
                    $age_to,
                    $weight_from,
                    $weight_to,
                    $pet_size,
                    false,
                    $dosage,
                    $id_dosage_measure, function () {
                    return new Dosages();
                }, 'создании');
            }
        }

        return $ids;
    }

    /**
     * @param $name
     * @param int $id_tmc
     * @param string $type_tmc
     * @param $id_disease
     * @param $id_species
     * @param int $age_from
     * @param int $age_to
     * @param int $weight_from
     * @param int $weight_to
     * @param string $pet_size
     * @param boolean $is_default
     * @param $dosage
     * @param int $id_dosage_measure
     * @param $func
     * @param $msg
     * @return integer
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    private function manage(
        $name,
        $id_tmc,
        $type_tmc,
        $id_disease,
        $id_species,
        $age_from,
        $age_to,
        $weight_from,
        $weight_to,
        $pet_size,
        $is_default,
        $dosage,
        $id_dosage_measure,
        $func,
        $msg
    ) {
        $this->validateInput($name, $type_tmc, $id_disease, $id_species, $age_from, $age_to, $weight_from, $weight_to, $dosage, $pet_size, $is_default);

        if (!$this->checkExistMeasure($id_tmc, $type_tmc)) {
            throw new BadRequestHttpException('У данного ТМЦ не указана единица измерения. ' .
                'Перед настройкой дозировок задайте единицу измерения на основной вкладке.');
        }

        $trans = \Yii::$app->getDb()->beginTransaction();
        $this->validateTypeTmc($id_tmc, $type_tmc);

        $age_from = $age_from ?? 0;
        $age_to = $age_to ?? 0;
        if ($age_from == 0 && $age_to == 0) {
            $age_range = 'empty';
        } else {
            $age_range = "[$age_from,$age_to]";
        }
        $weight_range = "[$weight_from, $weight_to]";
        $id_measure = $id_dosage_measure;
        try {
            $hasEmpty = $this->hasEmptyAgeRange($id_tmc, $id_disease, $id_species);
            $count = $this->dosagesCount($id_tmc, $id_disease, $id_species);
            if ($age_to != 0 && $hasEmpty && $count > 0) {
                throw new BadRequestHttpException('Для одного из указанных параметров, существует настройка не учитывающая возраст животного');
            }

            if ($age_to == 0 && !$hasEmpty && $count > 0) {
                throw new BadRequestHttpException('Для одного из указанных параметров, существует настройка учитывающая возраст животного');
            }

            /** @var Dosages $dose */
            $dose = $func();
            $dose->setAttributes(compact(
                'name',
                'id_tmc',
                'type_tmc',
                'age_range',
                'weight_range',
                'dosage',
                'id_measure',
                'id_disease',
                'id_species',
                'pet_size',
                'is_default'
            ));
            if (!$dose->save()) {
                $errors = $dose->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при ' . $msg . ' настройки дозировки' : implode("\n", array_values($errors)));
            }
        } catch (\yii\db\IntegrityException $e) {
            $trans->rollBack();
            $text = $e->errorInfo[2];
            $matches = null;
            preg_match_all('/\(.*?, .*?, .*, (.*?)\)=\(\d+, \d+, \d+, \[(\d+),(\d+)\)\)/um', $text, $matches);
            if (isset($matches[1])) {
                $int_name = $matches[1][0];
                switch ($matches[1][0]) {
                    case 'age_range':
                        $int_name = ' возраста животного';
                        break;
                    case 'weight_range':
                        $int_name = ' веса животного';
                        break;
                }
            }
            $msg = "Добавляемый интервал$int_name, пересекается с уже существующим.";
            throw new BadRequestHttpException($msg);
        } catch (\Throwable $e) {
            $trans->rollBack();
            throw new BadRequestHttpException($e->getMessage());
        }

        $trans->commit();

        return $dose->getPrimaryKey();
    }

    /**
     * @param $name
     * @param $type_tmc
     * @param $id_disease
     * @param $id_species
     * @param int $age_from
     * @param int $age_to
     * @param int $weight_from
     * @param int $weight_to
     * @param string $pet_size
     * @param boolean $is_default
     * @param $dosage
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    private function validateInput($name, $type_tmc, $id_disease, $id_species, $age_from, $age_to, $weight_from, $weight_to, $dosage, $pet_size, $is_default)
    {
        $model = DynamicModel::validateData([
            'Возраст с'              => $age_from,
            'Возраст по'             => $age_to,
            'Вес с'                  => $weight_from,
            'Вес по'                 => $weight_to,
            'Тип ТМЦ'                => $type_tmc,
            'Название для дозировки' => $name,
            'Размер животного'       => $pet_size,
            'Дозировка по умолчанию' => $is_default,
        ], [
            [['Название для дозировки'], 'required'],
            [['Название для дозировки'], 'string', 'max' => 50],
            [['Размер животного'], 'required'],
            [['Размер животного'], 'in', 'range' => Dosages::$petSizeCollection],
            [['Дозировка по умолчанию'], 'boolean'],
            [['Возраст с', 'Возраст по'], 'integer',],
            [['Вес с', 'Вес по'], 'integer', 'min' => 1],
            [['Вес с', 'Вес по'], 'required'],
            [
                'Возраст с',
                'required',
                'when'        => function ($model) {
                    return isset($model['Возраст по']);
                },
                'skipOnEmpty' => false
            ],
            [
                'Возраст по',
                'required',
                'when'        => function ($model) {
                    return isset($model['Возраст с']);
                },
                'skipOnEmpty' => false
            ],
            [
                'Возраст с',
                'compare',
                'compareAttribute' => 'Возраст по',
                'operator'         => '<=',
                'type'             => 'integer',
                'when'             => function ($model) {
                    return isset($model['Возраст по']) && isset($model['Возраст с']);
                }
            ],
            ['Вес с', 'compare', 'compareAttribute' => 'Вес по', 'operator' => '<=', 'type' => 'integer',],
            [
                ['Тип ТМЦ'],
                'in',
                'range'       => Dosages::ALLOWED_TYPES_TMC,
                'strict'      => true,
                'skipOnEmpty' => false,
                'skipOnError' => false
            ]
        ]);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }
    }

    /**
     * @param $name
     * @param int|null $id
     * @param null $type_tmc
     * @param null $id_tmc
     * @param null $id_disease
     * @param null $id_species
     * @param null $age_from
     * @param null $age_to
     * @param null $weight_from
     * @param null $weight_to
     * @param string $pet_size
     * @param boolean $is_default
     * @param null $dosage
     * @param null $id_dosage_measure
     * @return int
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function edit(
        int $id = null,
        $name,
        $type_tmc = null,
        $id_tmc = null,
        $id_disease = null,
        $id_species = null,
        $age_from = null,
        $age_to = null,
        $weight_from = null,
        $weight_to = null,
        $pet_size = null,
        $dosage = null,
        $id_dosage_measure = null)
    {
        if (!$id) {
            throw new BadRequestHttpException('Необходимо указать id');
        }

        if (!$this->checkExistMeasure($id_tmc, $type_tmc)) {
            throw new BadRequestHttpException('У данного ТМЦ не указана единица измерения.' .
                'Перед настройкой дозировок задайте единицу измерения на основной вкладке.');
        }

        return $this->manage(
            $name,
            $id_tmc,
            $type_tmc,
            $id_disease,
            $id_species,
            $age_from,
            $age_to,
            $weight_from,
            $weight_to,
            $pet_size,
            false,
            $dosage,
            $id_dosage_measure, function () use ($id) {
            return $this->findDosage($id, false);
        }, 'редактировании');
    }

    /**
     * Находит и возвращает настройку дозировки с указанным id или генерирует ошибку
     *
     * @param $id
     * @return ActiveRecord
     * @throws BadRequestHttpException
     */
    protected function findDosage($id, $is_arr = true)
    {
        $dosage_q = Dosages::find()
            ->joinWith('tmc')
            ->joinWith('species')
            ->joinWith('measure')
            ->joinWith('disease')
            ->andWhere(['dosages.id' => $id])
            ->select('dosages.*, 
            lower(age_range) as age_from, 
            upper(age_range) - 1 as age_to,
            lower(weight_range) as weight_from, 
            upper(weight_range) - 1 as weight_to'
            );

        if ($is_arr) {
            $dosage_q->asArray();
        }

        $dosage = $dosage_q->one();

        if (!$dosage) {
            throw new BadRequestHttpException('Указанная настройка дозировки не найдена');
        }

        return $dosage;
    }

    /**
     * Возвращает настройку дозировки по id
     *
     * @param $id
     * @throws BadRequestHttpException
     */
    public function get(int $id)
    {
        return $this->findDosage($id);
    }

    /**
     * Возращает список настроек
     *
     * @param $id_tmc
     * @param $filter
     * @return array
     */
    public function list($id_tmc, $filter)
    {

        $filter = $this->validateFilter($filter);

        $query = Dosages::find()
            ->joinWith('tmc')
            ->joinWith('species')
            ->joinWith('measure')
            ->joinWith('disease')
            ->joinWith('flag', false)
            ->andWhere(['id_tmc' => $id_tmc])
            ->select('dosages.*, 
            lower(age_range) as age_from, 
            upper(age_range) - 1 as age_to,
            lower(weight_range) as weight_from, 
            upper(weight_range) - 1 as weight_to,
            dosages_flags.for_act as flag_for_act'
            );

        if (!empty($filter)) {
            $this->applyFilter($query, $filter);
        }

        return $query
            ->asArray()
            ->all();
    }

    /**
     * @param ActiveQuery $query
     * @param array $filter
     */
    protected function applyFilter(ActiveQuery $query, array $filter)
    {
        if (!empty($filter['id_species'])) {
            $query->andWhere(['IN', 'id_species', $filter['id_species']]);
        }

        if (!empty($filter['id_disease'])) {
            $query->andWhere(['id_disease' => $filter['id_disease']]);
        }
    }

    /**
     * @param $filter
     * @return array|void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    protected function validateFilter($filter)
    {
        if (empty($filter)) {
            return;
        }

        $empty_filter = [
            'id_disease' => null,
            'id_species' => null,
        ];

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['id_disease'], 'integer'],
            [['id_species'], 'each', 'rule' => ['integer']],
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        return $model->attributes;
    }

    /**
     * @param $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        $dosage = $this->findDosage($id, false);

        return $dosage->delete();
    }

    /**
     * Установка дозировки по умолчанию
     *
     * @param $id
     */
    public function setDefault($id)
    {
        $dosage = Dosages::findOne($id);
        if (!$dosage) {
            throw new BadRequestHttpException('Дозировка не найдена');
        }

        if ($dosage->is_default) {
            return;
        }

        Dosages::updateAll(
            ['is_default' => false],
            [
                'id_tmc'     => $dosage->id_tmc,
                'id_species' => $dosage->id_species,
            ]
        );

        $dosage->setAttribute('is_default', true);
        if (!$dosage->save()) {
            if ($dosage->hasErrors()) {
                $errors = $dosage->getErrorSummary(true);
                throw new BadRequestHttpException(!$errors ? 'Ошибка:' : implode("\n", array_values($errors)));
            }
        }
    }

    /**
     * @param $id_tmc
     * @param $id_disease
     * @param $id_species
     * @return bool
     */
    private function hasEmptyAgeRange($id_tmc, $id_disease, $id_species): bool
    {
        $query = Dosages::find()
            ->andWhere(['id_tmc' => $id_tmc])
            ->andWhere(['id_disease' => $id_disease])
            ->andWhere(['id_species' => $id_species]);

        $exist = $query
            ->andWhere(['age_range' => 'empty'])
            ->exists();

        return $exist;
    }

    private function dosagesCount($id_tmc, $id_disease, $id_species): int
    {
        $query = Dosages::find()
            ->andWhere(['id_tmc' => $id_tmc])
            ->andWhere(['id_disease' => $id_disease])
            ->andWhere(['id_species' => $id_species]);

        $count = $query->count('id');

        return $count;
    }

    /**
     * @param $id_tmc
     * @param $type_tmc
     * @throws BadRequestHttpException
     */
    protected function validateTypeTmc($id_tmc, $type_tmc)
    {
        $tmc = TmcBase::findOne(['id' => $id_tmc]);
        if (empty($tmc)) {
            throw new BadRequestHttpException('Указанный ТМЦ не найден');
        }

        if ($tmc->type !== $type_tmc) {
            throw new BadRequestHttpException('Переданные ID ТМЦ и Тип ТМЦ не соответствуют друг другу');
        }
    }

}
