<?php

namespace app\common\components\visitServiceReport;

use app\common\components\FileService;
use app\common\components\visitServiceReport\helpers\ServiceReportActionsTrait;
use app\models\db\Pets;
use app\models\db\Visits;
use app\modules\v1\models\ActiveDataProvider;
use app\modules\v1\models\FileResource;
use app\common\components\visitServiceReport\helpers\ServiceReportsDataHelper;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use Yii;

/**
 * Class ServiceReportActions
 *
 * @package app\common\components\report
 * @author Aleksandr Roik
 */
class ServiceReportActions
{
    use ServiceReportActionsTrait;

    /** @var string */
    const FILE_RESOURCE_ENTITY = 'visits-gov-services';

    /** @var string */
    const PARAMS_VALUES_ENTITY = 'visit-service-param-values';

    /**
     * @var ServiceReportsDataHelper
     */
    private $handler;

    /**
     * @var
     */
    private $action;


    /**
     * @return ServiceReportsDataHelper
     */
    public function getHandler(): ServiceReportsDataHelper
    {
        return $this->handler;
    }

    /**
     * GET /v1/service-reports/{id}
     *
     * @param int $id
     * @return array
     */
    public function actionView($id)
    {
        $models = $this->findParamsValues($id, 'view');
        $data = $this->handler->prepareOutput($models);

        return [
            'data' => $data
        ];
    }

    /**
     * POST /v1/service-reports/{id}
     *
     * @param int $id
     * @return array
     */
    public function actionCreate($id)
    {
        $post = $this->loadDataFromRequest();

        /* @var $resource \app\modules\v1\models\EntityResource */
        $resource = $this->findVisitServiceRecord($id, 'create');

        if ($this->handler->save($post, 'create')) {
            $models = $this->findParamsValues($id, 'create');
            $data = $this->handler->prepareOutput($models);

            return [
                'data' => $data,
            ];
        }

        if (empty($this->handler->errors)) {
            throw new UnprocessableEntityHttpException('Неизвестная ошибка при создании отчета');
        }

        return $this->handler;
    }

    /**
     * PUT /v1/service-reports/{id}
     *
     * @param int $id
     * @param array|null $post
     * @return array
     */
    public function actionUpdate($id, $post = null)
    {
        if (!$post) {
            $post = $this->loadDataFromRequest();
        }

//        var_dump($id, $post); die;

        $models = $this->findParamsValues($id, 'update');

        if ($this->handler->save($post, 'update', $models)) {
            $models = $this->findParamsValues($id, 'update');
            $data = $this->handler->prepareOutput($models);

            $this->removeOldPdf($id);

            return [
                'data' => $data,
            ];
        }

        if (empty($this->handler->errors)) {
            throw new UnprocessableEntityHttpException('Неизвестная ошибка при создании отчета');
        }

        return $this->handler;
    }

    /**
     * @return array
     * @throws UnprocessableEntityHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionBatchUpdate(): array
    {
        $services = ArrayHelper::getValue(Yii::$app->getRequest()->getBodyParams(), 'Visit_service_param_value', []);
        $response = [];

        $restructured = [];
        foreach ($services as $service) {
            $restructured[$service['id_visit_service']][] = $service;
        }

        foreach ($restructured as $idVisitService => $service) {
            $response[$idVisitService] = $this->actionUpdate($idVisitService, $service);
        }

        return $response;
    }

    /**
     * GET /v1/service-reports/get-all-by-visit/{id}
     *
     * @param $id
     * @return array
     */
    public function actionGetByVisit($id)
    {
        if (!$visit = Visits::findOne(['id' => $id])) {
            throw new NotFoundHttpException("Приём с id $id не найден");
        }
        $outputByPets = [];
        foreach($visit->visitsGovServices as $visitsGovService) {
            $service = $visitsGovService->service;
            if ($service->hasReports()) {
                $models = $this->findParamsValues($visitsGovService->id, 'view');
                $output = $this->handler->prepareOutput($models);
                $output['service_name'] = $service->name;
                $output['service_alt_name'] = $service->alternative_name;
                $output['service_code'] = $service->cod;
                $output['service_type'] = $service->serviceType->name;
                $output['service_count'] = $visitsGovService->count;

                $outputByPets[$visitsGovService->id_pet][] = $output;
            }
        }
        foreach ($outputByPets as $petId => $value) {
            $pet = Pets::find()->where(['id' => $petId])->one();
            $outputByPets[$petId][0]['name'] = $pet->name;
        }

        return array_values($outputByPets);
    }

