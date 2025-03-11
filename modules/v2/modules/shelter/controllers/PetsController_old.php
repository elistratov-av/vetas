<?php

namespace app\modules\v2\modules\shelter\controllers;

use app\models\db\Aviary;
use app\models\db\Files;
use app\models\db\PetHealth;
use app\models\db\PetHistory;
use app\models\db\PetIdentification;
use app\models\db\PetRefSkill;
use app\models\db\Pets;
use app\models\db\PetOwners;
use app\models\db\PetToSkills;
use app\models\db\ShelterGuests;
use app\models\db\RegExpireReasons;
use app\models\db\Species;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\shelter\models\ShelterGuestModel;
use app\modules\v2\modules\shelter\models\ShelterSearchModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use app\modules\v1\models\FileResource;
use yii\httpclient\Exception;
use Yii;
use app\models\db\DocumentTypes;
use app\models\db\Documents;
use app\models\db\Organizations;

/**
 * Class PetsController
 * @package app\modules\v2\modules\shelter\controllers
 */
class PetsController extends BaseController
{
    /**
     * @param int $id_pet
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet(int $id_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShelterGuestModel();
        $result = $model->getPet($id_pet);

        return ($result === false)
            ? $this->errorResponse($model)
            : [
                'result' => $result,
            ];
    }

    /**
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(
        int $page = 1,
        $limit = 10,
        array $filter = [],
              $sortBy = null,
              $sortDesc = false
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        //$searchModel = new ShelterSearchModel();
        /* выбираем всех, у кого есть записи о карантине ранее сегодняшнего дня - 10 дней (т.е. карантин длится более 10 дней)
        * меняем статус с Карантин на Карантин (пр)
        */
        $dumpQuery = ShelterGuests::find()
        ->alias('sg')
        ->select('sg.id, sg.id_pet, sg.status')
        ->all();


        $subQuery = ShelterGuests::find()
            ->alias('sg')
            ->select('sg.id')
            ->distinct()
            ->joinWith('petHealths ph')
            ->where(['AND',
                ['ph.status' => PetHealth::HEALTH_STATUS_QUARANTINE],
                ['=','sg.status','QUARANTINE'],
                ['<', 'ph.date', date('Y-m-d', strtotime('today -10 days'))],
                ['ph.protected_at' => null],
            ]);


        ShelterGuests::updateAll(['status' => ShelterGuests::STATUS_QUARANTINE_OTHER], ['AND',
            ['in', 'id', $subQuery],
        ]);

        /* выбираем всех, у кого есть записи о карантине позднее сегодняшнего дня (т.е. находятся все еще на карантине)
         * исключаем их из обновления статуса
         */
        $subQuery = ShelterGuests::find()
            ->alias('sg')
            ->select('sg.id')
            ->distinct()
            ->joinWith('petHealths ph')
            ->where(['AND',
                ['ph.status' => PetHealth::HEALTH_STATUS_QUARANTINE],
                ['>=', 'ph.date', date('Y-m-d')]
            ]);
        
        ShelterGuests::updateAll(['status' => ShelterGuests::STATUS_QUARANTINE], ['AND',
        ['in', 'id', $subQuery]
        ]);

        $addQuarantineDate = date_create(date('Y-m-d'));
        $addQuarantineDate->modify('-10 day');
        $d = date_format($addQuarantineDate, 'Y-m-d');
        $addQuarantineQuery = ShelterGuests::find()
        ->alias('sg')
        ->select('sg.id')
        ->joinWith('petHealths ph')
        ->where(['>=', 'ph.date', $d])
        ->groupBy(['sg.id'])
        ->having('count(ph.date)>10');

        ShelterGuests::updateAll(['status' => ShelterGuests::STATUS_QUARANTINE_OTHER], ['AND',
        ['in', 'id', $addQuarantineQuery]
        ]);

