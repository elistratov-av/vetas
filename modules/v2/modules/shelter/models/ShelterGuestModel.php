<?php

namespace app\modules\v2\modules\shelter\models;
use Yii;
use app\common\components\FileService;
use app\common\components\inform\events\AnimalFoundEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\SubscriptionService;
use app\common\components\wordGenerator;
use app\common\models\UserModel;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\Files;
use app\models\db\IdentificationTypes;
use app\models\db\Organizations;
use app\models\db\PetIdentification;
use app\models\db\PetOwnerType;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\PetsToPetRefEarType;
use app\models\db\PetsToPetRefSize;
use app\models\db\PetsToPetRefTailType;
use app\models\db\PetsToPetRefWoolType;
use app\models\db\RegExpireReasons;
use app\models\db\ShelterGuests;
use app\models\db\Species;
use app\models\db\subscription\Subscriptions;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\pets\models\PetToOwnerModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Query;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\common\helpers\DateHelper;
use app\common\components\media\UploadFileRepository;
use yii\helpers\FileHelper;
use DateTime;
use DateInterval;
use app\models\db\PetHealth;
use app\models\db\PetHistory;
use app\models\db\PetDehelmintization;
use app\models\db\PetOtherVaccinations;
use yii\web\BadRequestHttpException;
use app\models\db\Documents;
use app\models\db\DocumentTypes;

/**
 * Class ShelterGuestModel
 * @package app\modules\v2\modules\shelter\models
 */
class ShelterGuestModel extends Model
{
    /**
     * @var int
     */
    private $id_organization;
    /**
     * @var int
     */
    private $id_ident_type_chip;
    /**
     * @var int
     */
    private $id_ident_type_label;
    /**
     * @var int
     */
    private $id_species_dog;
    /**
     * @var int
     */
    private $id_contact_type_org_phone;
    /**
     * @var int
     */
    private $id_contact_type_org_phone_mob;

    private $arr = [
        'января',
        'февраля',
        'марта',
        'апреля',
        'мая',
        'июня',
        'июля',
        'августа',
        'сентября',
        'октября',
        'ноября',
        'декабря'
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        /** @var $user UserModel */
        $user = \Yii::$app->user->getIdentity();
        if ($user->specialist === null || empty($user->specialist->id_organization)) {
            throw new InvalidConfigException();
        }

        $this->id_organization = $user->specialist->id_organization;
        $this->id_ident_type_chip = IdentificationTypes::findIdentificationTypeId('чип');
        $this->id_ident_type_label = IdentificationTypes::findIdentificationTypeId('метка');
        $this->id_species_dog = Species::findOne(['name' => 'собаки']);
        $this->id_contact_type_org_phone = ContactTypes::findOne(['name' => 'Телефон']);
        $this->id_contact_type_org_phone_mob = ContactTypes::findOne(['name' => 'Мобильный телефон']);
    }


