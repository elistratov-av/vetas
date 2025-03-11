<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\models\db\PetHotel;
use app\models\db\PetHotelRequest;
use app\models\db\PetHotelRoom;
use app\modules\admin\models\export\SendXlsxTrait;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\AnimalModel;
use app\modules\v2\modules\pethotels\models\ItemModel;
use app\modules\v2\modules\pethotels\models\OwnerModel;
use app\modules\v2\modules\pethotels\models\RequestModel;
use app\modules\v2\modules\pethotels\models\RoomModel;
use app\modules\v2\modules\pethotels\models\StatusModel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ItemController extends BaseController
{
    use SendXlsxTrait;

    /**
     * Возвращает список всех зоогостиниц для заданной организации
     * @param $id_organization
     * @param $include_status_for_date
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll($id_organization, $include_status_for_date = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($include_status_for_date)) {
            $include_status_for_date = date('Y-m-d');
        }

        $model_1 = new ItemModel();
        /** @var PetHotel $all_hotels */
        $all_hotels = $model_1
            ->list($id_organization)
            ->all();
        if (!empty($all_hotels)) {
            foreach ($all_hotels as &$hotel) {
                $hotel['busy_rooms'] = 0;
                $hotel['free_rooms'] = 0;
                $model_2 = new RoomModel();
                /** @var PetHotelRoom $hotel_rooms */
                $hotel_rooms = $model_2
                    ->list($hotel['id'])
                    ->all();
                if (!empty($hotel_rooms)) {
                    $room_ids = $model_2->getIdsArray($hotel_rooms);
                    $model_3 = new RequestModel();
                    /** @var PetHotelRequest $requests */
                    $requests = $model_3->listBusy(
                        $room_ids, $include_status_for_date,
                        $include_status_for_date
                    )->all();
                    if (!empty($requests)) {
                        foreach ($requests as $request) {
                            foreach ($hotel_rooms as $room) {
                                if ($request['id_room'] == $room['id']) {
                                    $hotel['busy_rooms'] += 1;
                                }
                            }
                        }
                    }
                    $hotel['free_rooms'] = count($room_ids) - $hotel['busy_rooms'];
                }
            }
        }
        return [
            'result' => $all_hotels,
        ];
    }

    /**
     * Создает новую зоогостиницу
     *
     * @param $name
     * @param $id_organization
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($name, $id_organization)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ItemModel();
        $hotel = $model->create($name, $id_organization);
        return [
            'result' => true,
            'id' => $hotel->id,
        ];
    }

    /**
     * Удаляет зоогостиницу
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ItemModel();
        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование зоогостиницы
     *
     * @param $id
     * @param $name
     * @param $id_organization
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $name = null, $id_organization = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ItemModel();
        $model->edit($id, $name, $id_organization);
        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанную зоогостиницу
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ItemModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Поиск зоогостиниц
     *
     * @param $name
     * @param $id_organization
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $name = null, $id_organization = null,
        $page = null, $limit = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $model = new ItemModel();
        return [
            'result' => $model->search(
                $name, $id_organization,
                $page, $limit
            ),
        ];
    }

    /**
     * Календарь занятости помещений зоогостиницы
     *
     * @param $id_pet_hotel
     * @param $date_from
     * @param $date_to
     * @param $purpose
     * @param $xlsx
     *
     * @throws NotFoundHttpException|BadRequestHttpException
     * @throws \Throwable
     */
    public function actionCalendar(
        $id_pet_hotel, $date_from, $date_to,
        $purpose = null, $xlsx = false,
        $xlsx_template = "room_calendar.xlsx"
    )
    {
        $model_room = new RoomModel();
        /** @var PetHotelRoom $hotel_rooms */
        $hotel_rooms = $model_room
            ->list($id_pet_hotel, $purpose)
            ->all();
        $result = [];
        foreach ($hotel_rooms as $room) {
            $result[] = [
                'id' => $room['id'],
                'name' => $room['name'],
                'purpose' => $room['purpose'],
                'requests' => []
            ];
        }
        $room_ids = $model_room->getIdsArray($hotel_rooms);
        $model_request = new RequestModel();
        /** @var PetHotelRequest $requests */
        $requests = $model_request->list(
            $room_ids, $date_from,
            $date_to
        )->all();
        $model_status = new StatusModel();
        $model_animal = new AnimalModel();
        $model_owner = new OwnerModel();

        if (!$xlsx) {
            $id = 0;
        }
        foreach ($requests as $request) {
            foreach ($result as &$room) {
                if ($request['id_room'] == $room['id']) {
                    $status = $model_status->get($request['id_status']);
                    $animal = $model_animal->get($request['id_animal']);
                    $owner = $model_owner->get($animal['id_owner']);
//                    $req_date_from = max([$date_from, $request['date_from']]);
//                    $req_date_to = min([$date_to, $request['date_to']]);
                    $req_date_from = $request['date_from'];
                    $req_date_to = $request['date_to'];

                    if (!$xlsx) {
                        if ($id == $request['id']) {
                            continue;
                        }
                        $id = $request['id'];
                    }

                    $room['requests'][] = [
                        'id' => $request['id'],
                        'id_status' => $status['id'],
                        'status' => $status['name'],
                        'status_code' => $status['code'],
                        'animal_id' => $animal['id'],
                        'animal' => $animal['nickname'],
                        'animal_gender_male' => $animal['gender_male'],
                        'animal_breed' => $request['animal_breed'],
                        'animal_age' => $request['animal_age'],
                        'owner_fio' => $owner['i_fio'] . ' ' . $owner['o_fio'] . ' ' . $owner['f_fio'],
                        'owner_phone' => $owner['phone_number'],
                        'date_from' => $req_date_from,
                        'date_to' => $req_date_to,
                        'notes' => $request['notes'],
                    ];
                }
            }
        }
        if (!$xlsx) {
            return [
                'result' => $result
            ];
        } else {
            @ini_set('memory_limit', '512M');

            $xlsx_reader = new Xlsx();
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
                    if (str_contains($cell_value, "%hotel_name%")) {
                        $item_model = new ItemModel();
                        $hotel = $item_model->get($id_pet_hotel);
                        $cell->setValue(str_replace(
                            "%hotel_name%", $hotel->name,
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
            $model_status = new StatusModel();
            $model_animal = new AnimalModel();
            $model_owner = new OwnerModel();
            foreach ($requests as $request) {
                foreach ($result as &$room) {
                    if ($request['id_room'] != $room['id']) {
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
                            if (str_contains($data_cell_value, "%room_name%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%room_name%", $room['name'],
                                    $data_cell_value
                                ));
                            }
                            if (str_contains($data_cell_value, "%request_id%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%request_id%", $request['id'],
                                    $data_cell_value
                                ));
                            }
                            if (str_contains($data_cell_value, "%request_status%")) {
                                $status = $model_status->get($request['id_status']);
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%request_status%", $status['code'],
                                    $data_cell_value
                                ));
                            }
                            $animal = $model_animal->get($request['id_animal']);
                            if (str_contains($data_cell_value, "%animal_nickname%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%animal_nickname%", $animal['nickname'],
                                    $data_cell_value
                                ));
                            }
                            if (str_contains($data_cell_value, "%animal_type%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%animal_type%", $animal['type'],
                                    $data_cell_value
                                ));
                            }
                            if (str_contains($data_cell_value, "%animal_breed%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%animal_breed%", $animal['breed'],
                                    $data_cell_value
                                ));
                            }
                            $owner = $model_owner->get($animal['id_owner']);
                            if (str_contains($data_cell_value, "%owner_fio%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%owner_fio%", $owner['i_fio'] . ' ' . $owner['o_fio'] . ' ' . $owner['f_fio'],
                                    $data_cell_value
                                ));
                            }
                            // if (str_contains($data_cell_value, "%owner_phone%")) {
                            //     $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                            //         "%owner_phone%", '+' . $owner['phone_number'],
                            //         $data_cell_value
                            //     ));
                            // }
                            if (str_contains($data_cell_value, "%owner_phone%")) {
                                if($owner['phone_number']){
                                    $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                        "%owner_phone%", ' +' . $owner['phone_number'],
                                        $data_cell_value
                                    ));
                                }
                            }
                            if (str_contains($data_cell_value, "%request_date_from%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%request_date_from%", date("d.m.Y", strtotime($request['date_from'])),
                                    $data_cell_value
                                ));
                            }
                            if (str_contains($data_cell_value, "%request_date_to%")) {
                                \Yii::info("req date to: " . $request['date_to']);
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%request_date_to%", date("d.m.Y", strtotime($request['date_to'])),
                                    $data_cell_value
                                ));
                            }
                            if (str_contains($data_cell_value, "%request_notes%")) {
                                $sheet->setCellValueByColumnAndRow($col_idx, $row_idx, str_replace(
                                    "%request_notes%", $request['notes'],
                                    $data_cell_value
                                ));
                            }

                        }
                        $col_idx += 1;
                        $data_cell = $sheet->getCellByColumnAndRow($col_idx, $data_row_idx);
                    }
                }
            }
            $sheet->removeRow($data_row_idx);

            return $this->sendXlsx(
                $spreadsheet, $xlsx_template,
                ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        }
    }

}
