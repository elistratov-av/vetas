<?php

namespace app\common\components\visitServiceReport\helpers;

use app\common\components\entity\EntityResourceFactory;
use app\common\components\visitServiceReport\ServiceReportActions;
use app\common\models\VisitStatus;
use app\models\db\Visits;
use app\modules\v1\models\ActiveDataProvider;
use app\modules\v1\models\EntityResource;
use app\modules\v1\models\FileResource;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use Yii;

trait ServiceReportActionsTrait
{
    use ParamsTrait;

    /**
     * @param $id
     * @return null|static|EntityResource
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function findVisitServiceRecord($id, $actionName)
    {
        $entity = ServiceReportActions::FILE_RESOURCE_ENTITY;

        /* @var $resource \app\modules\v1\models\EntityResource */
        $resource = EntityResourceFactory::getResource($entity);

        if (!$object = $resource::findOne(['id' => $id])) {
            throw new NotFoundHttpException("Услуга приема с указанным ID #{$id} не найдена.");
        }

        $visit = Visits::findOne(['id' => $object->id_visit]);
        if ($visit === null) {
            throw new NotFoundHttpException("Прием ID #{$object->id_visit} для услуги с указанным ID #{$id} не найден.");
        }

        $allowed = false;
        if ($visit->status == VisitStatus::IN_WORK) {
            $allowed = true;
        } elseif ($visit->status == VisitStatus::FINISHED) {
            if (in_array($actionName, ['view', 'files'])) {
                $allowed = true;
            } elseif (in_array($actionName, ['create', 'update']) && !empty($visit->fact_end_dttm)) {
                $date_end = date_create_from_format('Y-m-d H:i:s', $visit->fact_end_dttm);
                if ($date_end !== false) {
                    $date_end_plus = $date_end->modify('+1 day');
                    if (time() < $date_end_plus->getTimestamp()) {
                        $allowed = true;
                    }
                }
            }
        }

        if ($allowed === false) {
            throw new BadRequestHttpException('Операции с отчетом недоступны для текущего статуса приема');
        }

        $this->initHandler($object->id, $object->id_service, $object->id_visit);

        return $object;
    }

    /**
     * Возвращает объекты значений параметров отчёта по услуге приёма
     *
     * @param int $id ID объекта \app\models\db\VisitsGovServices
     *
     * @return \app\models\db\VisitServiceParamValues[]
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\NotFoundHttpException
     */
    private function findParamsValues($id, $actionName)
    {
        /* @var $resource \app\modules\v1\models\EntityResource */
        $resource = $this->findVisitServiceRecord($id, $actionName);

        return $resource->getActiveQueryForPluralRelation(ServiceReportActions::PARAMS_VALUES_ENTITY)->all();
    }

    /**
     * @param $id_visitservice
     * @param $id_service
     * @param $id_visit
     * @throws \yii\base\InvalidConfigException
     */
    private function initHandler($id_visitservice, $id_service, $id_visit)
    {
        $this->handler = \Yii::createObject([
            'class'           => ServiceReportsDataHelper::class,
            'id_visitservice' => $id_visitservice,
            'id_service'      => $id_service,
            'id_visit'        => $id_visit,
        ]);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function loadDataFromRequest()
    {
        return ArrayHelper::getValue(Yii::$app->getRequest()->getBodyParams(), 'Visit_service_param_value', []);
    }

    /**
     *  @return array
     */
    private function loadServiceData($entry)
    {
        return ArrayHelper::getValue($entry, 'Visit_service_param_value', []);
    }

    /**
     * @param int $id ID визита
     * @return array
     */
    private function findReportsAvailable($id)
    {
        $records = $this->findVisitServiceRecords($id);
        if (empty($records)) {
            return [
                'data' => []
            ];
        }

        $results = [];
        foreach ($records as $record) {
            $params = $this->findReportParamsForService($record['id_service']);
            if (!empty($params)) {
                $results[] = [
                    'id_visitservice' => $record['id'],
                    'id_service'      => $record['id_service'],
                ];
            }

            $results[] = [
                'id_visitservice' => $record['id'],
                'id_service'      => $record['id_service'],
            ];

        }

        return [
            'data' => $results
        ];
    }

    /**
     * @param int $id ID визита
     * @return array
     */
    private function findPdfReportsAvailable($id)
    {
        $records = $this->findVisitServiceRecords($id);
        if (empty($records)) {
            return [
                'data' => []
            ];
        }

        $ids = $this->findServicesWithReports(ArrayHelper::getColumn($records, 'id_service'));

        if (empty($ids)) {
            return [
                'data' => []
            ];
        }

        $results = [];
        foreach ($records as $record) {
            if (in_array($record['id_service'], $ids, true)) {
                $results[] = [
                    'id_visitservice' => $record['id'],
                    'id_service'      => $record['id_service'],
                ];
            }
        }

        return [
            'data' => $results
        ];
    }

    /**
     * @param int $id ID визита
     * @return array
     */
    private function findVisitServiceRecords($id)
    {
        $records = (new Query())
            ->from('visits_gov_services')
            ->andWhere(['id_visit' => $id])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return $records;
    }

    /**
     * @param int $id
     */
    private function removeOldPdf($id)
    {
        /* @var $files \app\modules\v1\models\FileResource[] */
        $files = FileResource::find()
            ->where([
                'entity_id'   => $id,
                'entity_type' => 'visits_gov_service'
            ])
            ->all();

        if (!empty($files)) {
            foreach ($files as $file) {
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                    Yii::error('Failed to delete pdf file for visitservice_id ' . $id . "\n" . $e->getMessage());
                }
            }
        }
    }

    /**
     * @param array $service_ids
     * @return array
     */
    public function findServicesWithReports($service_ids = [])
    {
        $q = (new Query())
            ->select('id_service')
            ->from('gov_services_reports')
            ->groupBy('id_service');
        if (!empty($service_ids)) {
            $q->andWhere(['in', 'id_service', $service_ids]);
        }

        return $q->column();
    }
}