    /**
     * GET /v1/service-reports/{id}/files
     *
     * @param int $id
     * @return array
     */
    public function actionFiles($id)
    {
        /* @var $resource \app\modules\v1\models\EntityResource */
        $resource = $this->findVisitServiceRecord($id, 'files');

        $available = $this->findServicesWithReports([$resource->id_service]);
        if (empty($available)) {
            throw new BadRequestHttpException('Для данной услуги не предусмотрено печатной формы отчета');
        }

        $files = $resource->getActiveQueryForPluralRelation('files')
            ->orderBy(['created_at' => SORT_DESC])
            ->all()
        ;
        if (!empty($files)) {
            $fileResource = array_shift($files);
            if (is_readable($fileResource->path)) {
                // возвращаем существующий FileResource
                return $fileResource;
            }

            \Yii::warning(sprintf(
                'Файл отчёта по услуге (id_visit_service=%s) %s не доступен для чтения и будет пересоздан.',
                $id,
                $fileResource->path
            ));

            // Удаляем "битую" запись.
            // Используем метод "таблицы", чтобы не рассылать событие.
            FileResource::deleteAll(['id' => $fileResource->id]);
        }

        return $this->generatePDF($id);
    }

    /**
     * GET /v1/service-reports/available/{id}
     *
     * @param int $id ID визита
     * @return array
     */
    public function actionAvailable($id)
    {
        return $this->findReportsAvailable($id);
    }

    /**
     * GET /v1/service-reports/available-pdf/{id}
     *
     * @param int $id ID визита
     * @return array
     */
    public function actionAvailablePdf($id)
    {
        return $this->findPdfReportsAvailable($id);
    }

    /**
     * Генерирует PDF, сохраняет и возвращает указатель на файл (FileResource)
     *
     * @var int $id_visit_service ID услуги в приёме
     *
     * @return FileResource ресурс созданного файла
     *
     * @throws ServerErrorHttpException
     * @throws UnprocessableEntityHttpException
     * @throws MediaException
     */
    public function generatePDF(int $id_visit_service, bool $return_data = false)
    {

        $reportParamsValues = $this->findParamsValues($id_visit_service, 'files');
        if (empty($reportParamsValues)) {
            throw new ServerErrorHttpException('Отсутствуют данные для генерации отчета');
        }

        $data = $this->handler->prepareOutput($reportParamsValues, true);
        if ($return_data) return $data;
        if ($data === false) {
            if (empty($this->handler->errors)) {
                throw new UnprocessableEntityHttpException('Неизвестная ошибка при создании отчета');
            }

            return $this->handler;
        }

        /** @var \app\common\components\pdfGenerator\PdfGenerator $generator */
        $generator = Yii::$app->get('pdfGenerator');
        $generator->entity_type = self::FILE_RESOURCE_ENTITY;
        $generator->entity_id = $id_visit_service;
        try {
            [$dir, $filename, $ext] = $generator->createDocumentList($data['id_report'], $data['params']);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException("Ошибка при генерации отчета: {$e->getMessage()}", 0, $e);
        }

        /** @var FileService $fileService */
        $fileService = \Yii::$app->fileService;
        $fileService->repository = $generator;
        $hash = $fileService->generateHash($filename);
        $filePath = $fileService->repository->save($dir . '/' . $filename . '.' . $ext, $hash, $ext);

        try {
            $fileResource = new FileResource();
            $fileResource->hash = $hash;
            $fileResource->path = $filePath;
            $fileResource->name = $hash . '.' . $ext;
            $fileResource->entity_id = $id_visit_service;
            $fileResource->entity_type = self::FILE_RESOURCE_ENTITY;
            $fileResource->save();
        } catch (\Exception $e) {
            $fileService->repository->delete($filePath);
            throw new \app\common\components\media\MediaException("Ошибка при сохранении файла: {$e->getMessage()}", 0, $e);
        }

        return $fileResource;
    }
}