    public function findOrCreatePetV2(
        string $arrival_date,
        int    $id_organization,
        int    $id_species,
        string $birthday,
        int    $id_breed = null,
        string $sex = null,
        int    $color_id = null,
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
        array  $photoSets = null,
        array  $documentSets = null,
        string $departure_date = null,
        string $departure_comment = null,
        string $status = null,
        string $departure_reason = null
    )
    {
        $identifications = array();
        if (!empty($shelter_chip)) {
            $identifications[0] = array('id_ident_type' => $this->id_ident_type_chip, 'identification_code' => $shelter_chip);
        }
        if (!empty($shelter_label)) {
            $identifications[1] = array('id_ident_type' => $this->id_ident_type_label, 'identification_code' => $shelter_label);
        }

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
            WHERE identification_code='".$shelter_chip."' AND id_ident_type=1
            ORDER BY sg.id DESC LIMIT 1");
        $fQuery = $command->queryAll(); 
        foreach($fQuery as $f){ //указан чип существующего животного
        
       
        //животное существует, но не было в приюте
        if($f['shelter_status']==0){

            $pet = $this->findPet($f['id_pet']);
            if ($pet === null) {
                $this->addError('id_pet', 'Указанное животное не найдено');
                return false;
            }
            $pet->id_breed = $id_breed;
            $pet->id_species = $id_species;
            $pet->sex = $sex;
            $pet->color_id = $color_id;
            $pet->characteristics = $characteristics;
            $pet->birthday = $birthday;
            $pet->name = $name;
            $pet->character = $character;
            $pet->ear_type_id = $ears;
            $pet->tail_type_id = $tails;
            $pet->wool_type_id = $wool;
            $pet->size_id = $size_id;

            if (!$pet->save()) {
                $this->addErrors($pet->getErrors());
                return false;
            }

            if (!empty($quarantine_from)) {
                $date_from = DateTime::createFromFormat("Y-m-d", $quarantine_from);
                $quarantine_from = $date_from->format("Y-m-d");
            }
    
            if (!empty($quarantine_to)) {
                $date_to = DateTime::createFromFormat("Y-m-d", $quarantine_to);
                $quarantine_to = $date_to->format("Y-m-d");
            }
    
            $record = new ShelterGuests(compact('arrival_date', 'id_organization', 'is_quarantine', 'quarantine_from', 'quarantine_to', 'arrival_reason', 'arrival_act_number', 'arrival_act_number_date',
                'arrival_work_order', 'arrival_work_order_date', 'catching_act_number', 'catching_act_date', 'catching_address', 'is_catching_video', 'catching_video'));
            $record->id_pet = $pet->id;
            if($quarantine_from && $quarantine_to){
                $record->status = ShelterGuests::STATUS_QUARANTINE;
            }else{
                $record->status = ShelterGuests::STATUS_IN_SHELTER;
            }
    
            if (!$record->save()) {
                $this->addErrors($record->getErrors());
                return false;
            }
    
            if (!empty($photoSets)) {
                foreach ($photoSets as $photo) {
                    $photoRecord = Files::findOne(['id' => $photo['id']]);
                    $photoRecord->entity_id = $pet->id;
                    if ($photo['selected']) {
                        $photoRecord->entity_type = 'shelter_main';
                    } else {
                        $photoRecord->entity_type = 'shelter';
                    }
                    if (!$photoRecord->save()) {
                        $this->addErrors($photoRecord->getErrors());
                        return false;
                    }
                }
            }
    
            if (!empty($documentSets)) {
                foreach ($documentSets as $document) {
                    $documentRecord = Files::findOne(['id' => $document['id']]);
                    $documentRecord->entity_id = $record->id;
                    $documentRecord->entity_type = 'shelter_guests';
                    if (!$documentRecord->save()) {
                        $this->addErrors($documentRecord->getErrors());
                        return false;
                    }
    
                    $docModel = new Documents();
                    $docModel->file_id = $documentRecord->id;
                    $docModel->type_id = intval($document['document_type']);
                    $docModel->number = $document['document_number'];
                    $docModel->date = $document['document_date'];
                    $docModel->name = $document['full_name'];
                    $docModel->created_by = \Yii::$app->user->id;
                    $docModel->created_date = Date("Y-m-d H:i:s");
    
                    if (!$docModel->save()) {
                        $this->addErrors($docModel->getErrors());
    
                        return false;
                    }
    
                    PetHistory::addRecord([
                        'id_pet' => $pet->id,
                        'event' => 'Добавлен документ: ' . $docModel->name,
                    ]);
                }
            }

            $this->addHistoryAndHealthInfo($pet, $id_organization, $is_quarantine, $quarantine_from, $quarantine_to);

            /** @var Organizations $organization */
            $organization = Organizations::find()->where(['id' => $this->id_organization])->one();
            if ($representative = $organization->representative) {
                $link = PetsToOwner::find()->where(['id_pet' => $pet->id, 'id_owner' => $representative->id])->one();
                if (!$link) {
                    $ownerType = PetOwnerType::find()->where(['is_owner' => false])->one();
                    (new PetToOwnerModel())->create($pet->id, $representative->id, $ownerType->id);
                }
            }
    
            return [
                'record' => $record->toArray(),
                'pet' => $this->preparePetOutput($pet),
            ];
        }
        //животное было в приюте, меняем приют
        else if($f['shelter_status']==2){
            $record = ShelterGuests::findOne([
                'id' => $f['shelter_table_id'],
                'id_pet' => $f['id_pet'],
            ]);
            if ($record === null) {
                $this->addError('id_record', 'Запись о нахождении животного в приюте не найдена');
                return false;
            }

            $pet = $this->findPet($f['id_pet']);
            if ($pet === null) {
                $this->addError('id_pet', 'Указанное животное не найдено');
                return false;
            }
            $pet->id_breed = $id_breed;
            $pet->id_species = $id_species;
            $pet->sex = $sex;
            $pet->color_id = $color_id;
            $pet->characteristics = $characteristics;
            $pet->birthday = $birthday;
            $pet->name = $name;
            $pet->character = $character;
            $pet->ear_type_id = $ears;
            $pet->tail_type_id = $tails;
            $pet->wool_type_id = $wool;
            $pet->size_id = $size_id;

            if (!$pet->save()) {
                $this->addErrors($pet->getErrors());
                return false;
            }

            if (!empty($quarantine_from)) {
                $date_from = DateTime::createFromFormat("Y-m-d", $quarantine_from);
                $quarantine_from = $date_from->format("Y-m-d");
            }

            if (!empty($quarantine_to)) {
                $date_to = DateTime::createFromFormat("Y-m-d", $quarantine_to);
                $quarantine_to = $date_to->format("Y-m-d");
            }
    
            $record->status = ShelterGuests::STATUS_QUARANTINE;

            $record->setAttributes(compact('arrival_date', 'id_organization', 'is_quarantine', 'quarantine_from', 'quarantine_to', 'arrival_reason', 'arrival_act_number', 'arrival_act_number_date',
            'arrival_work_order', 'arrival_work_order_date', 'catching_act_number', 'catching_act_date', 'catching_address', 'is_catching_video', 'catching_video','departure_date', 'departure_comment', 'departure_reason'));
            if (count($record->getDirtyAttributes()) > 0) {
                if (!$record->save()) {
                    $this->addErrors($record->getErrors());
                    return false;
                }
            }

            
        //перезатираем фото, если фотографии добавлены
        //TODO!

        //добавляем документы к существующим

        if (!empty($documentSets)) {
            foreach ($documentSets as $document) {
                $documentRecord = Files::findOne(['id' => $document['id']]);
                
                $documentRecord->entity_id = $record->id;
                $documentRecord->entity_type = 'shelter_guests';
                if (!$documentRecord->save()) {
                    $this->addErrors($documentRecord->getErrors());
                    return false;
                }

                $docModel = new Documents();
                $docModel->file_id = $documentRecord->id;
                $docModel->type_id = intval($document['document_type']);
                $docModel->number = $document['document_number'];
                $docModel->date = $document['document_date'];
                $docModel->name = $document['full_name'];
                $docModel->created_by = \Yii::$app->user->id;
                $docModel->created_date = Date("Y-m-d H:i:s");

                if (!$docModel->save()) {
                    $this->addErrors($docModel->getErrors());

                    return false;
                }

                PetHistory::addRecord([
                    'id_pet' => $pet->id,
                    'event' => 'Добавлен документ: ' . $docModel->name,
                ]);
            }
        }

        

        //добавляем историю
        $this->addHistoryAndHealthInfo($pet, $id_organization, $is_quarantine, $quarantine_from, $quarantine_to, true);

        /** @var Organizations $organization */
        $organization = Organizations::find()->where(['id' => $this->id_organization])->one();
        if ($representative = $organization->representative) {
            $link = PetsToOwner::find()->where(['id_pet' => $pet->id, 'id_owner' => $representative->id])->one();
            if (!$link) {
                $ownerType = PetOwnerType::find()->where(['is_owner' => false])->one();
                (new PetToOwnerModel())->create($pet->id, $representative->id, $ownerType->id);
            }
        }
        return [
            'record' => $record->toArray(),
            'pet' => $this->preparePetOutput($pet),
        ];

        }
        
        
        return;
        }
        //чип животного не указан или указан новый чип

            //создаем новое животное
            $ear_type_id = $ears;
            $wool_type_id = $wool;
            $tail_type_id = $tails;
            $pet = $this->createPet(compact('id_breed', 'id_species', 'sex', 'color_id', 'characteristics', 'birthday', 'name', 'character', 'size_id', 'ear_type_id', 'tail_type_id', 'wool_type_id'));
            if ($pet === false) {
                return false;
            }

            //если указан чип или метка у несуществующего животного создаем
            if (!empty($identifications)) {
                $this->saveIdentifications($identifications, $pet->id);
            }

            //добавляем животное в приют
            if (!empty($quarantine_from)) {
                $date_from = DateTime::createFromFormat("Y-m-d", $quarantine_from);
                $quarantine_from = $date_from->format("Y-m-d");
            }

            if (!empty($quarantine_to)) {
                $date_to = DateTime::createFromFormat("Y-m-d", $quarantine_to);
                $quarantine_to = $date_to->format("Y-m-d");
            }


            $record = new ShelterGuests(compact('arrival_date', 'id_organization', 'is_quarantine', 'quarantine_from', 'quarantine_to', 'arrival_reason', 'arrival_act_number', 'arrival_act_number_date',
                'arrival_work_order', 'arrival_work_order_date', 'catching_act_number', 'catching_act_date', 'catching_address', 'is_catching_video', 'catching_video'));
            $record->id_pet = $pet->id;
            if($quarantine_from && $quarantine_to){
                $record->status = ShelterGuests::STATUS_QUARANTINE;
            }else{
                $record->status = ShelterGuests::STATUS_IN_SHELTER;
            }

            if (!$record->save()) {
                $this->addErrors($record->getErrors());
                return false;
            }

            if (!empty($photoSets)) {
                foreach ($photoSets as $photo) {
                    $photoRecord = Files::findOne(['id' => $photo['id']]);
                    $photoRecord->entity_id = $pet->id;
                    if ($photo['selected']) {
                        $photoRecord->entity_type = 'shelter_main';
                    } else {
                        $photoRecord->entity_type = 'shelter';
                    }
                    if (!$photoRecord->save()) {
                        $this->addErrors($photoRecord->getErrors());
                        return false;
                    }
                }
            }

            if (!empty($documentSets)) {
                foreach ($documentSets as $document) {
                    $documentRecord = Files::findOne(['id' => $document['id']]);
                    $documentRecord->entity_id = $record->id;
                    $documentRecord->entity_type = 'shelter_guests';
                    if (!$documentRecord->save()) {
                        $this->addErrors($documentRecord->getErrors());
                        return false;
                    }

                    $docModel = new Documents();
                    $docModel->file_id = $documentRecord->id;
                    $docModel->type_id = intval($document['document_type']);
                    $docModel->number = $document['document_number'];
                    $docModel->date = $document['document_date'];
                    $docModel->name = $document['full_name'];
                    $docModel->created_by = \Yii::$app->user->id;
                    $docModel->created_date = Date("Y-m-d H:i:s");

                    if (!$docModel->save()) {
                        $this->addErrors($docModel->getErrors());

                        return false;
                    }

                    PetHistory::addRecord([
                        'id_pet' => $pet->id,
                        'event' => 'Добавлен документ: ' . $docModel->name,
                    ]);
                }
            }

            $this->addHistoryAndHealthInfo($pet, $id_organization, $is_quarantine, $quarantine_from, $quarantine_to);

            /** @var Organizations $organization */
            $organization = Organizations::find()->where(['id' => $this->id_organization])->one();
            if ($representative = $organization->representative) {
                $link = PetsToOwner::find()->where(['id_pet' => $pet->id, 'id_owner' => $representative->id])->one();
                if (!$link) {
                    $ownerType = PetOwnerType::find()->where(['is_owner' => false])->one();
                    (new PetToOwnerModel())->create($pet->id, $representative->id, $ownerType->id);
                }
            }

            return [
                'record' => $record->toArray(),
                'pet' => $this->preparePetOutput($pet),
            ];
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
     * @return array|false
     */
    public function findOrCreatePet(
        string $arrival_date,
        int    $id_organization,
        int    $id_species,
        string $birthday,
        int    $id_breed = null,
        string $sex = null,
        int    $color_id = null,
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
        array  $photoSets = null,
        array  $documentSets = null
    )
    {
        $identifications = array();
        if (!empty($shelter_chip)) {
            $identifications[0] = array('id_ident_type' => $this->id_ident_type_chip, 'identification_code' => $shelter_chip);
        }
        if (!empty($shelter_label)) {
            $identifications[1] = array('id_ident_type' => $this->id_ident_type_label, 'identification_code' => $shelter_label);
        }

        if (empty($id_pet)) {
            // это первый запрос - если передан номер чипа, ищем животных,
            // если номер чипа не передан или животных не было найдено - создаем новое животное
            if (!empty($shelter_chip)) {
                // возвращаем список найденных по номеру чипа животных
                $searchModel = new ShelterSearchModel();
                $pets = $searchModel->preparePetsQuery(['identification_code' => $shelter_chip])
                    ->asArray()
                    ->all();

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
                        WHERE identification_code='".$shelter_chip."' AND id_ident_type=1
						ORDER BY sg.id DESC LIMIT 1");
                    $fQuery = $command->queryAll();    

                if (!empty($pets)) {
                    foreach ($pets as &$pet) {
                        $pet['can_edit'] = ($pet['id_created_organization'] == $this->id_organization);
                    }

                    return [
                        'found' => true,
                        'pets' => $fQuery,
                    ];
                }
            }
            $ear_type_id = $ears;
            $wool_type_id = $wool;
            $tail_type_id = $tails;
            $pet = $this->createPet(compact('id_breed', 'id_species', 'sex', 'color_id', 'characteristics', 'birthday', 'name', 'character', 'size_id', 'ear_type_id', 'tail_type_id', 'wool_type_id'));
            if ($pet === false) {
                return false;
            }

            if (!empty($identifications)) {
                $this->saveIdentifications($identifications, $pet->id);
            }
        } else {
            // это второй запрос, животные были найдены и пользователь выбрал существующее животное
            if (empty($shelter_chip)) {
                $this->addError('id_pet', 'Не передан номер чипа');

                return false;
            }
            $pet = $this->findPet($id_pet);
            if ($pet === null) {
                $this->addError('id_pet', 'Указанное животное не найдено');

                return false;
            }
            $pet->id_breed = $id_breed;
            $pet->id_species = $id_species;
            $pet->sex = $sex;
            $pet->color_id = $color_id;
            $pet->characteristics = $characteristics;
            $pet->birthday = $birthday;
            $pet->name = $name;
            $pet->character = $character;
            $pet->ear_type_id = $ears;
            $pet->tail_type_id = $tails;
            $pet->wool_type_id = $wool;
            $pet->size_id = $size_id;

            if (!$pet->save()) {
                $this->addErrors($pet->getErrors());
                return false;
            }

            $chip = $this->findChip($shelter_chip);
            if ($chip === null || $chip->id_pet != $pet->id) {
                $this->addError('id_pet', 'Передан некорректный номер чипа');

                return false;
            }
        }

        if (!empty($quarantine_from)) {
            $date_from = DateTime::createFromFormat("Y-m-d", $quarantine_from);
            $quarantine_from = $date_from->format("Y-m-d");
        }

        if (!empty($quarantine_to)) {
            $date_to = DateTime::createFromFormat("Y-m-d", $quarantine_to);
            $quarantine_to = $date_to->format("Y-m-d");
        }


        $record = new ShelterGuests(compact('arrival_date', 'id_organization', 'is_quarantine', 'quarantine_from', 'quarantine_to', 'arrival_reason', 'arrival_act_number', 'arrival_act_number_date',
            'arrival_work_order', 'arrival_work_order_date', 'catching_act_number', 'catching_act_date', 'catching_address', 'is_catching_video', 'catching_video'));
        $record->id_pet = $pet->id;
        if($quarantine_from && $quarantine_to){
            $record->status = ShelterGuests::STATUS_QUARANTINE;
        }else{
            $record->status = ShelterGuests::STATUS_IN_SHELTER;
        }
        #$record->status = ShelterGuests::STATUS_IN_SHELTER;

        if (!$record->save()) {
            $this->addErrors($record->getErrors());
            return false;
        }

        if (!empty($photoSets)) {
            foreach ($photoSets as $photo) {
                $photoRecord = Files::findOne(['id' => $photo['id']]);
                $photoRecord->entity_id = $pet->id;
                if ($photo['selected']) {
                    $photoRecord->entity_type = 'shelter_main';
                } else {
                    $photoRecord->entity_type = 'shelter';
                }
                if (!$photoRecord->save()) {
                    $this->addErrors($photoRecord->getErrors());
                    return false;
                }
            }
        }

        if (!empty($documentSets)) {
            foreach ($documentSets as $document) {
                $documentRecord = Files::findOne(['id' => $document['id']]);
                $documentRecord->entity_id = $record->id;
                $documentRecord->entity_type = 'shelter_guests';
                if (!$documentRecord->save()) {
                    $this->addErrors($documentRecord->getErrors());
                    return false;
                }

                $docModel = new Documents();
                $docModel->file_id = $documentRecord->id;
                $docModel->type_id = intval($document['document_type']);
                // $docModel->status = $document['document_status'];
                $docModel->number = $document['document_number'];
                $docModel->date = $document['document_date'];
                $docModel->name = $document['full_name'];
                $docModel->created_by = \Yii::$app->user->id;
                $docModel->created_date = Date("Y-m-d H:i:s");

                if (!$docModel->save()) {
                    $this->addErrors($docModel->getErrors());

                    return false;
                }

                PetHistory::addRecord([
                    'id_pet' => $pet->id,
                    'event' => 'Добавлен документ: ' . $docModel->name,
                ]);
            }
        }

        if (!empty($identification_code_chip)) {
            $this->notifyOwner($pet, $identification_code_chip);
        }

        $this->addHistoryAndHealthInfo($pet, $id_organization, $is_quarantine, $quarantine_from, $quarantine_to);

        /** @var Organizations $organization */
        $organization = Organizations::find()->where(['id' => $this->id_organization])->one();
        if ($representative = $organization->representative) {
            $link = PetsToOwner::find()->where(['id_pet' => $pet->id, 'id_owner' => $representative->id])->one();
            if (!$link) {
                $ownerType = PetOwnerType::find()->where(['is_owner' => false])->one();
                (new PetToOwnerModel())->create($pet->id, $representative->id, $ownerType->id);
            }
        }

        return [
            'record' => $record->toArray(),
            'pet' => $this->preparePetOutput($pet),
        ];
    }

    private function addHistoryAndHealthInfo(
        Pets   $pet,
        int    $id_organization,
        bool   $is_quarantine,
        string $quarantine_from = null,
        string $quarantine_to = null,
        bool $notTheFirstTime = false)
    {
        $regModel = new PetHistory();
        $regModel->id_pet = $pet->id;
        if($notTheFirstTime==false){
            $regModel->event = PetHistory::HISTORY_EVENT_REGISTERED;
            $regModel->id_organization = $id_organization;
            $regModel->created_by = \Yii::$app->user->id;
            $regModel->created_at = Date("Y-m-d H:i:s");

            if (!$regModel->save()) {
                $this->addErrors($regModel->getErrors());
                return false;
            }
        }

        $shelterModel = new PetHistory();
        $shelterModel->id_pet = $pet->id;
        $shelterModel->event = PetHistory::HISTORY_EVENT_SHELTER;
        $shelterModel->options = $id_organization;
        $shelterModel->id_organization = $id_organization;
        $shelterModel->created_by = \Yii::$app->user->id;
        $shelterModel->created_at = Date("Y-m-d H:i:s");

        if (!$shelterModel->save()) {
            $this->addErrors($shelterModel->getErrors());
            return false;
        }

        if ($is_quarantine && !empty($quarantine_from) && !empty($quarantine_to)) {

            $quarantineModel = new PetHistory();
            $quarantineModel->id_pet = $pet->id;
            $quarantineModel->event = PetHistory::HISTORY_EVENT_QUARANTINE;
            $quarantineModel->id_organization = $id_organization;
            $quarantineModel->created_by = \Yii::$app->user->id;
            $quarantineModel->created_at = Date("Y-m-d H:i:s");

            if (!$quarantineModel->save()) {
                $this->addErrors($quarantineModel->getErrors());
                return false;
            }

            $temp_date = DateTime::createFromFormat("Y-m-d", $quarantine_from);
            $end_date = DateTime::createFromFormat("Y-m-d", $quarantine_to);
            while ($temp_date <= $end_date) {
                $healthModel = new PetHealth();
                $healthModel->id_pet = $pet->id;
                $healthModel->status = PetHealth::HEALTH_STATUS_QUARANTINE;
                $healthModel->date = $temp_date->format("Y-m-d");
                $healthModel->id_organization = $id_organization;
                $healthModel->created_by = \Yii::$app->user->id;
                $healthModel->created_at = Date("Y-m-d H:i:s");

                if (!$healthModel->save()) {
                    $this->addErrors($healthModel->getErrors());
                    return false;
                }

                $temp_date->add(new DateInterval('P1D'));

            }
        } else {
            $maintenanceModel = new PetHistory();
            $maintenanceModel->id_pet = $pet->id;
            $maintenanceModel->event = PetHistory::HISTORY_EVENT_MAINTENANCE;
            $maintenanceModel->id_organization = $id_organization;
            $maintenanceModel->created_by = \Yii::$app->user->id;
            $maintenanceModel->created_at = Date("Y-m-d H:i:s");

            if (!$maintenanceModel->save()) {
                $this->addErrors($maintenanceModel->getErrors());
                return false;
            }
        }
    }


    /**
     * @param int|int[] $id_pet
     * @return \app\models\db\PetHealth[]
     */
    public function getPetHealth($id_pet)
    {
        $pet = Pets::findOne(['id' => $id_pet]);

        if (empty($pet)) {
            throw new BadRequestHttpException('Указанное животное не найдено');
        }

        $pet_healths = PetHealth::find()
            ->where(['id_pet' => $id_pet])
            ->orderBy('date')
            ->all();

        return $pet_healths;
    }

    /**
     * Сохранение информации о здоровье животного
     *
     * @param $id_pet
     * @param $healths
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveHealth($id_pet, $healths)
    {
        $pet = Pets::findOne(['id' => $id_pet]);

        if (empty($pet)) {
            throw new BadRequestHttpException('Указанное животное не найдено');
        }

        /*
         * Разбиваем входной массив на два:
         * 1. Те которые трогать не надо - от них только id нужен
         * 2. Отредактированные или обновленные(все равно перезаписывать) - для сохранения
         */
        $separated_by_action = $this->separateHealthsByAction($healths);

        PetHealth::getDb()->beginTransaction();

        /*
         * Удаляем старые/обновленные
         */
        $this->removeFromTablesDeletedOrEditedHealths(
            $id_pet, $separated_by_action
        );

        /*
         * Валидируем и сохраняем новые/отредактированные
         */
        foreach ($separated_by_action['new_or_edited'] as $health) {
            $health = $this->saveHealthRow($id_pet, $health);
        }

        PetHealth::getDb()->transaction->commit();
    }

