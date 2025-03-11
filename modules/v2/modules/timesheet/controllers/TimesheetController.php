<?php

namespace app\modules\v2\modules\timesheet\controllers;

use app\modules\admin\models\export\SendXlsxTrait;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\timesheet\models\TimeSheetModel;
use app\modules\v2\modules\timesheet\models\TimesheetsCopy;
use app\modules\v2\modules\timesheet\models\TimesheetsSave;
use app\modules\v2\modules\timesheet\models\TimesheetsSaveInfo;
use app\modules\v2\modules\timesheet\skeletons\timesheet\Lists;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Class TimesheetController
 * работа с расписанием
 *
 * @package app\modules\v2\modules\timesheet\controllers
 */
class TimesheetController extends BaseController
{
    use SendXlsxTrait;

    /**
     * метод для получения расписаний
     *
     * @param integer $id_organization
     * @param array   $shift_type_id
     * @param string  $date_from
     * @param int     $days_count
     * @param int     $page
     * @param int     $limit
     *
     * @return Lists
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionList(
        int $id_organization,
        string $date_from,
        array $shift_type_id = [],
        array $id_specialist = [],
        int $days_count = 14,
        int $page = 1,
        $limit = 10
    ): Lists {

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $this->validateDate($date_from);
        if ($limit !== false && $limit <= 0) {
            throw new BadRequestHttpException('Параметр limit должен быть больше нуля');
        }
        if ($page <= 0) {
            throw new BadRequestHttpException('Параметр page должен быть больше нуля');
        }

        $timeSheetModel = new TimeSheetModel();

        return $timeSheetModel->list($date_from, $id_organization, $shift_type_id, $id_specialist, $days_count, $page, $limit);
    }

    /**
     * валидация даты
     *
     * @param        $dateText
     * @param string $dateName
     *
     * @throws BadRequestHttpException
     */
    private function validateDate($dateText, $dateName = 'date_from'): void
    {
        if (empty($dateText)) {
            return;
        }

        $matches = [];
        $result = preg_match('~^((\d{4})\-(\d{1,2})\-(\d{1,2}))$~', $dateText, $matches);

        if ($result !== 1) {
            throw new BadRequestHttpException("Параметр {$dateName} имеет невалидный формат");
        } elseif (!isset($matches[2], $matches[3], $matches[4])) {
            throw new BadRequestHttpException("Параметр {$dateName} имеет невалидный формат");
        } elseif (checkdate($matches[3], $matches[4], $matches[2]) === false) {
            throw new BadRequestHttpException("Параметр {$dateName} имеет невалидный формат");
        }
    }

    /**
     * метод сохранения таймшитов
     *
     * @param array    $range
     * @param array    $specialists
     * @param array    $timesheet
     * @param int      $id_organization
     * @param int|null $vaccination_station_id
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionSave(
        array $range,
        array $specialists,
        array $timesheet,
        int $id_organization,
        int $vaccination_station_id = null
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new TimesheetsSave();
        $model->load($this->actionParams, '');

        $count_visits_to_transfer = $model->save();

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors))
            );
        }

        return [
            'result' => true,
            'count_visits_to_transfer' => $count_visits_to_transfer,
        ];
    }

    public function actionTransfercounts(
        array $range,
        array $specialists,
        array $timesheet,
        int $id_organization,
        int $vaccination_station_id = null
    ): array {
        $model = new TimesheetsSaveInfo();
        $model->load($this->actionParams, '');
        $count_visits_to_transfer = $model->save();
        return [
            'result' => $count_visits_to_transfer
        ];
    }

    /**
     * @param string $source_start_date
     * @param int    $source_specialist_id
     * @param int    $duration
     * @param string $destination_date
     * @param array  $destination_specialist_id
     * @param int    $id_organization
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     */
    public function actionCopy(
        string $source_start_date,
        int $source_specialist_id,
        int $duration,
        string $destination_date,
        array $destination_specialist_id,
        int $id_organization
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $this->validateDate($source_start_date);
        $this->validateDate($destination_date);

        (new TimesheetsCopy())->copy(
            $source_start_date,
            $source_specialist_id,
            $duration,
            $destination_date,
            $destination_specialist_id,
            $id_organization
        );


        return [
            'result' => true,
        ];

    }

