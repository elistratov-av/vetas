<?php

namespace app\modules\v2\modules\reports\controllers;

use app\common\components\reports\definitions\FileTypeDefinition;
use app\common\components\reports\definitions\ReportDefinition;
use app\common\components\reports\definitions\VersionDefinition;
use app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto\MakeSingleRequestDto as ActAcceptanceTransferV1MakeSingleRequestDto;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\MakePeriodRequestDto as ActInventoryWriteOffV1MakePeriodRequestDto;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\MakeSingleRequestDto as ActInventoryWriteOffV1MakeSingleRequestDto;
use app\common\components\reports\handlers\UsedTmcInReception\v1\dto\MakeSingleRequestDto as UsedTmcInReceptionV1MakeSingleRequestDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\MakeTmcRequestDto as ActVaccineWriteOffV1MakeTmcRequestDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\MakePeriodRequestDto as ActVaccineWriteOffV1MakePeriodRequestDto;

use app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\dto\MakeRequestDto;
use app\common\components\reports\ReportService;
use app\modules\v2\modules\BaseController;
use Yii;

/**
 * Class BalanceActionsController
 *
 * @package app\modules\v2\modules\reports\controllers
 * @author Aleksandr Roik
 */
class BalanceActionsController extends BaseController
{
    /**
     * @var ReportService
     */
    private $reportService;

    /**
     * BalanceActionsController constructor.
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

    /**
     * Создает отчет "Акт о списании материальных запасов" по заданому id_balance_action
     *
     * @param int $id_balance_action
     * @param string|null $units
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeActInventoryWriteOffById(int $id_balance_action, string $units = null)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::ACT_INVENTORY_WRITE_OFF,
                VersionDefinition::V1
            )
            ->makeById(
                new ActInventoryWriteOffV1MakeSingleRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL
            );
    }

    /**
     * Создает отчет "Акт о списании материальных запасов" за период
     *
     * @param int $id_organization
     * @param int $id_specialist
     * @param string $month
     * @param string $year
     * @param string|null $units
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeActInventoryWriteOffByPeriod(int $id_organization, int $id_specialist, string $month, string $year, string $units = null)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::ACT_INVENTORY_WRITE_OFF,
                VersionDefinition::V1
            )
            ->makeByPeriod(
                new ActInventoryWriteOffV1MakePeriodRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL
            );
    }

    /**
     * Создает отчет "Накладная по отпуску материалов на сторону"
     *
     * @param int $id_balance_action
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeInvoiceMaterialsLeaveToSide(int $id_balance_action)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::INVOICE_MATERIALS_LEAVE_TO_SIDE,
                VersionDefinition::V1
            )
            ->make(new MakeRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL);
    }

    /**
     * @param int $id_balance_action
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeRequirementInvoice(int $id_balance_action)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::REQUIREMENT_INVOICE,
                VersionDefinition::V1
            )
            ->make(new \app\common\components\reports\handlers\RequirementInvoice\v1\dto\MakeRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL);
    }

    /**
     * Создает отчет "Акт о списании материальных запасов" по заданому id_balance_action
     *
     * @param int $id_balance_action
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeActAcceptanceTransferById(int $id_balance_action)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::ACT_ACCEPTANCE_TRANSFER,
                VersionDefinition::V1
            )
            ->makeById(
                new ActAcceptanceTransferV1MakeSingleRequestDto($this->actionParams),
                FileTypeDefinition::PDF
            );
    }

    /**
     * @param int $id_organization
     * @param int $id_specialist
     * @param string $start_date
     * @param string $end_date
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeMonthlySpendingReport(int $id_organization, int $id_specialist, string $start_date, string $end_date)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::MONTHLY_SPENDING_REPORT,
                VersionDefinition::V1
            )
            ->make(new \app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\MakeRequestDto($this->actionParams), FileTypeDefinition::EXCEL);
    }

    /**
     * @param int $id_organization
     * @param int $id_specialist
     * @param string $month
     * @param string $year
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeDailySpendingReport(int $id_organization, int $id_specialist, string $month, string $year)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::DAILY_SPENDING_REPORT,
                VersionDefinition::V1
            )
            ->make(new \app\common\components\reports\handlers\DailySpendingReport\v1\dto\MakeRequestDto($this->actionParams), FileTypeDefinition::EXCEL);
    }

    /**
     * Создает отчет "Акт списания вакцины ежедневный от врача" за период
     *
     * @param int $id_balance_action
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeActVaccineWriteOffByPeriod(int $id_organization, int $id_specialist, string $date)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::ACT_VACCINE_WRITE_OFF,
                VersionDefinition::V1
            )
            ->makeByPeriod(
                new ActVaccineWriteOffV1MakePeriodRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL
            );
    }


    /**
     * Создает отчет "Акт списания вакцины от врача в припивочных пунктах"
     *
     * @param int $id_balance_action
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeActVaccineWriteOffByJournalVaccination(array $data = null, string $template = null)
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::ACT_VACCINE_WRITE_OFF,
                VersionDefinition::V1
            )
            ->makeByTmc(
                new ActVaccineWriteOffV1MakeTmcRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL
            );
    }

    /**
     * Создает отчет "Требование-заказ"
     *
     * @param int $id_balance_action
     * @param string|null $units
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeRequirementOrder(int $id_balance_action, string $units = 'null')
    {
        return $this->reportService
            ->createHandler(
                ReportDefinition::REQUIREMENT_ORDER,
                VersionDefinition::V1
            )
            ->make(new \app\common\components\reports\handlers\RequirementOrder\v1\dto\MakeRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL);
    }

    /**
     * Создает отчет по организации
     * @param int $id_organization
     * @param string $month
     * @param string $year
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeOrganizationReport(int $id_organization, string $month, string $year)
    {
        return $this->reportService
            ->createHandler(ReportDefinition::ORGANIZATION_REPORT, VersionDefinition::V1)
            ->make(new \app\common\components\reports\handlers\OrganizationReport\v1\dto\MakeRequestDto($this->actionParams),
                FileTypeDefinition::EXCEL);
    }

    /**
     * Создает отчет по балансовых ТМЦ, использованых в услугуах приема
     * @param int $id_organization
     * @param string $month
     * @param string $year
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMakeUsedTmcInReception(int $id_visit)
    {
        return $this->reportService
            ->createHandler(ReportDefinition::USED_TMC_IN_RECEPTION, VersionDefinition::V1)
            ->makeById(new UsedTmcInReceptionV1MakeSingleRequestDto($this->actionParams), FileTypeDefinition::PDF);
    }
}
