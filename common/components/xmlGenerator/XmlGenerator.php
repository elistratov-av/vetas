<?php

namespace app\common\components\xmlGenerator;

use \app\common\components\media\ResourceFileRepository;
use app\models\db\ContactTypes;
use app\models\db\PetIdentification;
use app\models\db\PetOwners;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\Specialists;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitsGovServices;
use Yii;
use bupy7\xml\constructor\XmlConstructor;
use yii\db\Query;
use yii\web\NotFoundHttpException;

class XmlGenerator extends ResourceFileRepository
{

    public $xml;

    public function init()
    {
        parent::init();

        $this->path = Yii::$app->params['resources_media_dir'];
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function generate($id)
    {
        $xmlConstructor = new XmlConstructor();

        $result = $xmlConstructor->fromArray($this->generateVisitData($id))->toOutput();

        return $result;
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function generateOdopm($data)
    {
        $xmlConstructor = new XmlConstructor(['startDocument' => false]);
        $result = $xmlConstructor->fromArray($data)->toOutput();

        return $result;
    }

    /**
     * Оборачиваем в cdata
     * @param $data
     * @return string
     */
    public function cdataWrapper($data)
    {
        $xmlConstructor = new XmlConstructor(['startDocument' => false]);
        $result = $xmlConstructor->fromArray($data)->toOutput();
        return $result;
    }

    /**
     * @param $pet_id
     * @return PetRabiesVaccination
     */
    protected function getLastVaccination($pet_id)
    {
        return (new Query())
            ->from('pet_rabies_vaccination')
            ->where(['id_pet' => $pet_id])
            ->orderBy('date DESC')
            ->one();
    }

    /**
     * @param $owner_id
     * @param $pet_id
     * @return string
     */
    protected function getPetOwnerType($owner_id, $pet_id)
    {
        return (new Query())->select('pet_owner_type.name')
            ->from('pets_to_owner')
            ->leftJoin('pet_owner_type', 'pets_to_owner.id_owner_type = pet_owner_type.id')
            ->where(
                [
                    'AND',
                    ['pets_to_owner.id_pet' => $pet_id],
                    ['pets_to_owner.id_owner' => $owner_id],
                ]
            )
            ->scalar();
    }

    /**
     * @param $pet_id
     * @return PetIdentification
     */
    protected function getPetIdentification($pet_id)
    {
        return (new Query())
            ->from('pet_identification')
            ->where(
                [
                    'AND',
                    ['main_flag' => true],
                    ['id_pet' => $pet_id],
                ])
            ->one();
    }

    /**
     * Телефон пользователя
     *
     * @param PetOwners $owner
     * @return string|null
     */
    protected function getOwnerMainPhone($owner)
    {
        $contacts = $owner
            ->getContacts()
            ->select('contacts.name')
            ->leftJoin('contact_types', 'id_contact_type = contact_types.id')
            ->where(['type' => ContactTypes::TYPE_PHONE])
            ->andWhere(['main_flag' => true])
            ->limit(1)
            ->scalar();

        return (!empty($contacts)) ? $contacts : null;
    }

    /**
     * Email пользователя
     *
     * @param PetOwners $owner
     * @return string|null
     */
    protected function getOwnerMainEmail($owner)
    {
        $contacts = $owner
            ->getContacts()
            ->select('contacts.name')
            ->leftJoin('contact_types', 'id_contact_type = contact_types.id')
            ->where(['type' => ContactTypes::TYPE_EMAIL])
            ->andWhere(['main_flag' => true])
            ->limit(1)
            ->scalar();

        return (!empty($contacts)) ? $contacts : null;
    }

    /**
     * Собирает блок для elements массива Услуг
     *
     * @param $id_visit
     * @return array|null
     */
    protected function prepareArrayGovServices($id_visit)
    {
        $result = null;

        $gov_services = VisitsGovServices::find()
            ->select([
                'visits_gov_services.id AS Id',
                'gov_services.name AS Name',
                'gov_services.cod AS Code',
                'visits_gov_services.price AS Price',
                'visits_gov_services.count AS Count',
            ])
            ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
            ->where(['id_visit' => $id_visit])
            ->asArray()
            ->all();

        foreach ($gov_services as $service){
            $result[] = [
                'tag' => 'Service',
                'attributes' => $service
            ];
        }

        return $result;
    }

    /**
     * Собирает блок для elements массива ТМЦ
     *
     * @param $id_visit
     * @return array|null
     */
    protected function prepareArrayTmc($id_visit)
    {
        $result = null;

        $tmcs = VisitServiceTmc::find()
            ->select([
                'visit_service_tmc.id AS Id',
                'tmc.tmc.type AS BalanceType',
                'tmc.tmc.name AS Name',
                'tmc.balance.inventory_number AS InventoryNumber',
                'visit_service_tmc.count AS Count',
                'visit_service_tmc.price AS Price',
                'id_visits_gov_service AS VisitServiceId'
            ])
            ->leftJoin('tmc.tmc', 'visit_service_tmc.id_tmc = tmc.tmc.id'
                 . ' AND ' .
                 'visit_service_tmc.type_tmc = tmc.tmc.type')

            ->leftJoin('tmc.balance', 'visit_service_tmc.id_tmc = tmc.balance.id_tmc'
                . ' AND ' .
                 'visit_service_tmc.type_tmc = tmc.balance.type_tmc'
                . ' AND ' .
                'visit_service_tmc.id_balance_tmc = tmc.balance.id'
            )
            ->where(['id_visit' => $id_visit])
            ->asArray()
            ->all();

        foreach ($tmcs as $tmc){
            $result[] = [
                'tag' => 'Service',
                'attributes' => $tmc
            ];
        }

        return $result;
    }

    /**
     * @param integer $id - id карточки приема
     * @return array
     * @throws NotFoundHttpException
     */
    protected function generateVisitData($id)
    {
        /** @var Visits $visit */
        $visit = Visits::find()->where(['id' => $id])->one();

        if (!$visit) {
            throw new NotFoundHttpException('Карточка приема не найдена', 422);
        }

        /** @var Specialists $specialist */
        $specialist = $visit->specialists;

        /** @var PetOwners $owner */
        $owner = $visit->getOwner()->one();
        $ownerPhone = $this->getOwnerMainPhone($owner);
        $ownerEmail = $this->getOwnerMainEmail($owner);
        $ownerAddress = $owner->getFias_addresses()->one();
        $ownerFactAddress = $owner->getFact_fias_addresses()->one();

        /** @var Pets $pet */
        $pet = $visit->getPet()->one();
        $ownerType = $this->getPetOwnerType($owner->id, $pet->id);

        /** @var PetIdentification $petIdentification */
        $petIdentification = $this->getPetIdentification($pet->id);

        $price = (new Query())->select('price_with_discount, night_mark_up_ratio')->from('visit_price')->where(['id_visit' => $visit->id])->all();

        /** @var PetRabiesVaccination $vacciantion */
        $vaccination = $this->getLastVaccination($pet->id);

        $vaccination_valid = (
            !empty($vaccination['valid_until']) &&
            strtotime($visit->fact_start_dttm) <= strtotime($vaccination['valid_until'])
        ) ? 1 : 0;

        $result = [[
            'tag' => 'Visit', //root tag
            'attributes' => [
                'Id' => $visit->id,
                'Type' => $visit->type,
                'Ticket_number' => $visit->ticket_number,
                'FactStartDttm' => $visit->fact_start_dttm,
                'FactEndDttm' => $visit->fact_end_dttm,
                'TotalWithDiscount' => (!empty($price) && !empty($price[0]['price_with_discount'])) ? $price[0]['price_with_discount'] : null,
                'IsNightVisit' => (!empty($price) && !is_null($price[0]['night_mark_up_ratio'])) ? 1 : 0,
                'VisitToAddress' => $visit->visit_to_address,
                'SpecialistId' => $specialist->id,
                'SpecialistFio' => $specialist->fullname,
            ],
            'elements' => [
                [
                    'tag' => 'Owner',
                    'attributes' => [
                        'Id' => $owner->id,
                        'Fio' => $owner->fullname,
                        'Inn' => $owner->inn,
                        'Snils' => $owner->snils,
                        'FiasAddress' => (!is_null($ownerAddress)) ? $ownerAddress->full_address : null,
                        'FiasFactAddress' => (!is_null($ownerFactAddress)) ? $ownerFactAddress->full_address : null,
                        'Phone' => $ownerPhone,
                        'Type' => ($ownerType) ? $ownerType : null,
                        'EmailAddress' => $ownerEmail
                    ],
                ],
                [
                    'tag' => 'Pet',
                    'attributes' => [
                        'Id' => $pet->id,
                        'Name' => $pet->name,
                        'Species' => ($pet->species) ? $pet->species->name : null,
                        'Breed' => ($pet->breeds) ? $pet->breeds->name : null,
                        'Sex' => $pet->sex,
                        'Birthday' => $pet->birthday,
                        'IdentificatorType' => (!empty($petIdentification)) ? $petIdentification['id_ident_type'] : "нет данных",
                        'IdentitificationCode' => (!empty($petIdentification)) ? $petIdentification['identification_code'] : "нет данных",
                        'IsCastrated' => ($pet->castrated) ? 1 : 0,
                        'IsVaccinated' => $vaccination_valid,
                    ],
                ],
                [
                    'tag' => 'RabiesVaccine',
                    'attributes' => !empty($vaccination) ? [
                            'Id' => $vaccination['id'],
                            'IdVaccine' => $vaccination['id_vaccine'],
                            'DrugName' => $vaccination['drug_name'],
                            'ProducerName' => $vaccination['producer_name'],
                            'Batch' => $vaccination['batch'],
                            'ProductionDate' => $vaccination['production_date'],
                            'ExpiryDate' => $vaccination['expiry_date'],
                            'ValidUntil' => $vaccination['valid_until']
                    ] : [],
                ],
                [
                    'tag' => 'Service',
                    'elements' => $this->prepareArrayGovServices($visit->id)
                ],
                [
                    'tag' => 'Tmc',
                    'elements' => $this->prepareArrayTmc($visit->id)
                ],
            ]
        ]
        ];

        return $result;
    }
}
