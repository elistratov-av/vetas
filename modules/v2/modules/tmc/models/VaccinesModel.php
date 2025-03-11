<?php

namespace app\modules\v2\modules\tmc\models;

use app\common\validators\FullTrimValidator;
use app\models\db\Diseases;
use app\models\db\tmc\CategoryToTmc;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcToDiseases;
use app\models\db\tmc\TmcToSpecies;
use app\models\db\tmc\TmcVaccine;
use app\modules\v2\common\skeletons\CommonList;
use Throwable;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

class VaccinesModel
{
    use TmcModelTrait;

    /**
     * @param int $id
     * @return TmcVaccine|array|null|\yii\db\ActiveRecord
     */
    public function getVaccine(int $id)
    {
        return TmcVaccine::find()
            ->select([
                'tmc.tmc.id',
                'tmc.tmc.type',
                'name',
                'id_measure',
                'form_description',
                'packaging',
                'produced',
                'registered',
                'dealer',
                'unit',
                'excipients',
            ])
            ->with([
                'measure' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'measures.id',
                        'name',
                        'description'
                    ]);
                },
                'diseases' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'flag_danger',
                    ]);
                },
                'categories'  => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'description'
                    ]);
                },
                'species' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'description',
                        'flag_mos_ru',
                        'tech_name',
                    ]);
                },
                'dosages' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'dosages.id',
                        'dosage',
                        'id_tmc',
                        'type_tmc',
                        'id_measure',
                        'id_species',
                        'id_disease',
                        'age_range',
                        'weight_range',
                    ]);
                },
                'production_forms' => function ($query) {
                    /* @var $query ActiveQuery */
                    $query
                        ->select([
                            'id',
                            'id_tmc',
                            'type_tmc',
                            'name',
                            'volume',
                            'is_utilize',
                            'is_deleted',
                        ]);
                },
//                'active_substances' => function($query) {
//                    /** @var ActiveQuery $query */
//                    $query->select(['content_of_active_substances.*'])
//                        ->with('substance');
//                },
                'files' => function ($query) {
                    /** @var ActiveQuery $query */
                    $query->select(['files.*']);
                }
            ])
            ->where(['id' => $id])
            ->asArray()
            ->one();
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function list(int $page = 1, int $limit = 10, array $filter = [])
    {
        $filter = $this->validateFilter($filter);

        $vaccines = TmcVaccine::find()
            ->select([
                'tmc.tmc.id',
                'tmc.tmc.type',
                'name',
                'id_measure',
                'form_description',
                'packaging',
                'produced',
                'registered',
                'dealer',
                'unit',
                'excipients',
                'is_deleted',
            ])
            ->with([
                'measure' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'measures.id',
                        'name',
                        'description'
                    ]);
                },
                'diseases' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'flag_danger',
                    ]);
                },
                'categories'  => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'description'
                    ]);
                },
                'species' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'description',
                        'flag_mos_ru',
                        'tech_name',
                    ]);
                },
                'dosages' => function($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'dosages.id',
                        'dosage',
                        'id_tmc',
                        'type_tmc',
                        'id_measure',
                        'id_species',
                        'id_disease',
                        'age_range',
                        'weight_range',
                    ]);
                },