        ShelterGuests::updateAll(['status' => ShelterGuests::STATUS_IN_SHELTER], ['AND',
            ['not in', 'id', $subQuery],
            ['OR',
                ['status' => ShelterGuests::STATUS_QUARANTINE],
                ['status' => ShelterGuests::STATUS_QUARANTINE_OTHER],
            ],
        ]);

        foreach($dumpQuery as $d){
            $pet = ShelterGuests::findOne($d['id']);
            if($d['status'] != $pet['status']){
                if($pet['status']==ShelterGuests::STATUS_QUARANTINE){
                    PetHistory::addRecord(['id_pet' => $pet['id_pet'], 'event' => PetHistory::HISTORY_EVENT_QUARANTINE]);
                }else if($pet['status']==ShelterGuests::STATUS_QUARANTINE_OTHER){
                    PetHistory::addRecord(['id_pet' => $pet['id_pet'], 'event' => PetHistory::HISTORY_EVENT_QUARANTINE_OTHER]);
                }else if($pet['status']==ShelterGuests::STATUS_IN_SHELTER){
                    PetHistory::addRecord(['id_pet' => $pet['id_pet'], 'event' => PetHistory::HISTORY_EVENT_QUARANTINE_END]);
                    PetHistory::addRecord(['id_pet' => $pet['id_pet'], 'event' => PetHistory::HISTORY_EVENT_MAINTENANCE]);
                }                
            }
        }

        $searchModel = new ShelterSearchModel();