    /**
     * Сохраняем в БД
     *
     * @param $id_pet
     * @param $type
     * @param $health
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function saveHealthRow($id_pet, $health)
    {
        $health['date'] = $health['date'] ? DateTime::createFromFormat("Y-m-d", $health['date'])->format("Y-m-d") : null;
        if (array_key_exists('created_by', $health) && $health['created_by'])
            $health['updated_by'] = \Yii::$app->user->id;
        else
            $health['created_by'] = \Yii::$app->user->id;
        if (array_key_exists('created_at', $health) && $health['created_at'])
            $health['updated_at'] = Date("Y-m-d H:i:s");
        else
            $health['created_at'] = Date("Y-m-d H:i:s");

        $health['id_pet'] = $id_pet;

        /*
        * Создаем новую запись на вставку
        */
        $healthModel = new PetHealth($health);


        if (!$healthModel->save()) {
            // Откатываем изменения
            if (PetHealth::getDb()->transaction->isActive) {
                PetHealth::getDb()->transaction->rollBack();
            }

            $errors = $healthModel->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении ' : implode("\n", array_values($errors)));
        }

        return $healthModel;
    }

    /**
     * Удаляем из таблиц все по этому животному кроме нетронутых записей
     *
     * @param $id_pet
     * @param $separated_by_action
     */
    protected function removeFromTablesDeletedOrEditedHealths($id_pet, $separated_by_action)
    {
        $do_not_touch = $separated_by_action['do_not_touch'];
        $condition = ($do_not_touch) ?
            [
                'AND',
                ['id_pet' => $id_pet],
                ['NOT IN', 'id', $do_not_touch]
            ] : [
                'id_pet' => $id_pet
            ];

        PetHealth::deleteAll($condition);
    }

    /**
     * Разбивает входной массив $healths на два с такой же структурой
     * В первом, do_not_touch, будут ТОЛЬКО id от тех записей, которые удалять в бд не надо
     * Во втором, new_or_edited, будут измененные/новые записи целиком
     *
     * @param $healths
     * @return array
     * @throws BadRequestHttpException
     */
    protected function separateHealthsByAction($healths)
    {
        /*
         * По уговору с фронтом в ДАННОМ методе с id будут приходить только не измененные вакцины
         * Остальные - новые или отредактированные
         */

        $result = [
            'do_not_touch' => [],
            'new_or_edited' => [],
        ];

        foreach ($healths as $health) {
            if (array_key_exists('id', $health) && !is_numeric($health['id'])) {
                throw new BadRequestHttpException('id вакцины/препарата должен быть числом');
            } elseif (array_key_exists('id', $health)) {
                $result['do_not_touch'][] = $health['id'];
            } else {
                $result['new_or_edited'][] = $health;
            }
        }


        return $result;
    }


    /**
     * @param int $id_pet
     * @param int $id_species
     * @param int|null $id_breed
     * @param string|null $sex
     * @param string|null $color
     * @param string|null $characteristics
     * @param array|null $identifications
     * @return array|false
     */
    public function updatePet(
        int    $id_pet,
        int    $id_species,
        int    $id_breed = null,
        string $sex = null,
        string $color = null,
        string $characteristics = null,
        array  $identifications = null
    )
    {
        $pet = $this->findPet($id_pet);
        if ($pet === null) {
            $this->addError('id_pet', 'Указанное животное не найдено');

            return false;
        }

        if ($pet->id_created_organization != $this->id_organization) {
            $this->addError('id_pet', 'Вы не можете редактировать данное животное');

            return false;
        }

        $identifications = $identifications ?? [];
        $identification_code_chip = $this->extractChipNumber($identifications);
        if (!empty($identification_code_chip)) {
            $chip = $this->findChip($identification_code_chip);
            if ($chip !== null && $chip->id_pet != $pet->id) {
                $this->addError('id_pet', 'Указанный номер чипа уже используется');

                return false;
            }
        }

        $pet->setAttributes(compact('id_species', 'id_breed', 'sex', 'color', 'characteristics'));
        if (count($pet->getDirtyAttributes()) > 0) {
            if (!$pet->save()) {
                $this->addErrors($pet->getErrors());

                return false;
            }
        }

        $this->saveIdentifications($identifications, $pet->id, true);

        return [
            'record' => $pet->getLastActiveShelterRecord($this->id_organization)->toArray(),
            'pet' => $this->preparePetOutput($pet),
        ];
    }

    /**
     * @param int $id_pet
     * @param int $id_ident_type
     * @param string $identification_code
     * @return array|bool
     */
    public function actionAddIdentification(
        int    $id_pet,
        int    $id_ident_type,
        string $identification_code
    )
    {
        $pet = $this->findPet($id_pet);
        if ($pet === null) {
            $this->addError('id_pet', 'Указанное животное не найдено');

            return false;
        }

        if ($id_ident_type == $this->id_ident_type_chip) {
            $chip = $this->findChip($identification_code);
            if ($chip !== null) {
                $this->addError('id_pet', 'Указанный номер чипа уже используется');

                return false;
            }
        }

        $identModel = new PetIdentification();
        $identModel->id_pet = $pet->id;
        $identModel->id_ident_type = $id_ident_type;
        $identModel->identification_code = $identification_code;

        if (!$identModel->save()) {
            $this->addErrors($identModel->getErrors());

            return false;
        }

        return [
            'record' => $pet->getLastActiveShelterRecord($this->id_organization)->toArray(),
            'pet' => $this->preparePetOutput($pet),
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
     * @return array|bool
     */
    public function updateRecord(
        int    $id_record,
        int    $id_pet,
        string $arrival_date,
        string $arrival_comment = null,
        string $departure_date = null,
        string $departure_comment = null,
        string $status = null,
        string $departure_reason = null
    )
    {
        $pet = $this->findPet($id_pet);
        if ($pet === null) {
            $this->addError('id_pet', 'Указанное животное не найдено');

            return false;
        }

        $record = ShelterGuests::findOne([
            'id' => $id_record,
            'id_pet' => $id_pet,
        ]);
        if ($record === null) {
            $this->addError('id_record', 'Запись о нахождении животного в приюте не найдена');

            return false;
        }

        if ($record->departure_date !== null) {
            $this->addError('id_record', 'После выбытия животного редактирование записи не допускается');

            return false;
        }

        $record->setAttributes(compact('arrival_date', 'arrival_comment', 'departure_date', 'departure_comment', 'status', 'departure_reason', 'departure_specialist'));
        if (count($record->getDirtyAttributes()) > 0) {
            if (!$record->save()) {
                $this->addErrors($record->getErrors());

                return false;
            }
        }

        return [
            'record' => $record->toArray(),
            'pet' => $this->preparePetOutput($pet),
        ];
    }

    /**
     * @param int $id_pet
     * @param string $departure_date
     * @param string $departure_reason
     * @param string|null $departure_comment
     * @param int|null $departure_specialist
     * @param int|null $id_owner
     * @param array|null $departure_documents
     * @return array|bool
     */
    public function departurePet(
        int    $id_pet,
        string $departure_date,
        string $departure_reason = null,
        string $departure_comment = null,
        int $departure_specialist = null,
        int    $id_owner = null,
        array  $departure_documents = null
    )
    {
        if (strtotime($departure_date) > date(strtotime('today'))) {
            $this->addError('departure_date', 'Дата выбытия не может быть позже сегодняшнего дня.');

            return false;
        }

        $pet = $this->findPet($id_pet);
        if ($pet === null) {
            $this->addError('id_pet', 'Указанное животное не найдено');

            return false;
        }

        $record = $pet->getLastActiveShelterRecord($this->id_organization);
        if ($record === null) {
            $this->addError('id_record', 'Запись о нахождении животного в приюте не найдена');

            return false;
        }

        $status = ShelterGuests::STATUS_DEPARTURED;

        $quarantine_from = $quarantine_to = $arrival_reason = $arrival_act_number = $arrival_act_number_date = $arrival_work_order = $arrival_work_order_date = $catching_act_number = $catching_act_date = $catching_address = $catching_video = null;
        $record->setAttributes(compact(
            'departure_date',
            'departure_comment',
            'departure_reason',
            'departure_specialist',
            'status',
            'id_owner',
            'quarantine_from',
            'quarantine_to',
            'arrival_reason',
            'arrival_act_number',
            'arrival_act_number_date',
            'arrival_work_order',
            'arrival_work_order_date',
            'catching_act_number',
            'catching_act_date',
            'catching_address',
            'catching_video'
        ));

        if (!$record->save()) {
            $this->addErrors($record->getErrors());

            return false;
        }

        if ($departure_reason == ShelterGuests::DEPARTURE_REASON_RETURNED_TO_OWNER && !empty($id_owner)) {
            // связь между владельцем и животным создается только после подтверждения выдачи животного "новому" владельцу
            $link = PetsToOwner::findOne([
                'id_owner' => $id_owner,
                'id_pet' => $id_pet,
            ]);
            if ($link === null) {
                $id_owner_type = PetOwnerType::findRepresentativeTypeId();
                (new PetToOwnerModel())->create($id_pet, $id_owner, $id_owner_type);
                $pet->refresh();
            }
        }

        if ($departure_reason == ShelterGuests::DEPARTURE_REASON_DEATH) {
            /** @var RegExpireReasons $reason */
            $reason = RegExpireReasons::find()->where(['tech_name' => RegExpireReasons::TECH_NAME_DEATH])->one();
            $pet->reg_expire_date = date('Y-m-d');
            $pet->id_reg_expire_reason = $reason->id;

            if (!$pet->save()) {
                $errors = $pet->getErrorSummary(true);
                throw new Exception(empty($errors) ? 'Ошибка при снятии животного с учёта' : implode("\n", array_values($errors)));
            }
        }

        if (!empty($departure_documents)) {
            foreach ($departure_documents as $document) {
                $documentRecord = Files::findOne(['id' => $document['id']]);
                $documentRecord->entity_id = $record->id;
                $documentRecord->entity_type = 'shelter_guests';
                if (!$documentRecord->save()) {
                    $this->addErrors($documentRecord->getErrors());
                    return false;
                }

                $docModel = new Documents();
                $docModel->file_id = $documentRecord->id;
                $docModel->type_id = intval($document['document_type']);
                // $docModel->status = $document['document_status'];
                $docModel->number = $document['document_number'];
                $docModel->date = $document['document_date'];
                $docModel->name = $document['full_name'];
                $docModel->created_by = \Yii::$app->user->id;
                $docModel->created_date = Date("Y-m-d H:i:s");

                if (!$docModel->save()) {
                    $this->addErrors($docModel->getErrors());

                    return false;
                }

                PetHistory::addRecord([
                    'id_pet' => $pet->id,
                    'event' => 'Добавлен документ: ' . $docModel->name,
                ]);
            }
        }

        return [
            'record' => $record->toArray(),
            'pet' => $this->preparePetOutput($pet),
        ];
    }

    /**
     * @param int $id_pet
     * @return array|false
     */
    public function getPet(int $id_pet)
    {
        $pet = $this->findPet($id_pet);
        if ($pet === null) {
            $this->addError('id_pet', 'Указанное животное не найдено');

            return false;
        }

        $record = $pet->getLastShelterRecord($this->id_organization);
        if ($record === null) {
            $this->addError('id_record', 'Запись о нахождении животного в приюте не найдена');

            return false;
        }

        return [
            'record' => $record->toArray(['*'], ['pet_owner']),
            'pet' => $this->preparePetOutput($pet),
        ];
    }

    /**
     * @param int $id_pet
     * @return \app\models\db\Pets|null
     */
    private function findPet(int $id_pet)
    {
        return Pets::findOne(['id' => $id_pet]);
    }

    /**
     * @param string $identification_code
     * @return \app\models\db\PetIdentification|null
     */
    private function findChip(string $identification_code)
    {
        return PetIdentification::findOne([
            'id_ident_type' => $this->id_ident_type_chip,
            'identification_code' => $identification_code,
        ]);
    }

    /**
     * @param array $columns
     * @param array|null $identifications
     * @return \app\models\db\Pets|bool
     */
    private function createPet(array $columns)
    {
        $pet = new Pets($columns);
        $pet->id_created_organization = $this->id_organization;
        if (!$pet->save()) {
            $this->addErrors($pet->getErrors());

            return false;
        }

        return $pet;
    }

    /**
     * @param \app\models\db\Pets $pet
     * @return mixed
     */
    private function preparePetOutput($pet)
    {
        if ($pet->pet_identification !== null && $pet->species !== null && $pet->breeds !== null) {
            // just to populate relations
        }

        $output = $pet->toArray([], ['pet_identification', 'species', 'breeds']);
        $output['can_edit'] = ($pet->id_created_organization == $this->id_organization);

        return $output;
    }

    /**
     * @param array $identifications
     * @return string|null
     */
    private function extractChipNumber(array $identifications)
    {
        if (empty($identifications)) {
            return null;
        }

        foreach ($identifications as $identification) {
            if ($identification['id_ident_type'] == $this->id_ident_type_chip) {
                return $identification['identification_code'];
            }
        }

        return null;
    }

    /**
     * @param array $identifications
     * @param int $id_pet
     * @param bool $deleteExisting
     */
    private function saveIdentifications($identifications, $id_pet, $deleteExisting = false)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if ($deleteExisting === true) {
                PetIdentification::deleteAll(['id_pet' => $id_pet]);
            }

            foreach ($identifications as $identification) {
                $id_ident_type = $identification['id_ident_type'];
                $identification_code = strval($identification['identification_code']);
                $identModel = new PetIdentification(compact('id_ident_type', 'identification_code'));
                $identModel->id_pet = $id_pet;
                if (!$identModel->save()) {
                    $transaction->rollBack();

                    return;
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * @param array $identifications
     * @param int $id_pet
     * @param bool $deleteExisting
     */
    private function saveWool(int $wool, $id_pet, $deleteExisting = false)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if ($deleteExisting === true) {
                PetsToPetRefWoolType::deleteAll(['id_pets' => $id_pet]);
            }

            $woolModel = new PetsToPetRefWoolType(array('id_pet_ref_wool_type' => $wool));
            $woolModel->id_pets = $id_pet;
            if (!$woolModel->save()) {
                $transaction->rollBack();

                return;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $identifications
     * @param int $id_pet
     * @param bool $deleteExisting
     */
    private function saveEars(int $ears, $id_pet, $deleteExisting = false)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if ($deleteExisting === true) {
                PetsToPetRefEarType::deleteAll(['id_pets' => $id_pet]);
            }

            $earModel = new PetsToPetRefEarType(array('id_pet_ref_ear_type' => $ears));
            $earModel->id_pets = $id_pet;
            if (!$earModel->save()) {
                $transaction->rollBack();

                return;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $identifications
     * @param int $id_pet
     * @param bool $deleteExisting
     */
    private function saveTails(int $tails, $id_pet, $deleteExisting = false)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if ($deleteExisting === true) {
                PetsToPetRefTailType::deleteAll(['id_pets' => $id_pet]);
            }

            $tailModel = new PetsToPetRefTailType(array('id_pet_ref_tail_type' => $tails));
            $tailModel->id_pets = $id_pet;
            if (!$tailModel->save()) {
                $transaction->rollBack();

                return;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $identifications
     * @param int $id_pet
     * @param bool $deleteExisting
     */
    private function saveSize(int $id_size, $id_pet, $deleteExisting = false)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if ($deleteExisting === true) {
                PetsToPetRefSize::deleteAll(['id_pets' => $id_pet]);
            }

            $sizeModel = new PetsToPetRefSize(array('id_pet_ref_size' => $id_size));
            $sizeModel->id_pets = $id_pet;
            if (!$sizeModel->save()) {
                $transaction->rollBack();

                return;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param Pets $pet
     * @param string $identification_code_chip
     * @throws \Exception
     */
    private function notifyOwner(Pets $pet, string $identification_code_chip)
    {
        if ($pet->owner === null) {
            return;
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($pet->owner);

        if (empty($contacts)) {
            return;
        }

        // контакты приюта
        $phone = '';
        $organization = Organizations::findOne(['id' => $this->id_organization]);
        foreach ($organization->contacts as $contact) {
            if ($contact->contact_type->type == ContactTypes::TYPE_PHONE) {
                $phone = $contact->name;
                break;
            }
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new AnimalFoundEvent([
            'pet' => $pet,
            'id_pet' => $pet->id,
            'petMicrochip' => $identification_code_chip,
            'contacts' => $contacts,
            'phone' => $phone,
            'address' => (($organization->fias_addresses === null) ? '' : $organization->fias_addresses->full_address)
        ]));
    }

    /**
     * @param int $id_pet
     * @throws \yii\base\InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws NotFoundHttpException
     */
    public function createAnimalCard(int $id_pet)
    {
        /* @var $generator \app\common\components\pdfGenerator\PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');

        $pet = $this->findPet($id_pet);
        if (empty($pet))
            throw new NotFoundHttpException('Животное не найдено');

        $is_catch = false;
        $arrival_work_order = '_____';
        $catching_act_number = '_____';
        $is_legal = false;
        $shelter = ShelterGuests::findOne(['id_pet' => $id_pet]);
        if (!empty($shelter)) {

            if (!empty($shelter->aviary)) {
                $aviary = $shelter->aviary->title;
            }
            $departure_comment = $shelter->departure_comment;
            $shelter_id = $shelter->id_organization;
            $arrival_act_number = $shelter->arrival_act_number ?? '';
            $arrival_reason = empty($shelter->arrival_reason) ? '' : ShelterGuests::ARRIVAL_REASON_REPORT[$shelter->arrival_reason];
            $is_catch = empty($shelter->arrival_reason) ? false : $shelter->arrival_reason === ShelterGuests::ARRIVAL_REASON_CATCH;
            $arrival_work_order = $shelter->arrival_work_order ?? '_____';
            $catching_act_number = $shelter->catching_act_number ?? '_____';
            $catching_address = empty($shelter->fiasAddress) || empty($shelter->fiasAddress->full_address)
                ? ''
                : $shelter->fiasAddress->full_address;

            if (!empty($shelter->departure_date))
                $departure_date = DateTime::createFromFormat("Y-m-d", $shelter->departure_date);
            if (!empty($shelter->arrival_work_order_date))
                $arrival_work_order_date = DateTime::createFromFormat("Y-m-d", $shelter->arrival_work_order_date);
            if (!empty($shelter->catching_act_date))
                $catching_act_date = DateTime::createFromFormat("Y-m-d", $shelter->catching_act_date);
            if ($shelter->is_quarantine && !empty($shelter->quarantine_from))
                $quarantine_from = DateTime::createFromFormat("Y-m-d", $shelter->quarantine_from);
            if ($shelter->is_quarantine && !empty($shelter->quarantine_to))
                $quarantine_to = DateTime::createFromFormat("Y-m-d", $shelter->quarantine_to);
            if (!empty($shelter->arrival_date))
                $arrival_date = DateTime::createFromFormat("Y-m-d", $shelter->arrival_date);

            if (!empty($shelter->organization)) {
                $shelter_name = $shelter->organization->name;
                $chief_name = $shelter->organization->chief_name;
                if (!empty($shelter->organization->fias_addresses))
                    $shelter_address = $shelter->organization->fias_addresses->full_address;
            }

            if (!empty($shelter->pet_owner)) {
                $is_legal = $shelter->pet_owner->is_legal;
                if ($is_legal) $tutor = $shelter->pet_owner->fullname;
                $fio = $shelter->pet_owner->is_legal
                    ? $shelter->pet_owner->jur_name
                    : $shelter->pet_owner->fullname;
                if (!empty($shelter->pet_owner->fias_addresses)) {
                    $address = $shelter->pet_owner->fias_addresses->full_address;
                }
                $phone = Contacts::findOne(['id_contact_type' => $this->id_contact_type_org_phone_mob->id, 'entity_id' => $shelter->pet_owner->id]);

            }
        }

        $photoPath = '';
        $photo = Files::findOne(['entity_id' => $id_pet, 'entity_type' => 'shelter_main']);
        if (!empty($photo)) {
            $webroot = \Yii::getAlias('@webroot', false);
            if (!$webroot) $webroot = \Yii::getAlias('@app/web');
            $filePath = $webroot . FileHelper::normalizePath(\Yii::getAlias($photo->path));
            if (file_exists($filePath)) {
                $photoPath = $filePath;
            }
        }

        $idents = PetIdentification::findAll(['id_pet' => $id_pet]);
        $ident = '';
        if (count($idents) > 0) {
            $label = array_search($this->id_ident_type_label, array_column($idents, 'id_ident_type'));
            $chip = array_search($this->id_ident_type_chip, array_column($idents, 'id_ident_type'));
            if ($chip !== false)
                $ident = 'чип ' . $idents[$chip]->identification_code;
            if ($label !== false)
                $ident = 'метка ' . $idents[$label]->identification_code;
        }

        $health_rows = PetHealth::findAll(['id_pet' => $id_pet]);
        usort($health_rows, function ($a, $b) {
            return $a->date > $b->date;
        });
        if (count($health_rows) > 0) {
            $filtered = array_filter($health_rows, function (PetHealth $v) {
                return !is_null($v->weight);
            });
            if (!empty($filtered)) {
                $weight = $filtered[0]->weight;
            }
            if (!empty($health_rows[0]->date)) {
                $quarantine_from = DateTime::createFromFormat("Y-m-d", $health_rows[0]->date);
            }
            if (!empty($health_rows[count($health_rows) - 1]->date)) {
                $quarantine_to = DateTime::createFromFormat("Y-m-d", $health_rows[count($health_rows) - 1]->date);
            }
        }

        $drugs = PetDehelmintization::findAll(['id_pet' => $id_pet]);
        $vaccines = PetOtherVaccinations::findAll(['id_pet' => $id_pet]);
        $castrated_date = DateTime::createFromFormat("Y-m-d", $pet->castrated_date);
        $data = [
            'aviary' => empty($aviary) ? '__________' : $aviary,
            'health' => $health_rows,
            'drugs' => $drugs,
            'vaccines' => $vaccines,
            'castrated' => $pet->early_castrated,
            'castrated_date' => !empty($castrated_date)
                ? '«' . $castrated_date->format('d') . '» '
                . $this->arr[$castrated_date->format('n') - 1] . ' '
                . $castrated_date->format('Y') . ' г.'
                : '«___»______________20___ г.',
            'castrated_specialist' => empty($pet->castratedSpecialist)
            || empty($pet->castratedSpecialist->user)
            || empty($pet->castratedSpecialist->user->fullname) ? '' : $pet->castratedSpecialist->user->fullname,
            'castrated_org' => empty($pet->castratedOrg)
            || empty($pet->castratedOrg->name) ? '' : $pet->castratedOrg->name,
            'date' => '«' . Date('d') . '» ' . $this->arr[Date('n') - 1] . ' ' . Date('Y') . ' г.',
            'weight' => empty($weight) ? '____________________' : $weight,
            'shelter_name' => empty($shelter_name) ? '___________________' : $shelter_name,
            'shelter_address' => empty($shelter_address) ? '_____________________________________________________' : $shelter_address,
            'is_dog' => $pet->id_species === $this->id_species_dog->id,
            'age' => DateHelper::ageAtDate($pet->birthday, date('Y-m-d H:i:s')) ?? '________________',
            'sex' => Pets::GENDER_TYPES[$pet->sex] ?? '___________________',
            'name' => $pet->name ?? '________________',
            'color' => empty($pet->petRefColor) || empty($pet->petRefColor->title) ? '_________________' : $pet->petRefColor->title,
            'breed' => empty($pet->breeds) || empty($pet->breeds->name) ? '________________' : $pet->breeds->name,
            'ears' => empty($pet->petRefEarType) || empty($pet->petRefEarType->title) ? '__________________' : $pet->petRefEarType->title,
            'wool' => empty($pet->petRefWoolType) || empty($pet->petRefWoolType->title) ? '________________' : $pet->petRefWoolType->title,
            'size' => empty($pet->petRefSize) || empty($pet->petRefSize->title) ? '________________' : $pet->petRefSize->title,
            'tail' => empty($pet->petRefTailType) || empty($pet->petRefTailType->title) ? '__________________' : $pet->petRefTailType->title,
            'characteristics' => $pet->characteristics ?? '___________________________________________________',
            'character' => $pet->character ?? '_________________________________________________________',
            'ident' => empty($ident) ? '___________________________________________' : $ident,
            'exist_photo' => !empty($photoPath),
            'photoPath' => $photoPath,
            'socialized' => !empty($shelter) && $shelter->socialized,
            'arrival_act_number' => $arrival_act_number,
            'arrival_reason' => $arrival_reason,
            'is_catch' => $is_catch,
            'arrival_work_order' => $arrival_work_order,
            'arrival_work_order_date' => !empty($arrival_work_order_date)
                ? '«' . $arrival_work_order_date->format('d') . '» '
                . $this->arr[$arrival_work_order_date->format('n') - 1] . ' '
                . $arrival_work_order_date->format('Y') . ' г.'
                : '«___»______________20___ г.',
            'catching_act_number' => $catching_act_number,
            'catching_act_date' => !empty($catching_act_date)
                ? '«' . $catching_act_date->format('d') . '» '
                . $this->arr[$catching_act_date->format('n') - 1] . ' '
                . $catching_act_date->format('Y') . ' г.'
                : '«___»______________20___ г.',
            'catching_address' => $catching_address,
            'is_catching_video' => !empty($shelter) && $shelter->is_catching_video,
            'quarantine_from' => !empty($quarantine_from)
                ? '«' . $quarantine_from->format('d') . '» '
                . $this->arr[$quarantine_from->format('n') - 1] . ' '
                . $quarantine_from->format('Y') . ' г.'
                : '«___»______________20___ г.',
            'quarantine_to' => !empty($quarantine_to)
                ? '«' . $quarantine_to->format('d') . '» '
                . $this->arr[$quarantine_to->format('n') - 1] . ' '
                . $quarantine_to->format('Y') . ' г.'
                : '«___»______________20___ г.',
            'arrival_date' => !empty($arrival_date)
                ? '«' . $arrival_date->format('d') . '» '
                . $this->arr[$arrival_date->format('n') - 1] . ' '
                . $arrival_date->format('Y') . ' г.'
                : '«___»______________20___ г.',
            'chief_name' => empty($chief_name) ? '_____________________' : $chief_name,
            'card_num' => 'К/У' . $id_pet . ($pet->id_species === $this->id_species_dog->id ? 'с' : 'к') . $shelter_id,
            'departure_date' => !empty($departure_date)
                ? '«' . $departure_date->format('d') . '» '
                . $this->arr[$departure_date->format('n') - 1] . ' '
                . $departure_date->format('Y') . ' г.'
                : '«___»____________20___ г.',
            'departure_comment' => $departure_comment,
            'is_new_owner' => mb_convert_case($departure_comment, MB_CASE_LOWER, "UTF-8") === 'передача новому владельцу',
            'is_death' => mb_convert_case($departure_comment, MB_CASE_LOWER, "UTF-8") === 'смерть',
            'is_ephtanazia' => mb_convert_case($departure_comment, MB_CASE_LOWER, "UTF-8") === 'эвтаназия',
            'is_legal' => $is_legal,
            'fio' => empty($fio) ? '___________________________________________' : $fio,
            'tutor' => empty($tutor) ? '___________________________________________________' : $tutor,
            'address' => empty($address) ? '_____________________________________________________________' : $address,
            'phone' => empty($phone) ? '________________________________________________' : $phone->name,
        ];


        try {
            [$dir, $filename, $ext] = $generator->createDocument('pet_card', $data);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }

        /** @var FileService $fileService */
        $fileService = \Yii::$app->fileService;
        $hash = $fileService->generateHash($filename);

        $fileResource = new FileResource();
        $fileResource->hash = $hash;
        $fileResource->path = '/upload/pdf/' . $filename . '.' . $ext;
        $fileResource->name = $filename . '.' . $ext;
        $fileResource->entity_id = $id_pet;
        $fileResource->entity_type = 'pdf';

        return $fileResource;
    }

    /**
     * @param int $id_pet
     * @return null|FileResource
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function createTransferContract(int $id_pet)
    {
        /* @var $generator \app\common\components\wordGenerator\WordGenerator */
        $generator = \Yii::$app->get('wordGenerator');

        $pet = $this->findPet($id_pet);
        if (empty($pet))
            throw new NotFoundHttpException('Животное не найдено');

        //данные по приюту
        $shelter = ShelterGuests::findOne(['id_pet' => $id_pet]);
        if (!empty($shelter)) {
            $shelter_id = $shelter->id_organization;

            if ($shelter->id_organization)
                $org_phone = Contacts::findOne(['id_contact_type' => $this->id_contact_type_org_phone->id, 'entity_id' => $shelter->id_organization]);

            if (!empty($shelter->organization)) {
                $uo_id = $shelter->organization->managing_organization_id;
                $uo = Organizations::findOne(['id' => $uo_id]);
                $uo_name=$uo->name;
                
                $chief_name = $shelter->organization->chief_name;
                $inn = $shelter->organization->inn;
                $kpp = $shelter->organization->kpp;
                $ogrn = $shelter->organization->ogrn;
                if (!empty($shelter->organization->fias_addresses))
                    $shelter_address = $shelter->organization->fias_addresses->full_address;
            }

            if (!empty($shelter->departure_date))
                $departure_date = DateTime::createFromFormat("Y-m-d", $shelter->departure_date);
            if (!empty($shelter->pet_owner)) {
                $legal_type = htmlspecialchars($shelter->pet_owner->is_legal ? 'Ф.И.О./<U>организация</U> ' : '<U>Ф.И.О.</U>/организация');
                $fio = $shelter->pet_owner->is_legal
                    ? $shelter->pet_owner->jur_name
                    : $shelter->pet_owner->fullname;
                if (!empty($shelter->pet_owner->fias_addresses)) {
                    $address = $shelter->pet_owner->fias_addresses->full_address;
                }
                if ($shelter->pet_owner->addresses_is_equal) {
                    $fact_address = $address;
                } else if (!empty($shelter->pet_owner->fact_fias_addresses)) {
                    $fact_address = $shelter->pet_owner->fias_addresses->full_address;
                }
                $phone = Contacts::findOne(['id_contact_type' => $this->id_contact_type_org_phone_mob->id, 'entity_id' => $shelter->pet_owner->id]);

            }
        }

        //данные по индентификации
        $idents = PetIdentification::findAll(['id_pet' => $id_pet]);
        $ident = '';
        if (count($idents) > 0) {
            $label = array_search($this->id_ident_type_label, array_column($idents, 'id_ident_type'));
            $chip = array_search($this->id_ident_type_chip, array_column($idents, 'id_ident_type'));
            if ($label !== false)
                $ident = ', № метки ' . $idents[$label]->identification_code;
            if ($chip !== false)
                $ident = ', № чипа ' . $idents[$chip]->identification_code;
        }

        //данные по животному
        if (!empty($data['pet']->birthday))
            $age = DateHelper::ageAtDate($pet->birthday, date('Y-m-d H:i:s')) ?? '';


        $data = [
            'shelter_name' => empty($uo_name) ? '' : $uo_name,
            'chief_name' => empty($chief_name)
                ? '_______________ _________________________________________'
                : $chief_name,
            'name' => empty($pet->name) ? '' : ', кличка ' . $pet->name,
            'sex' => empty(Pets::GENDER_TYPES[$pet->sex]) ? '' : ', пол ' . Pets::GENDER_TYPES[$pet->sex],
            'age' => empty($age) ? '' : ', возраст ' . $age,
            'breed' => empty($pet->breeds) || empty($pet->breeds->name) ? '' : ', порода ' . $pet->breeds->name,
            'color' => empty($pet->petRefColor) || empty($pet->petRefColor->title) ? '' : ', окрас ' . $pet->petRefColor->title,
            'wool' => empty($pet->petRefWoolType) || empty($pet->petRefWoolType->title) ? '' : ', шерсть ' . $pet->petRefWoolType->title,
            'size' => empty($pet->petRefSize) || empty($pet->petRefSize->title) ? '' : ', размер ' . $pet->petRefSize->title,
            'ident' => $ident,
            'characteristics' => empty($pet->characteristics) ? '' : ', особые приметы ' . $pet->characteristics,
            'character' => empty($pet->character) ? '' : ', характер ' . $pet->character,
            'socialized' => $this->replaceUnderline(htmlspecialchars(!empty($shelter) && $shelter->socialized ? '<U>да</U>/нет' : 'да/<U>нет</U>')),
            'shelter_address' => empty($shelter_address) ? '' : $shelter_address,
            'ur_shelter_address' => empty($shelter_address) ? '' : $shelter_address,
            'inn' => empty($inn) ? '' : $inn,
            'kpp' => empty($kpp) ? '' : $kpp,
            'ogrn' => empty($ogrn) ? '' : $ogrn,
            'org_phone' => empty($org_phone) ? '' : $org_phone->name,
            'card_num' => ', № карточки учета  К/У' . $id_pet . ($pet->id_species === $this->id_species_dog->id ? 'с' : 'к') . $shelter_id,
            'departure_date' => !empty($departure_date)
                ? '«' . $departure_date->format('d') . '» '
                . $this->arr[$departure_date->format('n') - 1] . ' '
                . $departure_date->format('Y')
                : '«___»____________20___',
            'legal_type' => empty($legal_type) ? 'Ф.И.О./организация (нужное подчеркнуть)' : $this->replaceUnderline($legal_type),
            'fio' => empty($fio) ? '__________________________________________________________________' : $fio,
            'address' => empty($address) ? '_____________________________________' : $address,
            'fact_address' => empty($fact_address) ? '_____________________________________________ __________________________________________________________________' : $fact_address,
            'phone' => empty($phone) ? '_______________________' : $phone->name,
            'species' => $this->replaceUnderline(htmlspecialchars($pet->id_species === $this->id_species_dog->id ? '<U>собака</U>/кошка' : 'собака/<U>кошка</U>'))
        ];

        try {
            $fileResource = $generator->createDocument('transfer', 'Договор передачи', $data, $id_pet);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Ошибка при генерации договора передачи');
        }

        return $fileResource;
    }

    /**
     * @param int $id_pet
     * @return null|FileResource
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function createReturnAct(int $id_pet)
    {
        /* @var $generator \app\common\components\wordGenerator\WordGenerator */
        $generator = \Yii::$app->get('wordGenerator');

        $pet = $this->findPet($id_pet);
        if (empty($pet))
            throw new NotFoundHttpException('Животное не найдено');

        //инфо по приюту
        $shelter = ShelterGuests::findOne(['id_pet' => $id_pet]);
        if (!empty($shelter)) {

            if (!empty($shelter->departure_date))
                $departure_date = DateTime::createFromFormat("Y-m-d", $shelter->departure_date);

            if (!empty($shelter->pet_owner)) {
                $legal_type = htmlspecialchars($shelter->pet_owner->is_legal ? 'Ф.И.О./<U>организация</U> ' : '<U>Ф.И.О.</U>/организация');
                $fio = $shelter->pet_owner->is_legal
                    ? $shelter->pet_owner->jur_name
                    : $shelter->pet_owner->fullname;
                if (!empty($shelter->pet_owner->fias_addresses)) {
                    $address = $shelter->pet_owner->fias_addresses->full_address;
                }
                $phone = Contacts::findOne(['id_contact_type' => $this->id_contact_type_org_phone_mob->id, 'entity_id' => $shelter->pet_owner->id]);

            }

            if (!empty($shelter->organization)) {
                $shelter_name = $shelter->organization->name;
                if (!empty($shelter->organization->fias_addresses))
                    $shelter_address = $shelter->organization->fias_addresses->full_address;
            }
        }

        //инфо по животному
        $species = $pet->id_species === $this->id_species_dog->id ? 'собака' : 'кошка';
        $color = empty($pet->petRefColor) ? '' : $pet->petRefColor->title;
        $idents = PetIdentification::findAll(['id_pet' => $id_pet]);
        $pet_charcteristics = $species . ', пол ' . Pets::GENDER_TYPES[$pet->sex] . ', окрас ' . $color;

        //идентификация
        $ident = '';
        if (count($idents) > 0) {
            $label = array_search($this->id_ident_type_label, array_column($idents, 'id_ident_type'));
            $chip = array_search($this->id_ident_type_chip, array_column($idents, 'id_ident_type'));
            if ($label !== false)
                $ident = 'номер метки ' . $idents[$label]->identification_code;
            if ($chip !== false)
                $ident = 'номер чипа ' . $idents[$chip]->identification_code;
        }


        $data = [
            'shelter_name' => empty($shelter_name)
                ? '_________
                                __________________________________________________________________'
                : $shelter_name,
            'shelter_address' => empty($shelter_address)
                ? '_____________________________________________________'
                : $shelter_address,
            'pet_charcteristics' => $pet_charcteristics . $this->addSpaces($pet_charcteristics, 207),
            'ident' => empty($ident) ? '_____________________________________' : $ident,
            'departure_date' => !empty($departure_date)
                ? '«' . $departure_date->format('d') . '» '
                . $this->arr[$departure_date->format('n') - 1] . ' '
                . $departure_date->format('Y')
                : '«___»____________20___ ',
            'legal_type' => empty($legal_type) ? 'Ф.И.О./организация (нужное подчеркнуть)' : $this->replaceUnderline($legal_type),
            'fio' => empty($fio) ? '_______________________________________________ __________________________________________________________________' : $fio,
            'address' => empty($address) ? '___________________________________' : $address,
            'phone' => empty($phone) ? '_______________________' : $phone->name
        ];

        try {
            $fileResource = $generator->createDocument('return', 'Акт возврата', $data, $id_pet);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Ошибка при генерации акта возврата');
        }

        return $fileResource;
    }

    /**
     * @param int $id_pet
     * @return null|FileResource
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function createDeathAct(int $id_pet)
    {
        /* @var $generator \app\common\components\wordGenerator\WordGenerator */
        $generator = \Yii::$app->get('wordGenerator');

        $pet = $this->findPet($id_pet);
        if (empty($pet))
            throw new NotFoundHttpException('Животное не найдено');

        //данные приюта
        $shelter = ShelterGuests::findOne(['id_pet' => $id_pet]);
        $death_reason = '';
        if (!empty($shelter)) {
            $shelter_id = $shelter->id_organization;

            $death_reason = !empty($shelter->departure_comment)
            && mb_convert_case($shelter->departure_comment, MB_CASE_LOWER, "UTF-8") === 'эвтаназия'
                ? 'Эвтаназия'
                : '';
            if (!empty($shelter->departure_date))
                $departure_date = DateTime::createFromFormat("Y-m-d", $shelter->departure_date);

            if ($shelter->id_organization)
                $phone = Contacts::findOne(['id_contact_type' => $this->id_contact_type_org_phone->id, 'entity_id' => $shelter->id_organization]);

            if (!empty($shelter->organization)) {
                $shelter_name = $shelter->organization->name;
                $chief_name = $shelter->organization->chief_name;
                if (!empty($shelter->organization->fias_addresses))
                    $shelter_address = $shelter->organization->fias_addresses->full_address;
            }
        }

        //данные идентификации
        $idents = PetIdentification::findAll(['id_pet' => $id_pet]);
        $ident = '';
        if (count($idents) > 0) {
            $label = array_search($this->id_ident_type_label, array_column($idents, 'id_ident_type'));
            $chip = array_search($this->id_ident_type_chip, array_column($idents, 'id_ident_type'));
            if ($chip !== false)
                $ident = $idents[$chip]->identification_code;
            if ($label !== false)
                $ident = $idents[$label]->identification_code;
        }

        //данные животного
        $color = empty($pet->petRefColor) ? '' : $pet->petRefColor->title;
        $size = empty($pet->petRefSize) ? '' : $pet->petRefSize->title;
        $tail = empty($pet->petRefTailType) ? '' : $pet->petRefTailType->title;
        $ears = empty($pet->petRefEarsType) ? '' : $pet->petRefEarsType->title;
        $wool = empty($pet->petRefWoolType) ? '' : $pet->petRefWoolType->title;
        if (!empty($pet->birthday)) {
            $age = DateHelper::ageAtDate($pet->birthday, date('Y-m-d H:i:s')) ?? '';
            $year = DateHelper::ageInYear($pet->birthday, date('Y-m-d H:i:s')) ?? '';
            $is_baby = $year < 1;
        }

        $card_num = $id_pet . ($pet->id_species === $this->id_species_dog->id ? 'с' : 'к') . $shelter_id;

        $health_rows = PetHealth::findAll(['id_pet' => $id_pet]);
        if (count($health_rows) > 0) {
            $filtered = array_filter($health_rows, function (PetHealth $v) {
                return !is_null($v->weight);
            });
            if (!empty($filtered)) {
                usort($filtered, function ($a, $b) {
                    return $a->date > $b->date;
                });
                $weight = $filtered[0]->weight;
                $size = empty($size) ? $weight : $size . ', ' . $weight;
            }
        }

        $is_dog = $pet->id_species === $this->id_species_dog->id;

        $species = htmlspecialchars($is_dog
            ? ($is_baby ? 'собака,<U> щенок</U>, кошка, котенок' : '<U>собака</U>, щенок, кошка, котенок')
            : ($is_baby ? 'собака, щенок, кошка,<U> котенок</U>' : 'собака, щенок,<U> кошка</U>, котенок'));

        $data = [
            'species' => $this->replaceUnderline($species),
            'age' => $age . $this->addSpaces($age, 104),
            'ident_code' => $ident . $this->addSpaces($ident, 66),
            'color' => $color . $this->addSpaces($color, 117),
            'wool' => $wool . $this->addSpaces($wool, 114),
            'ears' => $ears . $this->addSpaces($ears, 120),
            'tail' => $tail . $this->addSpaces($tail, 117),
            'size' => $size . $this->addSpaces($size, 107),
            'card_num' => 'К/У' . $card_num . $this->addSpaces($card_num, 7),
            'name' => $pet->name . $this->addSpaces($pet->name, 93),
            'sex' => Pets::GENDER_TYPES[$pet->sex] . $this->addSpaces(Pets::GENDER_TYPES[$pet->sex], 121),
            'characteristics' => $pet->characteristics . $this->addSpaces($pet->characteristics, 99),
            'death_reason' => $death_reason . $this->addSpaces('', 84),
            'departure_date' => !empty($departure_date)
                ? '«' . $departure_date->format('d') . '» '
                . $this->arr[$departure_date->format('n') - 1] . ' '
                . $departure_date->format('Y')
                : '«___»____________20___ ',
            'temp_date' => '«' . Date('d') . '» '
                . $this->arr[Date('n') - 1] . ' '
                . Date('Y'),
            'shelter_name' => empty($shelter_name)
                ? '_____________________________________________________'
                : $shelter_name,
            'shelter_address' => empty($shelter_address)
                ? '__________________________________________________________________'
                : $shelter_address,
            'chief_name' => empty($chief_name)
                ? '_____________________ __________________________________________________________________'
                : $chief_name,
            'phone' => !empty($phone)
                ? $phone->name
                : '__________________'
        ];

        try {
            $fileResource = $generator->createDocument('death', 'Акт смерти животного', $data, $id_pet);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Ошибка при генерации акта смерти животного');
        }

        return $fileResource;
    }

    /**
     * @param int $word
     * @param int $default_space_count
     * @return string
     */
    private function addSpaces(string $word, int $default_space_count)
    {
        return str_repeat(
            '<w:t xml:space="preserve"> </w:t>',
            $default_space_count - strlen($word) > 0 ? $default_space_count - strlen($word) : 0);
    }


    /**
     * @param int $word
     * @return string
     */
    private function replaceUnderline(string $word)
    {
        $replace = str_replace('&lt;U&gt;', '</w:t></w:r><w:r><w:rPr><w:u w:val="single"/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve">', $word);
        return str_replace('&lt;/U&gt;', '</w:t></w:r><w:r><w:rPr><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t>', $replace);
    }
}