//                'active_substances' => function($query) {
//                    /** @var ActiveQuery $query */
//                    $query->select(['content_of_active_substances.*'])
//                        ->with('substance');
//                },
                'files' => function ($query) {
                    /** @var ActiveQuery $query */
                    $query->select(['files.*']);
                }
            ])
            ->where([
                'tmc.tmc.type' => TmcBase::TYPE_VACCINE,
            ])
            ->asArray()
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->orderBy('tmc.tmc.name ASC')
        ;

        if (!empty($filter)) {
            $this->applyFilter($vaccines, $filter);
        }

        $count = clone $vaccines;
        $result = new CommonList('vaccines', $vaccines->all(), $count->count(), $page, $limit);

        return $result;
    }

    /**
     * @param $filter
     * @return array|void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    protected function validateFilter($filter)
    {
        $empty_filter = [
            'id_disease' => null,
            'id_species' => null,
            'name' => null,
            'registered' => null,
            'produced' => null,
            'dealer' => null,
            'rabies_vaccines_only' => null,
            'with_archived' => false,
        ];
        
        if (empty($filter)) {
            return $empty_filter;
        }

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['id_disease', 'id_species'], 'integer'],
            [['name', 'registered', 'produced', 'dealer'], 'string', 'max' => 255],
            [['name', 'registered', 'produced', 'dealer'], FullTrimValidator::class],
            [['rabies_vaccines_only', 'with_archived'], 'boolean'],
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }
        return $model->attributes;
    }

    /**
     * @param ActiveQuery $query
     * @param array $filter
     */
    protected function applyFilter(ActiveQuery $query, array $filter)
    {
        if (!empty($filter['rabies_vaccines_only'])) {
            /** @var Diseases $rabies */
            $rabies = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one();
            $filter['id_disease'] = $rabies->id;
        }

        if (!empty($filter['id_disease'])) {
            $query->innerJoin(
                'tmc.tmc_to_diseases',
                'tmc.tmc_to_diseases.id_tmc = tmc.tmc.id 
                AND
                 tmc.tmc_to_diseases.type_tmc = tmc.tmc.type'
                );
            $query->andWhere([
                'tmc.tmc_to_diseases.id_disease' => $filter['id_disease']
            ]);
        }

        if (!empty($filter['id_species'])) {
            $query->innerJoin(
                'tmc.tmc_to_species',
                'tmc.tmc_to_species.id_tmc = tmc.tmc.id 
                AND
                 tmc.tmc_to_species.type_tmc = tmc.tmc.type'
            );
            $query->andWhere(['tmc.tmc_to_species.id_species' => $filter['id_species']]);
        }

        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'name', $filter['name']]);
        }

        if (!empty($filter['registered'])) {
            $query->andWhere(['ILIKE', 'registered', $filter['registered']]);
        }

        if (!empty($filter['produced'])) {
            $query->andWhere(['ILIKE', 'produced', $filter['produced']]);
        }

        if (!empty($filter['dealer'])) {
            $query->andWhere(['ILIKE', 'dealer', $filter['dealer']]);
        }

        if (!isset($filter['with_archived']) || true !== $filter['with_archived']) {
            $query->andWhere(['is_deleted' => false]);
        }
    }

    /**
     * Создание вакцины
     *
     * @param string $name
     * @param $registered
     * @param $produced
     * @param null $dealer
     * @param null $form_description
     * @param null $unit
     * @param null $id_measure
     * @param null $packaging
     * @param array $category_ids
     * @param array $diseases_ids
     * @param array $species_ids
     * @param $excipients
     * @return TmcVaccine
     *
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function create(
        $name,
        $registered,
        $produced,
        $dealer = null,
        $form_description = null,
        $unit = null,
        $id_measure = null,
        $packaging = null,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = [],
        $excipients
    )
    {
        $vaccine = new TmcVaccine();

        $vaccine->type = TmcBase::TYPE_VACCINE;
        $vaccine->name = $name;
        $vaccine->registered = $registered;
        $vaccine->produced = $produced;
        $vaccine->dealer = $dealer;
        $vaccine->unit = $unit;
        $vaccine->form_description = $form_description;
        $vaccine->packaging = $packaging;
        $vaccine->id_measure = $id_measure;
        $vaccine->excipients = $excipients;

        return $this->save(
            $vaccine,
            $category_ids,
            $diseases_ids,
            $species_ids,
            'Ошибка при сохранении вакцины'
        );
    }

    /**
     * Редактирование вакцины
     *
     * @param int $id
     * @param string $name
     * @param $registered
     * @param $produced
     * @param null $dealer
     * @param null $form_description
     * @param null $unit
     * @param null $id_measure
     * @param null $packaging
     * @param array $category_ids
     * @param array $diseases_ids
     * @param array $species_ids
     * @param $excipients
     * @return TmcVaccine
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function edit(
        $id,
        $name,
        $registered,
        $produced,
        $dealer = null,
        $form_description = null,
        $unit = null,
        $id_measure = null,
        $packaging = null,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = [],
        $excipients
    )
    {
        $vaccine = TmcVaccine::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_VACCINE,
        ]);

        if (empty($vaccine)) {
            throw new BadRequestHttpException('Указанная вакцина не найдена');
        }

        $vaccine->name = $name;
        $vaccine->registered = $registered;
        $vaccine->produced = $produced;
        $vaccine->dealer = $dealer;
        $vaccine->unit = $unit;
        $vaccine->form_description = $form_description;
        $vaccine->packaging = $packaging;
        $vaccine->id_measure = $id_measure;
        $vaccine->excipients = $excipients;

        return $this->save(
            $vaccine,
            $category_ids,
            $diseases_ids,
            $species_ids,
            'Ошибка при сохранении вакцины'
        );
    }

    /**
     * Удаление вакцины
     *
     * @param int $id
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $vaccine = TmcVaccine::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_VACCINE,
        ]);

        if (empty($vaccine)) {
            throw new BadRequestHttpException('Указанная вакцина не найдена');
        }

        $this->validateWhereIsBalance($vaccine);

        $vaccine->is_deleted = true;

        // Сохраняем, зануляя связи
        return $this->save($vaccine, [], [], [], 'Ошибка при удалении вакцины');
    }


    /**
     * Сохраняем и выставляем связи
     *
     * @param $tmc
     * @param array $category_ids
     * @param array $diseases_ids
     * @param array $species_ids
     * @param string $error_msg
     * @return mixed
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    protected function save($tmc, $category_ids = [],  $diseases_ids = [], $species_ids = [],  $error_msg = 'Ошибка при сохранении препарата')
    {
        $transaction = TmcVaccine::getDb()->beginTransaction();

        if (!$tmc->save()) {
            $transaction->rollBack();
            $errors = $tmc->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? $error_msg : implode("\n", array_values($errors)));
        }

        try {
            // Привязываем категории
            CategoryToTmc::bathLinkCategoriesToTmc(
                $tmc->type,
                $tmc->id,
                $category_ids
            );

            // Заболевания
            TmcToDiseases::bathLinkDiseasesToTmc(
                $tmc->type,
                $tmc->id,
                $diseases_ids
            );

            // Виды
            TmcToSpecies::bathLinkSpeciesToTmc(
                $tmc->type,
                $tmc->id,
                $species_ids
            );
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $tmc;
    }

}