        return $searchModel->list($page, $limit, $filter, $sortBy, $sortDesc);
    }

    /**
     * @param string $arrival_date
     * @param int $id_species
     * @param int|null $id_breed
     * @param string|null $sex
     * @param string|null $color
     * @param string|null $characteristics
     * @param string|null $arrival_comment
     * @param array|null $identifications
     * @param int|null $id_pet
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAdd(
        int    $id_organization,
        int    $id_species,
        string $birthday,
        int    $id_breed = null,
        string $sex = null,
        int    $color = null,
        string $characteristics = null,
        int    $id_pet = null,
        bool   $is_quarantine = null,
        string $quarantine_from = null,
        string $quarantine_to = null,
        string $arrival_reason = null,
        string $arrival_act_number = null,
        string $arrival_act_number_date = null,
        string $arrival_work_order = null,
        string $arrival_work_order_date = null,
        string $catching_act_number = null,
        string $catching_act_date = null,
        string $catching_address = null,
        int    $is_catching_video = null,
        string $catching_video = null,
        int    $shelter_chip = null,
        string $shelter_label = null,
        string $name = null,
        int    $size_id = null,
        int    $wool = null,
        int    $tails = null,
        int    $ears = null,
        string $character = null,
        array  $pet_photo = null,
        array  $pet_document = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShelterGuestModel();
        $result = $model->findOrCreatePetV2(date("Y-m-d"), $id_organization, $id_species, $birthday, $id_breed, $sex, $color, $characteristics, $id_pet, $is_quarantine, $quarantine_from,
            $quarantine_to, $arrival_reason, $arrival_act_number, $arrival_act_number_date, $arrival_work_order, $arrival_work_order_date, $catching_act_number,
            $catching_act_date, $catching_address, $is_catching_video, $catching_video, $shelter_chip, $shelter_label, $name, $size_id, $wool, $tails, $ears,
            $character, $pet_photo, $pet_document);

        return ($result === false)
            ? $this->errorResponse($model)
            : [
                'result' => $result,
            ];
    }

    /**
     * Возвращает информацию по состоянию здоровья животного
     *
     * @param $id_pet
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionHealth(int $id_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ShelterGuestModel())->getPetHealth($id_pet),
        ];
    }

    /**
     * Сохранение информации о здоровье животного
     *
     * @param $id_pet
     * @param $healths
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionHealthSave($id_pet, $healths)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ShelterGuestModel())->saveHealth($id_pet, $healths);

        return [
            'result' => (new ShelterGuestModel())->getPetHealth($id_pet),
        ];
    }

    /**
     * @param int    $id_pet
     * @param int    $id_ident_type
     * @param string $identification_code
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAddIdentification(
        int $id_pet,
        int $id_ident_type,
        string $identification_code
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShelterGuestModel();
        $result = $model->actionAddIdentification($id_pet, $id_ident_type, $identification_code);

        return ($result === false)
            ? $this->errorResponse($model)
            : [
                'result' => $result,
            ];
    }

    /**
     * @param int         $id_pet
     * @param int         $id_species
     * @param int|null    $id_breed
     * @param string|null $sex
     * @param string|null $color
     * @param string|null $characteristics
     * @param array|null  $identifications
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEditPet(
        int $id_pet,
        int $id_species,
        int $id_breed = null,
        string $sex = null,
        string $color = null,
        string $characteristics = null,
        array $identifications = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShelterGuestModel();
        $result = $model->updatePet($id_pet, $id_species, $id_breed, $sex, $color, $characteristics, $identifications);

        return ($result === false)
            ? $this->errorResponse($model)
            : [
                'result' => $result,
            ];
    }

    /**
     * @param int $id_record
     * @param int $id_pet
     * @param string $arrival_date
     * @param string|null $arrival_comment
     * @param string|null $departure_date
     * @param string|null $departure_comment
     * @param string|null $status
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEditRecord(
        int    $id_record,
        int    $id_pet,
        string $arrival_date,
        string $arrival_comment = null,
        string $departure_date = null,
        string $departure_comment = null,
        string $status = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShelterGuestModel();
        $result = $model->updateRecord($id_record, $id_pet, $arrival_date, $arrival_comment, $departure_date, $departure_comment, $status);

        return ($result === false)
            ? $this->errorResponse($model)
            : [
                'result' => $result,
            ];
    }

    public function actionUpdate()
    {
        if ($data = json_decode(Yii::$app->request->getRawBody(), true)['data']) {
            if (($model = Pets::findOne($data['id'])) && $model->setAttributes($data)) {

                PetToSkills::deleteAll(['id_pet' => $data['id']]);
                if (isset($data['skills_ids']) && $data['skills_ids'])
                {
                    $skills = PetRefSkill::findAll(['id' => $data['skills_ids']]);

                    foreach ($skills as $skill)
                        $model->link('skills', $skill);
                }


                foreach ((array)$data['photos'] as $key => $file_id) {
                    if (is_array($file_id)) {
                        $file_id = $key;
                    }
                    if ($file = Files::find()->where(['id' => $file_id])->one()) {
                        if ($data['photos'] && array_key_exists('selected', $data['photos'][$file_id]) && $data['photos'][$file_id]['selected']) {
                            $file->setAttributes([
                                'entity_id' => $data['id'],
                                'entity_type' => 'shelter_main',
                            ]);
                        } else {
                            $file->setAttributes([
                            'entity_id' => $data['id'],
                            'entity_type' => 'shelter',
                        ]);
                        }
                        $file->save();
                    }
                }

                Files::deleteAll([
                    'AND',
                    ['entity_id' => $data['id'], 'entity_type' => ['pets', 'shelter', 'shelter_main',],],
                    ['NOT', ['id' => array_keys((array)$data['photos'])]],
                ]);

                if ($model->save()) {
                    if (!$identification = PetIdentification::findOne(['id_pet' => $data['id']])) {
                        $identification = new PetIdentification(['id_pet' => $data['id']]);
                    }

                    if ($data['identification_1']) {
                        $identification->setAttributes([
                            'id_ident_type' => 1,
                            'identification_code' => $data['identification_1'],
                        ]);
                    } else {
                        $identification->setAttributes([
                            'id_ident_type' => 7,
                            'identification_code' => $data['identification_2'],
                        ]);
                    }

                    $identification->save();

                    if ($shelterGuests = ShelterGuests::findOne(['id_pet' => $data['id']])) {
                        $shelterGuests->updateAttributes([
                            'socialized' => (bool)$data['socialized'],
                        ]);
                    }

                    return 'success';
                }
            }
        }

        throw new BadRequestHttpException('Shit happens');
    }

    public function actionHistory($pet_id)
    {
        $models = PetHistory::find()
            ->where(['id_pet' => $pet_id])
            ->with('organization')
            ->with('creater')
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();

            

        if ($models) {
            foreach ($models as &$model) {
                $model['event_title'] = PetHistory::HISTORY_EVENTS[$model['event']] ?? $model['event'];

                if ($model['event'] === PetHistory::HISTORY_EVENT_AVIARY && $aviary = Aviary::findOne((int)$model['options'])) {
                    $model['options'] = $aviary->title;
                }else if ($model['event'] === PetHistory::HISTORY_EVENT_SHELTER && $organization = Organizations::findOne((int)$model['options'])) {
                    $model['options'] = $organization->short_name;
                }else if ($model['event'] === PetHistory::HISTORY_EVENT_DEPARTURE) {
                     $record = ShelterGuests::findOne(['id_pet' => (int)$model['id_pet']]);
                     if($record->departure_reason){
                        if($record->departure_reason == 'ESCAPE'){
                            $options_title = 'Побег';
                        }else if($record->departure_reason == 'DEATH'){
                            $options_title = 'Падёж';
                        }else if($record->departure_reason == 'EUTHANASIA'){
                            $options_title = 'Эвтаназия';
                        }else if($record->departure_reason == 'RETURNED_TO_NEW_OWNER'){
                            $options_title = 'Передача новому владельцу';
                        }else if($record->departure_reason == 'RETURNED_TO_OWNER'){
                            $petowner = PetOwners::findOne(['id' => (int)$record['id_owner']]);
    
                            if($petowner->fullname){
                                $options_title = 'Возврат прежнему владельцу '.$petowner->fullname;
                            }
                        }
                        if($record->departure_comment){
                            $options_title=$options_title." (".$record->departure_comment.")";
                        }
                         $model['options']=$options_title;
                     }
                    
                }else if ($model['event'] === PetHistory::HISTORY_EVENT_DEREGISTERED) {
                    $record = Pets::findOne(['id' => (int)$model['id_pet']]);
                    $reason = RegExpireReasons::findOne(['id' => (int)$record->id_reg_expire_reason]);
                    $model['options'] = $reason->name;
                }else{
                    $model['options'] = '';
                }
            }
        }

        return $models ?? [];
    }

    public function actionUpdateMovement()
    {
        if ($data = json_decode(Yii::$app->request->getRawBody(), true)['data']) {
            $data['is_catching_video'] = isset($data['is_catching_video']) && $data['is_catching_video'] === 'true';
            // file_put_contents('debug6.txt',$data['id'], FILE_APPEND);
            if (($model = ShelterGuests::findOne($data['id']))) {
                $old_aviary_id = $model->aviary_id;
                $model->setAttributes($data);
                $file_ids = [];
                $latest_docs = [];
                foreach ((array)$data['files'] as $file_data) {
                    if (isset($file_data['id'])) {
                        if ($file = Files::find()->where(['id' => $file_data['id'], 'entity_id' => null])->one()) {
                            $file->setAttributes([
                                'entity_id' => $data['id'],
                                'entity_type' => 'shelter_guests',
                            ]);
                            if ($file->save()) {
                                $file_ids[] = $file->id;
                            }
                        } else {
                            $file_ids[] = $file_data['id'];
                        };
                        if (!$docModel = Documents::find()->where(['file_id' => $file_data['id']])->one()) {
                            $docModel = new Documents();
                            $docModel->created_by = Yii::$app->user->id;
                            $docModel->created_date = Date("Y-m-d H:i:s");
                            $add_history_record = true;
                        }
                        $docModel->file_id = $file_data['id'];
                        $docModel->type_id = intval($file_data['document_type']);
                        $docModel->number = $file_data['document_number'];
                        $docModel->date = $file_data['document_date'];
                        $docModel->name = $file_data['full_name'];

                        if (!$docModel->save()) {
                            // исключает возможность сохранения модели, если какой-то документ не сохранен
                            // работает через Ж, поэтому отключил
                            // $model->addErrors($docModel->getErrors());
                        }

                        if ($add_history_record ?? false) {
                            PetHistory::addRecord([
                                'id_pet' => $model->id_pet,
                                'event' => 'Добавлен документ: ' . $docModel->name,
                            ]);
                        }

                        // !@#)%#%$#$%-костыль №1. Сначала определяем актуальный документ по типам
                        $document_type = $docModel->documentType->type;
                        if (!isset($latest_docs[$document_type])
                            || strtotime($docModel->date) >= strtotime($latest_docs[$document_type]->date)
                        ) {
                            $latest_docs[$document_type] = $docModel;
                        }
                    }
                }

                if ($file_ids) {
                    $documents_to_delete = Documents::find()->where(['NOT', ['file_id' => $file_ids]])->all();

                    // foreach ($documents_to_delete as $document) {
                    //     PetHistory::addRecord([
                    //         'id_pet' => $model->id_pet,
                    //         'event' => 'Удален документ: ' . $document->name,
                    //     ]);
                    // }

                    Files::deleteAll([
                        'AND',
                        ['entity_id' => $data['id'], 'entity_type' => 'shelter_guests',],
                        ['NOT', ['id' => $file_ids]],
                    ]);
                }

                // !@#)%#%$#$%-костыль №2. Потом определяем актуальный документ по типам в группе
                foreach ($latest_docs as $document_type => $document) {
                    if (in_array($document_type, DocumentTypes::DOCUMENTS_BY_GROUPS[DocumentTypes::GROUP_ACTS_OF_ARRIVE])) {
                        if (strtotime($model->arrival_act_number_date) <= strtotime($document->date)) {
                            $model->arrival_act_number_date = $document->date;
                            $model->arrival_act_number = $document->number;
                        }
                    } else if (in_array($document_type, DocumentTypes::DOCUMENTS_BY_GROUPS[DocumentTypes::GROUP_WORK_ORDERS])) {
                        if (strtotime($model->arrival_work_order_date) <= strtotime($document->date)) {
                            $model->arrival_work_order_date = $document->date;
                            $model->arrival_work_order = $document->number;
                        }
                    } else if (in_array($document_type, DocumentTypes::DOCUMENTS_BY_GROUPS[DocumentTypes::GROUP_CATCHING])) {
                        if (strtotime($model->catching_act_date) <= strtotime($document->date)) {
                            $model->catching_act_date = $document->date;
                            $model->catching_act_number = $document->number;
                        }
                    }
                }

                if ($model->save()) {
                    if (is_numeric($model->aviary_id)
                        && $old_aviary_id !== (int)$model->aviary_id
                        && $aviary = Aviary::findOne($model->aviary_id)
                    ) {
                        PetHistory::addRecord([
                            'id_pet' => $model->id_pet,
                            'event' => PetHistory::HISTORY_EVENT_AVIARY,
                            'options' => $aviary->id,
                        ]);
                    }

                    return $model;
                }
            }
        }

        throw new BadRequestHttpException('Shit happens');
    }

    public function actionUpdateCastrated()
    {
        if ($data = json_decode(Yii::$app->request->getRawBody(), true)['data']) {
            if (($model = Pets::findOne($data['id'])) && $model->setAttributes($data)) {

                if ($model->save()) {
                    if($model->castrated === true){
                        if(PetHistory::findOne(['id_pet' => $model->id,'event' => 'CASTRATED'])){

                        }else{
                            PetHistory::addRecord(['id_pet' => $data['id'],'event' => PetHistory::HISTORY_EVENT_CASTRATED]);
                        }
                    }
                    
                    return 'success';
                }
            }
        }

        throw new BadRequestHttpException('Shit happens');
    }

    /**
     * @return array
     */
    public function actionDepartureReasons()
    {
        $reasons = [];
        $options = ShelterGuests::DEPARTURE_REASONS;
        foreach ($options as $key => $label) {
            $reasons[] = [
                'id' => $key,
                'name' => $label,
            ];
        }

        return [
            'result' => $reasons,
        ];
    }

    /**
     * @return array
     */
    public function actionDocumentTypes()
    {
        $reasons = [];
        $options = DocumentTypes::TYPES;
        foreach ($options as $key => $label) {
            $reasons[] = [
                'id' => $key,
                'name' => $label,
            ];
        }

        return [
            'result' => $reasons,
        ];
    }

    public function actionChipExists($chip_id)
    {
        // $model = PetIdentification::findOne([
        //     'id_ident_type' => PetIdentification::IDENT_TYPE_CHIP,
        //     'identification_code' => $chip_id,
        // ]);

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("
        SELECT
					pi.id_pet,
					pi.identification_code,
                    p.birthday,
                    p.name,
                    p.sex,
                    p.id_species,
                    s.name as name_species,
                    p.id_breed,
                    b.name as name_breed,
                    p.color,
                    p.characteristics,
                    p.description,
                    p.id_brood,
                    p.size_id,
                    p.color_id,
                    p.character,
                    p.ear_type_id,
                    p.tail_type_id,
                    p.wool_type_id,
                    coalesce(sg.id, '0') as shelter_table_id,
					case 
					when sg.status is null then '0'
					when sg.status = 'IN_SHELTER' OR sg.status = 'QUARANTINE' OR sg.status = 'QUARANTINE_OTHER' then '1'
					when sg.status = 'DEPARTURED' AND sg.departure_reason<>'EUTHANASIA' AND sg.departure_reason<>'DEATH' then '2'
					when sg.status = 'DEPARTURED' AND (sg.departure_reason='EUTHANASIA' OR sg.departure_reason='DEATH') then '3'
					else '-1' end as shelter_status,
                    sg.id_organization,
                    org.name as org_name
					
                        FROM public.pet_identification pi
                        LEFT JOIN pets p ON pi.id_pet=p.id
                        LEFT JOIN shelter_guests sg ON sg.id_pet=pi.id_pet
                        LEFT JOIN organizations org ON org.id=sg.id_organization
                        LEFT JOIN species s ON p.id_species=s.id
                        LEFT JOIN breeds b ON p.id_breed=b.id
                        WHERE identification_code='".$chip_id."' AND id_ident_type=1
						ORDER BY sg.id DESC LIMIT 1");
        $fQuery = $command->queryAll();

        foreach($fQuery as $f){
            return $f;
        }
        return null;

        //return $model->id_pet ?? null;
    }

    /**
     * @param int $id_pet
     * @param string $departure_date
     * @param string $status
     * @param string|null $departure_comment
     * @param int|null $id_owner
     * @param array|null $departure_documents
     * @return array|void
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDeparture(
        int    $id_pet,
        string $departure_date,
        string $status = null,
        string $departure_comment = null,
        int    $departure_specialist = null,
        int    $id_owner = null,
        array  $departure_documents = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShelterGuestModel();
        if (($result = $model->departurePet($id_pet, $departure_date, $status, $departure_comment, $departure_specialist, $id_owner, $departure_documents))
            && isset($result['record'])
        ) {
            $pet_history_model = new PetHistory();
            $pet_history_model->id_pet = $result['record']['id_pet'];
            $pet_history_model->event = PetHistory::HISTORY_EVENT_DEPARTURE;
            $pet_history_model->options = '';
            $pet_history_model->id_organization = $result['record']['id_organization'];
            $pet_history_model->created_by = Yii::$app->user->id;
            $pet_history_model->created_at = Date("Y-m-d H:i:s");

            if (!$pet_history_model->save()) {
                // return $pet_history_model->getErrors();
            }

            PetHealth::deleteAll(['AND',
                ['id_pet' => $id_pet],
                ['>=', 'date', date('Y-m-d', strtotime($departure_date))]
            ]);
        }

        return ($result === false)
            ? $this->errorResponse($model)
            : [
                'result' => $result,
            ];
    }

    /**
     * @param string $code
     * @param int $id_pet
     * @return null|FileResource
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionReport(
        string $code,
        int    $id_pet
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShelterGuestModel();
        switch ($code) {
            case ShelterGuests::EXPORT_REPORT_ANIMAL_CARD:
                $result = $model->createAnimalCard($id_pet);
                break;
            case ShelterGuests::EXPORT_REPORT_ANKETA:
                $generator = Yii::$app->get('wordGenerator');
                $result = $generator->createDocument('anketa', 'Анкета желающего взять животное из приюта', [], $id_pet);
                break;
            case ShelterGuests::EXPORT_REPORT_TRANSFER:
                $result = $model->createTransferContract($id_pet);
                break;
            case ShelterGuests::EXPORT_REPORT_RETURN:
                $result = $model->createReturnAct($id_pet);
                break;
            case ShelterGuests::EXPORT_REPORT_DEATH:
                $result = $model->createDeathAct($id_pet);
                break;
            default:
                throw new Exception("Отчет с кодом " . $code . " не реализован");
        }


        if ($result ?? false) {
            return $result;
        }

        throw new NotFoundHttpException();;
    }

    /**
     * @return array
     */
    public function actionExportReports()
    {
        $reasons = [];
        $options = ShelterGuests::EXPORT_REPORTS;
        foreach ($options as $key => $label) {
            $reasons[] = [
                'id' => $key,
                'name' => $label,
            ];
        }

        return [
            'result' => $reasons,
        ];
    }

    public function actionReturnToShelter()
    {
        /* @var ShelterGuests $model */
        if (($user = Yii::$app->user->getIdentity())
            && ($user->specialist->id_organization ?? false)
            && ($id = Yii::$app->request->post('id_record'))
            && $model = $this->findModel($id)
        ) {
            $model->updateAttributes([
                'departure_date' => null,
                'departure_comment' => null,
                'departure_reason' => null,
                'status' => ShelterGuests::STATUS_QUARANTINE,
                'quarantine_from' => date('Y-m-d'),
                'quarantine_to' => date('Y-m-d', strtotime('+9 days')),
                'id_organization' => $user->specialist->id_organization,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $user->getId(),
                'aviary_id' => null,
                'arrival_act_number' => null,
                'arrival_act_number_date' => null,
                'arrival_work_order' => null,
                'arrival_work_order_date' => null,
                'catching_video' => null,
                'is_catching_video' => false,
                'catching_address' => null,
                'catching_act_date' => null,
                'catching_act_number' => null,
            ]);

            // @todo Documents::updateAll(['protected_at' => date('Y-m-d H:i:s')], [''])
            foreach ($model->documents as $document) {
                $document->protected_at = date('Y-m-d H:i:s');
                $document->save();
            }

            foreach ($model->petHealths as $petHealth) {
                $petHealth->protected_at = date('Y-m-d H:i:s');
                $petHealth->save();
            }

            PetHistory::addRecord([
                'id_pet' => $model->id_pet,
                'event' => PetHistory::HISTORY_EVENT_RETURN_PET,
            ]);

            if ($model->addQuarantineDays(true)) {
                return $this->asJson($model->getAttributes());
            }
        }

        throw new InvalidConfigException();
    }

    public function actionUpdateStatus(int $id_pet, string $status)
    {
        $pet = ShelterGuests::findOne(['id_pet' => $id_pet]);
        $pet->setAttribute('status', $status);
        $pet->save();

        return $pet;
    }

    protected function findModel($id)
    {
        if (($model = ShelterGuests::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
