<?php


namespace app\modules\v2\modules\tmc\models;


use app\common\validators\FullTrimValidator;
use app\models\db\tmc\Dosages;
use app\models\db\tmc\CategoryToTmc;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcDrug;
use app\models\db\tmc\TmcToDiseases;
use app\models\db\tmc\TmcToSpecies;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;

class DrugsModel
{
    use TmcModelTrait;

    /**
     * @param int $id
     * @return array|ActiveRecord|null
     * @throws BadRequestHttpException
     */
    public function getDrug(int $id)
    {
        $drug = TmcDrug::find()
            ->select([
                'tmc.tmc.id',
                'tmc.tmc.type',
                'name',
                'id_measure',
                'form_description',
                'excipients',
                'basis',
                'packaging',
                'produced',
                'registered',
                'dealer',
                'unit'
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
            ->where([
                'AND',
                ['id' => $id],
                ['tmc.tmc.type' => TmcBase::TYPE_DRUG],
                ['is_deleted' => false],
            ])
            ->orderBy('tmc.tmc.name ASC')
            ->asArray()
            ->one();

        if (empty($drug)) {
            throw new BadRequestHttpException('Указанный препарат не найден');
        }

        return $drug;
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function list(int $page = 1, int $limit = 10, array $filter = [])
    {
        $filter = $this->validateFilter($filter);

        $drugs = TmcDrug::find()
            ->select([
                'tmc.tmc.id',
                'tmc.tmc.type',
                'tmc.tmc.name',
                'tmc.tmc.id_measure',
                'form_description',
                'excipients',
                'basis',
                'packaging',
                'produced',
                'registered',
                'dealer',
                'tmc.tmc.unit',
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
                'AND',
                ['tmc.tmc.type' => TmcBase::TYPE_DRUG],
                ['is_deleted' => false],
            ])
            ->asArray()
            ->limit($limit)
            ->offset(($page - 1) * $limit)
        ;

        if (!empty($filter)) {
            $this->applyFilter($drugs, $filter);
        }

        $count = clone $drugs;
        $result = new CommonList('drugs', $drugs->all(), $count->count('distinct tmc.tmc.name'), $page, $limit);

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
        if (empty($filter)) {
            return;
        }

        $empty_filter = [
            'id_measure' => null, 'form_description' => null,
            'excipients' => null, 'basis' => null, 'packaging' => null, 'unit' => null,
            'name' => null, 'registered' => null, 'produced' => null, 'dealer' => null
        ];

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['id_measure'], 'integer'],
            [['unit'], 'double'],
            [['form_description', 'excipients', 'basis', 'packaging', 'name', 'registered', 'produced', 'dealer'], 'string'],
            [['form_description', 'excipients', 'basis', 'packaging', 'name', 'registered', 'produced', 'dealer'], FullTrimValidator::class],
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

        if (!empty($filter['id_measure'])) {
            $query->innerJoin('measures', 'measures.id = tmc.tmc.id_measure');
            $query->andWhere(['tmc.tmc.id_measure' => $filter['id_measure']]);
        }

        if(!empty($filter['id_dosage']) || (isset($filter['dosage_exists']) && $filter['dosage_exists'] === true)) {
            $query->innerJoin(Dosages::tableName(), Dosages::tableName() . '.id_tmc = tmc.tmc.id');
        }

        if (!empty($filter['id_dosage'])) {
            $query->andWhere([Dosages::tableName() . '.id' => $filter['id_dosage']]);
        }

        if (isset($filter['dosage_exists'])) {
            if ($filter['dosage_exists'] === false) {
                $query->leftJoin(Dosages::tableName(), Dosages::tableName() . '.id_tmc = tmc.tmc.id');
                $query->andWhere([Dosages::tableName() . '.id' => null]);
            }
        }

        if (!empty($filter['active_substance'])) {
            $query
                ->innerJoin('content_of_active_substances', 'content_of_active_substances.id_tmc = tmc.tmc.id')
                ->innerJoin('active_substances', 'content_of_active_substances.id_active_substance = active_substances.id');
            $query->andWhere(['ILIKE', 'active_substances.name', $filter['active_substance']]);
        }


        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'tmc.tmc.name', $filter['name']]);
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

        if (!empty($filter['form_description'])) {
            $query->andWhere(['ILIKE', 'form_description', $filter['form_description']]);
        }

        if (!empty($filter['excipients'])) {
            $query->andWhere(['ILIKE', 'excipients', $filter['excipients']]);
        }

        if (!empty($filter['basis'])) {
            $query->andWhere(['ILIKE', 'basis', $filter['basis']]);
        }

        if (!empty($filter['packaging'])) {
            $query->andWhere(['ILIKE', 'packaging', $filter['packaging']]);
        }
    }

