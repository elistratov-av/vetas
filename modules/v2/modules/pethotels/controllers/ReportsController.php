<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\common\components\reports\definitions\FileTypeDefinition;
use app\common\components\reports\definitions\VersionDefinition;
use app\common\components\reports\definitions\ReportDefinition;
use app\modules\v2\modules\BaseController;
use Yii;

class ReportsController extends BaseController
{
    /**
     * @var ReportService
     */
    private $reportService;

    /**
     * ReportsController constructor.
     *
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        $this->reportService = Yii::$app->reportService;
        parent::__construct($id, $module, $config);
    }

    public function actionStayReport($animal_type = '', $date_from, $date_to, $id_pet_hotel)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::PET_HOTEL_STAY_REPORT,
                VersionDefinition::V1
            )
            ->make(new \app\common\components\reports\handlers\PetHotelStayReport\v1\dto\MakeRequestDto([
                'animalType' => $animal_type,
                'dateFrom' => $date_from,
                'dateTo' => $date_to,
                'idPetHotel' => $id_pet_hotel,
            ]), FileTypeDefinition::EXCEL);
    }
}