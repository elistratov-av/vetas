<?php

namespace app\modules\v1\controllers;

use app\common\components\visitServiceReport\ServiceReportActions;
use app\common\controllers\ApiController;

/**
 * Class ServiceReportsController
 *
 * @package app\modules\v1\controllers
 */
class ServiceReportsController extends ApiController
{
    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return array_merge(
            parent::verbs(),
            [
                'files'         => ['GET', 'HEAD', 'OPTIONS'],
                'available'     => ['GET', 'HEAD', 'OPTIONS'],
                'available-pdf' => ['GET', 'HEAD', 'OPTIONS'],
            ]
        );
    }

    /**
     * GET /v1/service-reports/{id}
     *
     * @param int $id
     * @return array
     */
    public function actionView($id)
    {
        return [
            'data' => (new ServiceReportActions())
                ->actionView($id)
        ];
    }

    /**
     * GET /v1/service-reports/get-all-by-visit/{id}
     *
     * @param $id
     * @return array
     *
     */
    public function actionGetAllByVisit($id)
    {
        return [
            'data' => (new ServiceReportActions())
                ->actionGetByVisit($id)
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
        return (new ServiceReportActions())
            ->actionCreate($id);
    }

    /**
     * PUT /v1/service-reports/{id}
     *
     * @param int $id
     * @return array
     */
    public function actionUpdate($id)
    {
        return (new ServiceReportActions())
            ->actionUpdate($id);
    }


    /**
     * PUT /v1/service_reports/batch
     *
     * @return array
     */
    public function actionUpdateBatch()
    {
        return (new ServiceReportActions())
            ->actionBatchUpdate();
    }

    /**
     * GET /v1/service-reports/{id}/files
     *
     * @param int $id
     * @return array
     */
    public function actionFiles($id)
    {
        return (new ServiceReportActions())
            ->actionFiles($id);
    }

    /**
     * GET /v1/service-reports/available/{id}
     *
     * @param int $id ID визита
     * @return array
     */
    public function actionAvailable($id)
    {
        return (new ServiceReportActions())
            ->actionAvailable($id);
    }

    /**
     * GET /v1/service-reports/available-pdf/{id}
     *
     * @param int $id ID визита
     * @return array
     */
    public function actionAvailablePdf($id)
    {
        return (new ServiceReportActions())
            ->actionAvailablePdf($id);
    }
}
