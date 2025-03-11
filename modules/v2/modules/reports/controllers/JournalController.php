<?php

namespace app\modules\v2\modules\reports\controllers;

use app\common\components\pdfGenerator\PdfGenerator;
use app\common\components\rbac\Role;
use app\common\validators\FullTrimValidator;
use app\models\db\Discount;
use app\models\db\FiasAddresses;
use app\models\db\GovServices;
use app\models\db\IdentificationTypes;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Reports;
use app\models\db\ShiftType;
use app\models\db\Species;
use app\models\db\Visits;
use app\modules\soap\models\ServiceTypes;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\reports\models\JournalModel;
use app\modules\v2\modules\reports\models\VisitJournalModel;
use app\modules\v2\modules\reports\models\VisitJournalReportExport;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use DateTimeImmutable;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class JournalController
 *
 * @package app\modules\v2\modules\reports\controllers
 */
class JournalController extends BaseController
{
    use ReportsTrait;

    /**
     * @var string
     */
    public $type = Reports::TYPE_JOURNAL;

    /**
     * Возвращает список журналов
     *
     * @return array
     */
    public function actionList()
    {
        $condition = [];
        if (\Yii::$app->user->can(Role::ROLE_INSPECTOR) && !\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            // Пользователю роли "Инспектор" доступен только "Журнал регистрации и вакцинации"
            // Светлана, 11:53
            // у сотрудника комитета может быть сразу две роди "инспектор"и "системный администратор"
            $condition = ['id' => 23];
        }
        $models = $this->findModels($condition);

        $result = ArrayHelper::map(
            $models,
            'id',
            function ($model) {
                /* @var $model \app\models\db\Reports */
                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'meta' => JournalModel::prepareMeta($model, true),
                ];
            }
        );