    /**
     * Получение xlsx-документа с документами
     *
     * @param integer $id_organization
     * @param array $shift_type_id
     * @param string $date_from
     * @param int $days_count
     *
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionXlsx(
        int    $id_organization,
        string $date_from,
        array  $shift_type_id = [],
        int    $days_count = 14
    ): Response
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $this->validateDate($date_from);
        $timeSheetModel = new TimeSheetModel();
        $list = $timeSheetModel->list($date_from, $id_organization, $shift_type_id, $days_count, 1, 10000);
        $specialists = $list->specialists;
        $timesheets = $list->timesheet;

        $date_to = date('Y-m-d',
            strtotime($date_from . ' + ' . $days_count . ' days')
        );

        @ini_set('memory_limit', '1048M');

        $query_org = new Query();
        $org = $query_org
            ->select([
                'o.id as id',
                'o.name as name',
                'a.name as address'
            ])
            ->from('public.organizations o')
            ->leftJoin('public.addresses a', 'a.id = o.id_address')
            ->where(['o.id' => $id_organization])
            ->one();

        $query_shift_type = new Query();
        $shift_types = $query_shift_type
            ->select([
                'id', 'description',
            ])
            ->from('shift_type')
            ->all();

        $shift_types_ar = [];
        foreach ($shift_types as $shift_type) {
            $shift_types_ar[$shift_type['id']] = $shift_type['description'];
        }

        $xlsx_reader = new Xlsx();
        $xlsx_template = "timesheets.xlsx";
        $template_path = realpath(__DIR__) . '/../templates/' . $xlsx_template;
        $spreadsheet = $xlsx_reader->load($template_path);
        $sheet = $spreadsheet->getSheet(0);
        $row_iterator = $sheet->getRowIterator();
        $row_idx = 0;
        while ($row_iterator->valid()) {
            $row_idx += 1;
            $row = $row_iterator->current();
            $row_iterator->next();
            $cell_iterator = $row->getCellIterator();
            $do_break = false;
            while ($cell_iterator->valid()) {
                $cell = $cell_iterator->current();
                $cell_iterator->next();
                $cell_value = $cell->getValue();
                if (!is_string($cell_value)) {
                    continue;
                }
                if ($cell_value == '%rownum%') {
                    $do_break = true;
                    break;
                }
                if (str_contains($cell_value, "%org_name%")) {
                    $cell->setValue(str_replace(
                        "%org_name%", $org['name'],
                        $cell_value
                    ));
                }
                $cell_value = $cell->getValue();
                if (str_contains($cell_value, "%date_from%")) {
                    $cell->setValue(str_replace(
                        "%date_from%", date("d.m.Y", strtotime($date_from)),
                        $cell_value
                    ));
                }
                $cell_value = $cell->getValue();
                if (str_contains($cell_value, "%date_to%")) {
                    $cell->setValue(str_replace(
                        "%date_to%", date("d.m.Y", strtotime($date_to)),
                        $cell_value
                    ));
                }
            }
            if ($do_break) {
                break;
            }
        }
        $data_row_idx = $row_idx;
        foreach ($timesheets as $timesheet) {
            foreach ($specialists as $specialist) {
                if ($timesheet['id_specialist'] != $specialist['id']) {
                    continue;
                }
                $row_idx += 1;
                $sheet->insertNewRowBefore($row_idx);
                $col_idx = 1;
                $data_cell = $sheet->getCellByColumnAndRow($col_idx, $data_row_idx);
                while (!is_null($data_cell)) {
                    $data_cell_value = $data_cell->getValue();
                    if (is_null($data_cell_value)) {
                        break;
                    }
                    if (is_string($data_cell_value)) {
                        if (str_contains($data_cell_value, "%rownum%")) {
                            $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                "%rownum%", (string)($row_idx - $data_row_idx),
                                $data_cell_value
                            ));
                        }
                        if (str_contains($data_cell_value, "%fio%")) {
                            $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                "%fio%", $specialist['fio'],
                                $data_cell_value
                            ));
                        }
                        if (str_contains($data_cell_value, "%specialization_name%")) {
                            $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                "%specialization_name%", $specialist['specialization_name'],
                                $data_cell_value
                            ));
                        }
                        if (str_contains($data_cell_value, "%shift_from%")) {
                            $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                "%shift_from%", $timesheet['from'],
                                $data_cell_value
                            ));
                        }
                        if (str_contains($data_cell_value, "%shift_to%")) {
                            $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                "%shift_to%", $timesheet['to'],
                                $data_cell_value
                            ));
                        }
                        $shift_type = $shift_types_ar[$timesheet['id_shift_type']];
                        if (empty($shift_type)) {
                            $shift_type = '';
                        }
                        if (str_contains($data_cell_value, "%shift_type%")) {
                            $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                "%shift_type%", $shift_type,
                                $data_cell_value
                            ));
                        }
                        if (str_contains($data_cell_value, "%cabinet_name%")) {
                            $cabinet_name = $timesheet['cabinet_name'];
                            if (empty($cabinet_name)) {
                                $cabinet_name = '';
                            }
                            $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                "%cabinet_name%", $cabinet_name,
                                $data_cell_value
                            ));
                        }
                    }
                    $col_idx += 1;
                    $data_cell = $sheet->getCellByColumnAndRow($col_idx, $data_row_idx);
                }
                break;
            }
        }
        $sheet->removeRow($data_row_idx);

        return $this->sendXlsx(
            $spreadsheet, $xlsx_template,
            ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

}
