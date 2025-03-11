<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\models\db\Breeds;
use app\models\db\Brood;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PetHotel;
use app\models\db\PetHotelRequest;
use app\models\db\PetHotelRoom;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Species;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\ContractUploadForm;
use app\modules\v2\modules\pethotels\models\RequestModel;
use app\modules\v2\modules\pethotels\models\RoomModel;
use app\modules\v2\modules\pethotels\models\StatusModel;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class RequestController extends BaseController
{

    /**
     * Возвращает список всех заявок
     * @param $id_room
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll($id_room)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RequestModel();
        return [
            'result' => $model->list([$id_room])->all()
        ];
    }

    /**
     * Возвращает список всех заявок
     * @param $id_pet_hotel
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAllByHotel($id_pet_hotel = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($id_pet_hotel)) {
            return [
                'result' => []
            ];
        }

        $model_1 = new RoomModel();
        $rooms = $model_1->list($id_pet_hotel)->all();
        if (empty($rooms)) {
            return [
                'result' => []
            ];
        }
        $rooms_ids = $model_1->getIdsArray($rooms);
        $model_2 = new RequestModel();
        return [
            'result' => $model_2->list($rooms_ids)->all(),
        ];
    }

    /**
     * Создает новую заявку
     *
     * @param $id_status
     * @param $id_owner
     * @param $id_animal
     * @param $id_room
     * @param $date_from
     * @param $date_to
     * @param $notes
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate(
        $id_status, $id_owner, $id_animal, $id_room,
        $date_from, $date_to, $notes
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RequestModel();
        $animal = $model->create(
            $id_status, $id_owner, $id_animal, $id_room,
            $date_from, $date_to, $notes
        );
        return [
            'result' => true,
            'id' => $animal->id,
        ];
    }

    /**
     * Удаляет заявку
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

        $model = new RequestModel();
        $model->delete($id);
        return [
            'result' => true
        ];
    }

    public function actionProlong($id, $date_to) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RequestModel();

        return [
            'result' => $model->prolong($id, $date_to)
        ];
    }

    public function actionJotting($id, $animal_weight, $count_feeding, $animal_diet_dry, $animal_diet_wet, $feeding_rate, $count_walking, $notes) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RequestModel();

        return [
            'result' => $model->jotting($id, $animal_weight, $count_feeding, $animal_diet_dry, $animal_diet_wet, $feeding_rate, $count_walking, $notes)
        ];
    }

    /**
     * Редактирование заявки
     *
     * @param $id
     * @param $id_status
     * @param $id_owner
     * @param $id_animal
     * @param $id_room
     * @param $date_from
     * @param $date_to
     * @param $notes
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit(
        $id, $id_status = null, $id_owner = null, $id_animal = null, $id_room = null,
        $date_from = null, $date_to = null, $notes = null,
        $animal_weight = null, $count_feeding = null, $animal_diet_dry = null, $animal_diet_wet = null, $feeding_rate = null, $count_walking = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RequestModel();
        $model->edit(
            $id, $id_status, $id_owner,
            $id_animal, $id_room, $date_from,
            $date_to, $notes,
            $animal_weight, $count_feeding, $animal_diet_dry, $animal_diet_wet, $feeding_rate, $count_walking
        );
        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанную заявку
     *
     * @param $id
     * @param $docx
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id, $docx = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model_request = new RequestModel();
        $request = $model_request->get($id);
        if (!$docx) {
            return [
                'result' => $request
            ];
        } else {
            $query = new Query();
            $hotel_org = $query
                ->select([
                    'phr.id as room_id',
                    'ph.id as hotel_id',
                    'ph.name as hotel_name',
                    'o.id as org_id',
                    'o.inn as org_inn',
                    'o.kpp as org_kpp',
                    'o.ogrn as org_ogrn',
                    'o.name as org_name',
                    'a.name as org_address'
                ])
                ->from(PetHotelRoom::tableName() . ' phr')
                ->leftJoin(PetHotel::tableName() . ' ph', 'ph.id = phr.id_pet_hotel')
                ->leftJoin('public.organizations o', 'o.id = ph.id_organization')
                ->leftJoin('public.addresses a', 'a.id = o.id_address')
                ->where(['phr.id' => $request['id_room']])
                ->one();

            $petOwner = PetOwners::find()->where(['id' => $request['id_owner']])->one();
            $contactType = ContactTypes::find()->where(['name' => 'Электронная почта'])->one();

            if (!empty($contactType)) {
                $contact = Contacts::find()
                    ->where(['id_contact_type' => $contactType->id])
                    ->andWhere(['entity_type' => Contacts::ENTITY_TYPE_PET_OWNER])
                    ->andWhere(['entity_id' => $request['id_owner']])
                    ->one();
            }

            if (!empty($contact)) {
                $email = $contact->name;
            }else{
                $email = '';
            }

            $template_path = realpath(__DIR__) . '/../templates/contract.docx';
            $templateWord = new TemplateProcessor($template_path);

            $templateWord->setValue(
                "request_id", $request['id']
            );
            $templateWord->setValue(
                "request_date_created",
                date("d.m.Y", strtotime($request['created_at']))
            );
            $templateWord->setValue(
                "request_date_from",
                date("d.m.Y", strtotime($request['date_from']))
            );
            $templateWord->setValue(
                "request_date_to",
                date("d.m.Y", strtotime($request['date_to']))
            );
            $templateWord->setValue(
                "owner_fio", $request["owner_fio"]
            );
            $templateWord->setValue(
                "owner_phone", $request["owner_phone"]
            );
            $templateWord->setValue(
                "owner_address", $request["owner_address"]
            );
            $templateWord->setValue(
                "animal_type", $request["animal_type"]
            );
            $templateWord->setValue(
                "animal_breed", $request["animal_breed"]
            );
            $templateWord->setValue(
                "animal_nickname", $request["animal_nickname"]
            );
            $templateWord->setValue(
                "room_name", $request["room_name"]
            );
            $templateWord->setValue(
                "pet_hotel_name", $request["hotel_name"]
            );
            $templateWord->setValue(
                "pet_hotel_org_name", $hotel_org["org_name"]
            );
            $templateWord->setValue(
                "pet_hotel_org_address", $hotel_org["org_address"]
            );
            $templateWord->setValue(
                "pet_hotel_org_inn", $hotel_org["org_inn"]
            );
            $templateWord->setValue(
                "pet_hotel_org_kpp", $hotel_org["org_kpp"]
            );
            $templateWord->setValue(
                "pet_hotel_org_ogrn", $hotel_org["org_ogrn"]
            );
            $templateWord->setValue(
                "owner_passport_series", $petOwner->passport_series
            );
            $templateWord->setValue(
                "owner_passport_number", $petOwner->passport_number
            );
            $templateWord->setValue(
                "owner_passport_issue_date", $petOwner->passport_issue_date
            );
            $templateWord->setValue(
                "owner_passport_issuer", $petOwner->passport_issuer
            );
            $templateWord->setValue(
                "owner_email", $email
            );
            $tmpFileName = $templateWord->save();
            $response = \Yii::$app->getResponse();
            return $response->sendFile(
                $tmpFileName, "contract.docx",
                ['mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            );
        }
    }

    public function actionMemo($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $modelRequest = new RequestModel();
        $request = $modelRequest->get($id);

        $template_path = realpath(__DIR__) . '/../templates/memo.docx';

        $templateWord = new TemplateProcessor($template_path);

        $templateWord->setValue("animal_type", $request['animal_type']);

        $templateWord->setValue("animal_nickname", $request['animal_nickname']);

        $templateWord->setValue("owner_fio", $request['owner_fio']);

        $templateWord->setValue(
            "date_from",
            date("d.m.Y", strtotime($request['date_from']))
        );

        $templateWord->setValue(
            "date_to",
            date("d.m.Y", strtotime($request['date_to']))
        );

        $tmpFileName = $templateWord->save();
        $response = \Yii::$app->getResponse();
        return $response->sendFile(
            $tmpFileName, "memo.docx",
            ['mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        );
    }
    public function actionGetJotting($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $modelRequest = new RequestModel();
        $request = $modelRequest->get($id);

        $template_path = realpath(__DIR__) . '/../templates/jotting.docx';

        $templateWord = new TemplateProcessor($template_path);

        $templateWord->setValue("id_room", $request['id_room']);
        $templateWord->setValue("animal_breed", $request['animal_breed']);
        $templateWord->setValue("animal_age", $request['animal_age']);
        $templateWord->setValue("animal_weight", $request['animal_weight']);
        $templateWord->setValue("animal_nickname", $request['animal_nickname']);
        $templateWord->setValue("count_feeding", $request['count_feeding']);
        $templateWord->setValue("diet_dry", $request['animal_diet_dry']);
        $templateWord->setValue("diet_wet", $request['animal_diet_wet']);
        $templateWord->setValue("feeding_rate", $request['feeding_rate']);
        $templateWord->setValue("count_walking", $request['count_walking']);
        $templateWord->setValue("owner_fio", $request['owner_fio']);
        $templateWord->setValue("id", $request['id']);
        $templateWord->setValue("owner_address", $request['owner_address']);
        $templateWord->setValue("owner_phone", $request['owner_phone']);
        $templateWord->setValue("notes", $request['notes']);
        $templateWord->setValue(
            "date_from",
            date("d.m.Y", strtotime($request['date_from']))
        );
        $templateWord->setValue(
            "date_to",
            date("d.m.Y", strtotime($request['date_to']))
        );

        $pet = Pets::find()->where(['id' => $request['id_animal']])->one();

        $specie = Species::find()->where(['id'=> $pet->id_species])->one();

        if (empty($pet) || empty($specie)) {

            $templateWord->setImageValue("photo", array('path' => \Yii::getAlias('@webroot') . '/images/favicon.png', 'width' => 250, 'height' => 241, 'ratio' => true));
        } else {
            if ($specie->tech_name == Species::TECH_NAME_CAT) {
                $templateWord->setImageValue("photo", array('path' => \Yii::getAlias('@webroot') . '/images/cat.png', 'width' => 250, 'height' => 241, 'ratio' => true));
            } elseif ($specie->tech_name == Species::TECH_NAME_DOG) {
                $templateWord->setImageValue("photo", array('path' => \Yii::getAlias('@webroot') . '/images/dog.jpg', 'width' => 250, 'height' => 241, 'ratio' => true));
            } else {
                $templateWord->setImageValue("photo", array('path' => \Yii::getAlias('@webroot') . '/images/favicon.png', 'width' => 250, 'height' => 241, 'ratio' => true));
            }
        }

        $tmpFileName = $templateWord->save();
        $response = \Yii::$app->getResponse();
        return $response->sendFile(
            $tmpFileName, "jotting.docx",
            ['mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        );
    }

    /**
     * Поиск заявки
     *
     * @param $id_status
     * @param $id_owner
     * @param $id_animal
     * @param $id_room
     * @param $id_hotel
     * @param $date_created
     * @param $date_from
     * @param $date_to
     * @param $notes
     * @param $owner_fio
     * @param $owner_phone
     * @param $animal_type
     * @param $animal_nickname
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $id_status = null, $id_owner = null, $id_animal = null,
        $id_room = null, $id_hotel = null,
        $date_created = null, $date_from = null, $date_to = null,
        $notes = null, $owner_fio = null, $owner_phone = null,
        $animal_type = null, $animal_nickname = null, $room_name = null,
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
        if (is_null($date_from) and is_null($date_to) and is_null($date_created)) {
            $date_from = date('Y-m-01');
            $date_to = date('Y-m-t');
        }
        if (!is_null($owner_phone)) {
            $owner_phone = preg_replace("/[^0-9]/", "", $owner_phone);
        }
        $model = new RequestModel();
        $searchQuery = $model->searchQuery(
            $id_status, $id_owner, $id_animal,
            $id_room, $id_hotel,
            $date_created, $date_from, $date_to,
            $notes, $owner_fio, $owner_phone,
            $animal_type, $animal_nickname, $room_name
        );
        return [
            'result' => $model->search(
                $searchQuery, $page, $limit
            ),
        ];
    }

    /**
     * Возвращает общее количество заявок в разрезе статусов
     *
     * @param $id_hotel
     * @param $id_room
     * @param $date_from
     * @param $date_to
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCountByStatus(
        $id_hotel = null, $id_room = null,
        $date_from = null, $date_to = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($id_hotel) and (is_null($id_room))) {
            return [
                'result' => [],
            ];
        }

        $room_ids = [];
        if (!is_null($id_hotel)) {
            $model_1 = new RoomModel();
            $rooms = $model_1->list($id_hotel)->all();
            if (!empty($rooms)) {
                $room_ids = $model_1->getIdsArray($rooms);
            }
        } else if (!is_null($id_room)) {
            $room_ids = [$id_room];
        }

        if (empty($room_ids)) {
            return [
                'result' => [],
            ];
        }

        $model_2 = new RequestModel();
        $requests = $model_2
            ->list($room_ids, $date_from, $date_to)
            ->all();

        $model_3 = new StatusModel();
        $statuses = $model_3->list()->all();
        $counts = [];
        foreach ($statuses as $status) {
            foreach ($requests as $request) {
                if ($status['id'] == $request['id_status']) {
                    $status_name = $status['name'];
                    if (empty($counts[$status_name])) {
                        $counts[$status_name] = 1;
                    } else {
                        $counts[$status_name] += 1;
                    }
                }
            }
        }
        return [
            'result' => $counts,
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionUploadContract($id) {
        if (\Yii::$app->request->isPost) {
            $form = new ContractUploadForm();
            $form->id = $id;
            $form->file = UploadedFile::getInstanceByName('file');

            if ($form->validate()) {
                $requests = PetHotelRequest::find()->where(['id' => $id])->all();
                if (empty($requests)) {
                    throw new BadRequestHttpException("Заявка с id=$id не найдена");
                }
                $upload_path = \Yii::getAlias('@webroot/upload/requests/');
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                $uploaded_file = $upload_path . $id . '.pdf';
                $form->file->saveAs($uploaded_file);
                return [
                    'result' => true
                ];
            } else {
                $errors = $form->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка загрузки файла' : implode("\n", array_values($errors)));
            }
        } else {
            return [
                'result' => false,
                'error' => 'POST expected',
            ];
        }
    }

    public function actionUploadedContract($id)
    {
        if (is_string($id) and str_contains($id, '.')) {
            throw new BadRequestHttpException("Invalid id");
        }
        $upload_path = \Yii::getAlias('@webroot/upload/requests/');
        $uploaded_file = $upload_path . $id . '.pdf';
        if (file_exists($uploaded_file)) {
            return \Yii::$app
                ->response
                ->sendFile(
                    $uploaded_file, "contract-$id.pdf",
                    ['mimeType' => 'application/pdf']
                );
        } else {
            throw new NotFoundHttpException("Uploaded file not found");
        }
    }

}