        return [
            'result' => [
                'journals' => array_values($result),
            ],
        ];
    }

    /**
     * @param int $id
     * @param int $id_organization
     * @param array $filter
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function actionRecords($id, $id_organization, $filter = null, $page = null, $limit = null)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            throw new NotFoundHttpException('Журнал не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $journalModel = new JournalModel([
            'model' => $model,
            'id_organization' => $id_organization,
            'filter' => $filter,
            'page' => $page,
            'limit' => $limit,
        ]);

        $data = $journalModel->findRecords();

        if ($data === false) {
            $this->errorResponse($journalModel, 'Ошибка при получении данных журнала');
        }

        return [
            'result' => [
                'pages_count' => $journalModel->getPagesCount(),
                'total_count' => $journalModel->getTotalCount(),
                'meta' => $journalModel->formatMeta(),
                'data' => $data,
            ],
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws NotSupportedException
     * @throws ForbiddenHttpException
     */
    public function actionVisitRecords(): array
    {
        $this->checkAccess($this->action->getUniqueId());
        $params = \Yii::$app->getRequest()->getBodyParams();
        $search = $this->getSearchModel($params);
        $provider = new ActiveDataProvider([
            'query' => $search->findRecords(),
            'pagination' => [
                'defaultPageSize' => $params['limit'] ?? 10,
                'page' => ($params['page'] ?? 1) - 1,
            ],
        ]);
        // load pagination first
        $data = VisitJournalModel::addVisitDescriptions($provider->getModels());

        return [
            'result' => [
                'pages_count' => $provider->getPagination()->pageCount,
                'total_count' => $provider->getTotalCount(),
                'meta' => VisitJournalModel::getMeta(),
                'data' => $data,
                'computed' => $search->calculateStat(),
            ],
        ];
    }

    /**
     * @param int $id_report
     * @param int $id_organization
     * @param array $ids
     * @return array
     */
    public function actionFile($id_report, $id_organization, $ids)
    {
        $model = $this->findModel($id_report);
        if ($model === null) {
            throw new NotFoundHttpException('Журнал не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $journalModel = new JournalModel([
            'model' => $model,
            'id_organization' => $id_organization,
        ]);

        $result = $journalModel->createPdf($ids);

        if ($result === false) {
            $this->errorResponse($journalModel, 'Ошибка при генерации файла');
        }

        return [
            'result' => [
                'url' => $result,
            ],
        ];
    }

    /**
     * Формирование pdf файла для печати данных приема
     *
     * @param int $visit_id
     * @param int $pet_id
     * @return array
     */
    public function actionPrint($visit_id, $pet_id)
    {
        $visit = Visits::findOne(['id' => $visit_id]);
        if (!$visit) {
            throw new NotFoundHttpException('Приём не найден');
        }
        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $pets = $visit->pets;
        foreach ($visit->pets as $pet) {
            if ($pet->id === $pet_id) {
                $pets = [$pet];
                break;
            }
        }

        /* @var $generator PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');
        try {
            $path = $generator->createVisitDescription([
                'visit' => $visit,
                'description_types' => VisitDescriptionsModel::findAvailableDescriptionTypes($visit->id),
                'descriptions' => ArrayHelper::index($visit->visitDescriptions, 'id_description_type'),
                'pets' => $pets,
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($visit, 'Ошибка при генерации файла');
        }

        return [
            'result' => [
                'url' => \Yii::getAlias('@web') . '/upload/pdf/' . $path[1] . '.' . $path[2],
            ],
        ];
    }

    /**
     * Формирование excel для "Журнал приемов"
     *
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=134644332
     */
    public function actionExport()
    {
        $this->checkAccess($this->action->getUniqueId());

        $search = $this->getSearchModel(\Yii::$app->getRequest()->getBodyParams());
        $valid = false;
        /**
         * @var DateTimeImmutable|false $from
         * @var DateTimeImmutable|false $to
         */
        [$from, $to] = [DateTimeImmutable::createFromFormat('Y-m-d', $search->filter['date_from'] ?? ''), DateTimeImmutable::createFromFormat('Y-m-d', $search->filter['date_to'] ?? '')];
        if ($from && $to && $to->diff($from)->days <= 365) {
            $valid = true;
        }
        if (!$valid && count($search->idOrganizations) > 1 && count(array_diff(array_keys($search->filter), ['date_from', 'date_to']))) {
            $valid = true;
        }
        if (!$valid) {
            throw new BadRequestHttpException('Выгрузка доступна при установленных значениях фильтров: выбранном диапазоне дат не более года или в случае выбора не более одной организации при отсутствующих других значениях в фильтрах');
        }

        $export = new VisitJournalReportExport([
            'query' => $search->findRecords(),
            'columns' => VisitJournalModel::getMeta(),
        ]);

        $export->export();
    }

    /**
     * Метод для получения списка организаций для вывода в фильтре.
     * "Системному администратору доступны данные, связанные с действующим местом работы пользователя,
     * где ему выдана соответствующая роль, и дочерними организациями.
     * Пользователю с ролью отличной от "Системный администратор (гос)" доступны данные
     * связанные с действующим местом работы пользователя, где ему выдана соответствующая роль"
     *
     * @return array
     */
    public function actionOrganizations(): array
    {
        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        $organizations = (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_INSPECTOR))
            ? $user->specialist->organization->getTree(false, true)
            : [$user->specialist->organization];

        return [
            'result' => [
                'organizations' => $organizations,
            ],
        ];
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionServiceTypes(): array
    {
        $this->checkAccess($this->action->getUniqueId());

        return [
            'result' => [
                'service_types' => ServiceTypes::find()->select(['id', 'name'])->asArray()->all(),
            ],
        ];
    }

    /**
     * @param array $serviceTypeIds
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionServices(array $serviceTypeIds = []): array
    {
        $this->checkAccess($this->action->getUniqueId());
        if (empty($serviceTypeIds)) {
            return [
                'result' => [
                    'services' => GovServices::find()
                        ->select(['id', 'name', 'cod'])
                        ->orderBy('id')
                        ->asArray()
                        ->all(),
                ],
            ];
        }

        return [
            'result' => [
                'services' => GovServices::find()
                    ->select(['id', 'name', 'cod'])
                    ->where(['id_service_type' => $serviceTypeIds])
                    ->orderBy('id')
                    ->asArray()
                    ->all(),
            ],
        ];
    }

    /**
     * @param int|null $id
     * @param string|null $name
     * @return array
     * @throws BadRequestHttpException
     * @throws NotSupportedException
     * @throws ForbiddenHttpException
     */
    public function actionOwners(int $id = null, string $name = null): array
    {
        $this->checkAccess($this->action->getUniqueId());
        if ($id === null && $name === null) {
            throw new BadRequestHttpException('Не передан не один параметр (id, name)');
        }
        $where = ($id !== null) ?
            ['po.id' => $id] :
            [
                'OR',
                ['ILIKE', 'po.fullname', (new FullTrimValidator())->validateValue($name)],
                ['ILIKE', 'po.jur_name', (new FullTrimValidator())->validateValue($name)],
            ];

        return [
            'result' => [
                'owners' => (new Query())
                    ->select(['po.id', 'po.fullname', 'fa.full_address', 'po.jur_name', 'po.inn'])
                    ->from(PetOwners::tableName() . ' po')
                    ->leftJoin(FiasAddresses::tableName() . ' fa', 'po.id_fact_fias_address = fa.id')
                    ->where($where)
                    ->limit(10)
                    ->all(),
            ],
        ];
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionIdentificationTypes(): array
    {
        $this->checkAccess($this->action->getUniqueId());

        return [
            'result' => [
                'identification_types' => IdentificationTypes::find()->select(['id', 'name'])->asArray()->all(),
            ],
        ];
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionSpecies(): array
    {
        $this->checkAccess($this->action->getUniqueId());

        return [
            'result' => [
                'species' => Species::find()->select(['id', 'name'])->asArray()->all(),
            ],
        ];
    }

    /**
     * @param int|null $id
     * @param string|null $name
     * @param int|null $idOwner
     * @param string|null $nameOwner
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotSupportedException
     */
    public function actionPets(
        int $id = null,
        string $name = null,
        int $idOwner = null,
        string $nameOwner = null
    ): array {
        $this->checkAccess($this->action->getUniqueId());
        if ($id === null && $name === null && $idOwner === null && $nameOwner === null) {
            throw new BadRequestHttpException('Не передан не один параметр (id, name, idOwner, idSpecies)');
        }

        $query = (new Query)->select(['p.id', 'p.name'])->from(Pets::tableName() . ' p')->limit(10);

        if ($id !== null || $name !== null) {
            $query->andWhere($id !== null ? ['p.id' => $id] : ['ILIKE', 'p.name', (new FullTrimValidator())->validateValue($name)]);
        }

        if ($idOwner !== null) {
            $query
                ->innerJoin(PetsToOwner::tableName() . ' pto', 'pto.id_pet = p.id AND pto.id_owner = ' . $idOwner);
        } elseif ($nameOwner !== null) {
            $query
                ->innerJoin(PetsToOwner::tableName() . ' pto', 'pto.id_pet = p.id')
                ->innerJoin(PetOwners::tableName() . ' po', 'po.id = pto.id_owner')
                ->andWhere([
                    'OR',
                    ['ILIKE', 'po.fullname', (new FullTrimValidator())->validateValue($nameOwner)],
                    ['ILIKE', 'po.jur_name', (new FullTrimValidator())->validateValue($nameOwner)],
                ])
                ->groupBy('p.id');
        }

        return [
            'result' => [
                'pets' => $query->all(),
            ],
        ];
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionVisitTypes(): array
    {
        $this->checkAccess($this->action->getUniqueId());
        $result = [];
        foreach (Visits::typeOptions() as $id => $name) {
            $result[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        return [
            'result' => [
                'visit_types' => $result,
            ],
        ];
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionVisitChannels(): array
    {
        $this->checkAccess($this->action->getUniqueId());

        $q = (new Query())
            ->from(ShiftType::tableName())
            ->where([
                'AND',
                ['idle' => false],
                [
                    'IN',
                    'type',
                    [
                        ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT,
                        ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT,
                        ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE,
                        ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY,
                    ],
                ],
            ]);

        return [
            'result' => [
                'visit_channels' => array_map(static function (array $r) {
                    ['id' => $id, 'type' => $type, 'description' => $name] = $r;

                    return ['id' => $id, 'name' => $type === ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY ? 'По направлению' : $name];
                }, $q->all()),
            ],
        ];
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDiscountTypes(): array
    {
        $this->checkAccess($this->action->getUniqueId(), Discount::class);

        return [
            'result' => [
                'discount_types' => Discount::find()->select(['id', 'name'])->asArray()->all(),
            ],
        ];
    }

    private function getSearchModel(array $params): VisitJournalModel
    {
        $orgIds = $params['id_organizations'] ?? [];
        if (!count($orgIds)) {
            /* @var $user \app\common\models\UserModel */
            $user = \Yii::$app->user->getIdentity();
            $organizations = (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_INSPECTOR)) ? $user->specialist->organization->getTree(false, true) : [$user->specialist->organization];

            $orgIds = ArrayHelper::getColumn($organizations, 'id', []);
        }
        $filter = [];
        foreach ($params['filter'] ?? [] as $k => $v) {
            if (is_string($v)) {
                $v = trim($v);
            }
            if (in_array($k, ['date_from', 'date_to'])) {
                $dt = DateTimeImmutable::createFromFormat('d.m.Y', $v);
                $v = $dt ? $dt->format('Y-m-d') : null;
            }
            if (!empty($v)) {
                $filter[$k] = $v;
            }
        }

        return new VisitJournalModel([
            'idOrganizations' => $orgIds,
            'filter' => $filter,
            'order' => $params['sort'] ?? [],
        ]);
    }
}