    /**
     * Создание препарата
     *
     * @param string $name
     * @param string $registered
     * @param string $produced
     * @param string $dealer
     * @param string $form_description
     * @param string $unit
     * @param null $id_measure
     * @param string $excipients
     * @param null $basis
     * @param string $packaging
     * @param $category_ids
     * @param $diseases_ids
     * @param $species_ids
     * @return TmcDrug
     *
     * @throws BadRequestHttpException
     */
    public function create(
        $name,
        $registered,
        $produced,
        $dealer = null,
        $form_description = null,
        $unit = null,
        $id_measure = null,
        $excipients = null,
        $basis = null,
        $packaging = null,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = []
    )
    {
        $drug = new TmcDrug();

        $drug->type = TmcBase::TYPE_DRUG;
        $drug->name = $name;
        $drug->registered = $registered;
        $drug->produced = $produced;
        $drug->dealer = $dealer;
        $drug->form_description = $form_description;
        $drug->unit = $unit;
        $drug->id_measure = $id_measure;
        $drug->excipients = $excipients;
        $drug->basis = $basis;
        $drug->packaging = $packaging;

        return $this->save(
            $drug,
            $category_ids,
            $diseases_ids,
            $species_ids,
            'Ошибка при сохранении препарата'
        );
    }

    /**
     * Редактирование препарата
     *
     * @param string $name
     * @param string $registered
     * @param string $produced
     * @param string $dealer
     * @param string $form_description
     * @param string $unit
     * @param int $id_measure
     * @param string $excipients
     * @param string $packaging
     * @param $category_ids
     * @param $diseases_ids
     * @param $species_ids
     * @return TmcDrug
     * @throws BadRequestHttpException
     */
    public function edit(
        $id,
        $name,
        $registered,
        $produced,
        $dealer,
        $form_description,
        $unit,
        $id_measure,
        $excipients,
        $packaging,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = []
    )
    {
        $drug = TmcDrug::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_DRUG,
        ]);

        if (empty($drug)) {
            throw new BadRequestHttpException('Указанный препарат не найден');
        }

        $drug->name = $name;
        $drug->registered = $registered;
        $drug->produced = $produced;
        $drug->dealer = $dealer;
        $drug->form_description = $form_description;
        $drug->unit = $unit;
        $drug->id_measure = $id_measure;
        $drug->excipients = $excipients;
        $drug->packaging = $packaging;

        return $this->save(
            $drug,
            $category_ids,
            $diseases_ids,
            $species_ids,
            'Ошибка при сохранении препарата'
        );
    }

    /**
     * Удаление препарата
     *
     * @param $id
     * @return TmcDrug
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function delete($id)
    {
        $drug = TmcDrug::findOne([
            'id' => $id,
            'is_deleted' => false,
            'tmc.tmc.type' => TmcBase::TYPE_DRUG,
        ]);

        if (empty($drug)) {
            throw new BadRequestHttpException('Указанный препарат не найден');
        }

        $this->validateWhereIsBalance($drug);

        $drug->is_deleted = true;

        // Сохраняем, зануляя связи
        return $this->save($drug, [], [], [], 'Ошибка при удалении препарата');

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
        $transaction = TmcDrug::getDb()->beginTransaction();

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
