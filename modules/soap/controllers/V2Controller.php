<?php

namespace app\modules\soap\controllers;

use app\common\components\FileService;
use app\modules\soap\v2\models\etp\BookingMessage;
use app\common\components\inform\events\RefundEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\modules\soap\models\etp\ETPException;
use app\modules\soap\v2\models\db\ETPMessage;
use app\modules\soap\v2\queue\MosruStatusSender;
use yii\log\Logger;
use app\common\components\pdfGenerator\PdfGenerator;
use app\common\soap\v2\SoapAction;
use app\modules\soap\v2\models\etp\ApplicationMessage;
use app\modules\soap\v2\models\etp\ApplicationStatusMessage;
use app\modules\soap\v2\models\wsdl\CurrentRegistrationsHandler;
use app\modules\soap\v2\models\wsdl\OrgSpecListHandler;
use app\modules\soap\v2\models\wsdl\ReferencesHandler;
use app\modules\soap\v2\models\wsdl\TimeSlotHandler;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Visits;
use app\models\db\VisitDescriptions;
use app\models\db\VisitPets;
use app\modules\v2\modules\pets\models\PetsModel;
use app\common\helpers\DateHelper;
use app\common\components\pdfGenerator\PdfVisitGeneratorHelper;
use app\modules\v2\modules\visit\models\BillModel;
use app\modules\soap\models\VisitsGovServices;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use app\models\db\PetsToOwner;
use app\models\db\PetOwnersHistory;
use yii\web\ServerErrorHttpException;
use yii\web\HttpException;
use app\modules\v1\models\FileResource;
use app\models\db\elk\ElkPets;
use app\models\db\elk\ElkOwners;
use app\modules\v2\modules\visit\controllers\DescriptionsController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use yii\helpers\ArrayHelper;

use yii\console\ExitCode;
use yii\web\NotFoundHttpException;

/**
 * Class V2Controller
 * @package app\modules\soap\controllers
 *
 * @property-read \app\modules\soap\Module $module
 */
class V2Controller extends BaseController
{
    /**
     * @var string|null
     */
    private $messageId;

    /**
     * @var string|null
     */
    private $systemId;

    /**
     * @inheritDoc
     */
    public function actions(){
        $soap = \Yii::$app->getModule('soap');

        return [
            'wsdl' => [
                'class' => SoapAction::class,
                'serviceUrl' => (!empty($soap->params['soapServiceUrl'])) ? $soap->params['soapServiceUrl'] : null,
                'wsdlUrl' => (!empty($soap->params['wsdlUrl'])) ? str_replace('\\','/', $soap->params['wsdlUrl']) : null,
                'wsdlOptions' => [
                    'namespace' => 'http://vetas.mos.ru',
                    'serviceName' => 'Pets',
                    // 'operationBodyStyle' => [
                    //     'use' => WsdlGenerator::USE_LITERAL,
                    // ],
                    // 'bindingStyle' => WsdlGenerator::STYLE_DOCUMENT,
                ],
            ],
        ];
    }

    protected function errorResponse($visit, $errorMessage) {
        return [
            'message' => "Для отчета $visit произошла ошибка - $errorMessage"
        ];
    }

    /**
     * @return \app\modules\soap\v2\skeletons\species\SpeciesResponse
     * @soap
     */
    public function referenceSpecies(){
        $this->reportSoapMethod(__FUNCTION__);

        return ReferencesHandler::getSpecies();
    }

    /**
     * @return \app\modules\soap\v2\skeletons\orgs\OrgsResponse
     * @soap
     */
    public function referenceOrgs(){
        $this->reportSoapMethod(__FUNCTION__);

        return ReferencesHandler::getOrgs();
    }

    /**
     * @param \app\modules\soap\v2\skeletons\services\ServicesRequest $referenceServicesRequest
     * @soap
     * @return \app\modules\soap\v2\skeletons\services\ServicesResponse
     */
    public function referenceServices($referenceServicesRequest){
        $this->reportSoapMethod(__FUNCTION__);

        return ReferencesHandler::getServices($referenceServicesRequest->SpeciesId);
    }

    public function referenceServicesClinic($referenceServicesRequest){
        $SpeciesId = isset($referenceServicesRequest->SpeciesId) ? $referenceServicesRequest->SpeciesId : null;

        if($SpeciesId){
            $this->reportSoapMethod(__FUNCTION__);

            $sql = "SELECT id, name FROM public.service_types ORDER BY name;";
            $arr_st = \Yii::$app->db->createCommand($sql)->queryAll();
            $resultTypes = array();

            foreach ($arr_st as $st_) {
                $sql_ = "SELECT public.gov_services.id, public.gov_services.name, public.gov_services.price, mosru.services_hints.text AS hint, statistic.mosru_services_rating.sort_by AS rating FROM public.gov_services ";
                $sql_.="LEFT JOIN public.species_services ON public.species_services.id_service=public.gov_services.id ";
                $sql_.="LEFT JOIN mosru.services_hints ON mosru.services_hints.id_service=public.gov_services.id ";
                $sql_.="LEFT JOIN statistic.mosru_services_rating ON statistic.mosru_services_rating.mosru_services_id=public.gov_services.id ";
                $sql_.="WHERE id_service_type=".$st_['id']." AND type='mosru' AND public.species_services.id_species=".$SpeciesId." ";
                //
                $sql_.="AND public.gov_services.at_clinic=true ";
                //
                $sql_.="ORDER BY public.gov_services.name;";

                $arr_gs = \Yii::$app->db->createCommand($sql_)->queryAll();

                if(count($arr_gs) > 0){
                    $services_array = array();
                    
                    foreach ($arr_gs as $gs_) {
                        $service = ['ServiceId' => $gs_['id'], 'ServiceValue' => $gs_['name'], 'Price' => $gs_['price'], 'Rating' => $gs_['rating'], 'Hint' => $gs_['hint']];
                        array_push($services_array, $service);
                    }
                    $element = ['TypeId' => $st_['id'], 'TypeValue' => $st_['name'], 'ServiceList' => array('Service'=> $services_array)];
                    array_push($resultTypes, $element);
                }
            }
            $resultList = ['ServicesTypeList' => array('ServicesType'=> $resultTypes) ]; //отправляем в soap
            return $resultList;
        }else{
            throw new HttpException(200, 'Входящий параметр SpeciesId является обязательным.', 404);

            return false;
        }
    }

    public function referenceServicesHome($referenceServicesRequest){
        $SpeciesId = isset($referenceServicesRequest->SpeciesId) ? $referenceServicesRequest->SpeciesId : null;

        if($SpeciesId){
            $this->reportSoapMethod(__FUNCTION__);

            $sql = "SELECT id, name FROM public.service_types ORDER BY name;";
            $arr_st = \Yii::$app->db->createCommand($sql)->queryAll();
            $resultTypes = array();

            foreach ($arr_st as $st_) {
                $sql_ = "SELECT public.gov_services.id, public.gov_services.name, public.gov_services.price, mosru.services_hints.text AS hint, statistic.mosru_services_rating.sort_by AS rating FROM public.gov_services ";
                $sql_.="LEFT JOIN public.species_services ON public.species_services.id_service=public.gov_services.id ";
                $sql_.="LEFT JOIN mosru.services_hints ON mosru.services_hints.id_service=public.gov_services.id ";
                $sql_.="LEFT JOIN statistic.mosru_services_rating ON statistic.mosru_services_rating.mosru_services_id=public.gov_services.id ";
                $sql_.="WHERE id_service_type=".$st_['id']." AND type='mosru' AND public.species_services.id_species=".$SpeciesId." ";
                //
                $sql_.="AND public.gov_services.at_home=true ";
                //
                $sql_.="ORDER BY public.gov_services.name;";

                $arr_gs = \Yii::$app->db->createCommand($sql_)->queryAll();

                if(count($arr_gs) > 0){
                    $services_array = array();
                    
                    foreach ($arr_gs as $gs_) {
                        $service = ['ServiceId' => $gs_['id'], 'ServiceValue' => $gs_['name'], 'Price' => $gs_['price'], 'Rating' => $gs_['rating'], 'Hint' => $gs_['hint']];
                        array_push($services_array, $service);
                    }
                    $element = ['TypeId' => $st_['id'], 'TypeValue' => $st_['name'], 'ServiceList' => array('Service'=> $services_array)];
                    array_push($resultTypes, $element);
                }
            }
            $resultList = ['ServicesTypeList' => array('ServicesType'=> $resultTypes) ]; //отправляем в soap
            return $resultList;
        }else{
            throw new HttpException(200, 'Входящий параметр SpeciesId является обязательным.', 404);

            return false;
        }
    }

    public function referenceServicesOnline($referenceServicesRequest){
        $SpeciesId = isset($referenceServicesRequest->SpeciesId) ? $referenceServicesRequest->SpeciesId : null;

        if($SpeciesId){
            $this->reportSoapMethod(__FUNCTION__);

            $sql = "SELECT id, name FROM public.service_types ORDER BY name;";
            $arr_st = \Yii::$app->db->createCommand($sql)->queryAll();
            $resultTypes = array();

            foreach ($arr_st as $st_) {
                $sql_ = "SELECT public.gov_services.id, public.gov_services.name, public.gov_services.price, mosru.services_hints.text AS hint, statistic.mosru_services_rating.sort_by AS rating FROM public.gov_services ";
                $sql_.="LEFT JOIN public.species_services ON public.species_services.id_service=public.gov_services.id ";
                $sql_.="LEFT JOIN mosru.services_hints ON mosru.services_hints.id_service=public.gov_services.id ";
                $sql_.="LEFT JOIN statistic.mosru_services_rating ON statistic.mosru_services_rating.mosru_services_id=public.gov_services.id ";
                $sql_.="WHERE id_service_type=".$st_['id']." AND type='mosru' AND public.species_services.id_species=".$SpeciesId." ";
                //
                $sql_.="AND public.gov_services.id_service_type=18 ";
                //
                $sql_.="ORDER BY public.gov_services.name;";

                $arr_gs = \Yii::$app->db->createCommand($sql_)->queryAll();

                if(count($arr_gs) > 0){
                    $services_array = array();
                    
                    foreach ($arr_gs as $gs_) {
                        $service = ['ServiceId' => $gs_['id'], 'ServiceValue' => $gs_['name'], 'Price' => $gs_['price'], 'Rating' => $gs_['rating'], 'Hint' => $gs_['hint']];
                        array_push($services_array, $service);
                    }
                    $element = ['TypeId' => $st_['id'], 'TypeValue' => $st_['name'], 'ServiceList' => array('Service'=> $services_array)];
                    array_push($resultTypes, $element);
                }
            }
            $resultList = ['ServicesTypeList' => array('ServicesType'=> $resultTypes) ]; //отправляем в soap
            return $resultList;
        }else{
            throw new HttpException(200, 'Входящий параметр SpeciesId является обязательным.', 404);

            return false;
        }
    }

    /**
     * @param \app\modules\soap\v2\skeletons\rq\OrgSpecListRequest $orgSpecListRequest
     *
     * @return \app\modules\soap\v2\skeletons\rq\OrgSpecList
     * @soap
     */
    public function orgSpecList($orgSpecListRequest){
        $this->reportSoapMethod(__FUNCTION__);

        $callToHome = isset($orgSpecListRequest->CallToHome) ? $orgSpecListRequest->CallToHome : false;
        $fiasCode = isset($orgSpecListRequest->FiasCode) ? $orgSpecListRequest->FiasCode : null;

        return OrgSpecListHandler::getOrgSpecList($orgSpecListRequest->Services, $callToHome, null, $fiasCode);
    }

    /**
     * @param \app\modules\soap\v2\skeletons\rq\OrgSpecListByDateRequest $orgSpecListByDateRequest
     *
     * @return \app\modules\soap\v2\skeletons\rq\OrgSpecList
     * @soap
     */
    public function orgSpecListByDate($orgSpecListByDateRequest){
        $this->reportSoapMethod(__FUNCTION__);

        $dateHours = isset($orgSpecListByDateRequest->DateHours) ? $orgSpecListByDateRequest->DateHours : null;
        $callToHome = isset($orgSpecListByDateRequest->CallToHome) ? $orgSpecListByDateRequest->CallToHome : false;
        $fiasCode = isset($orgSpecListByDateRequest->FiasCode) ? $orgSpecListByDateRequest->FiasCode : null;

        return OrgSpecListHandler::getOrgSpecList($orgSpecListByDateRequest->Services, $callToHome, $dateHours, $fiasCode);
    }

    /**
     * @param \app\modules\soap\v2\skeletons\timeslots\TimeSlotsRequest $timeSlotListRequest
     * @return \app\modules\soap\v2\skeletons\timeslots\TimeSlotsResponse
     * @throws \app\common\soap\SoapException
     * @throws \yii\db\Exception
     * @soap
     */
    public function timeSlotList($timeSlotListRequest){
        $this->reportSoapMethod(__FUNCTION__);

        $Specialist = isset($timeSlotListRequest->Specialist) ? $timeSlotListRequest->Specialist : null;
        $OrgId = isset($timeSlotListRequest->OrgId) ? $timeSlotListRequest->OrgId : null;
        $DateHours = isset($timeSlotListRequest->DateHours) ? $timeSlotListRequest->DateHours : null;
        $CallToHome = isset($timeSlotListRequest->CallToHome) ? $timeSlotListRequest->CallToHome : false;

        return TimeSlotHandler::getTimeSlotList($timeSlotListRequest->Services, $Specialist, $OrgId, $DateHours, $CallToHome);
    }

    /**
     * @param \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberRequest $getRegistrationByServiceNumberRequest
     * @return \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberResponse
     * @soap
     */
    public function getRegistrationByServiceNumber($getRegistrationByServiceNumberRequest){
        $this->reportSoapMethod(__FUNCTION__);

        $ServiceNumber = isset($getRegistrationByServiceNumberRequest->ServiceNumber) ? $getRegistrationByServiceNumberRequest->ServiceNumber : null;

        return CurrentRegistrationsHandler::getRegistrationByServiceNumber($ServiceNumber);
    }

    /**
     * @param \app\modules\soap\v2\skeletons\current_registrations\CurrentRegistrationsRequest $getCurrentRegistrationsRequest
     * @return \app\modules\soap\v2\skeletons\current_registrations\CurrentRegistrationsResponse
     * @soap
     */

    public function petOutpatientCard($CardInfo){
        if($CardInfo->SsoId && $CardInfo->PetId){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = PetOwners::findOne(['sso_id' => $CardInfo->SsoId]);
            if($owner['id'] == ''){
                //пробуем залезть напрямую в таблицу
                $rec = \Yii::$app->db->createCommand('SELECT id_owner FROM elk.owners WHERE sso_id=\''.$CardInfo->SsoId.'\';')->queryOne();
                if($rec['id_owner'] != ''){
                    $owner = PetOwners::findOne(['id' => $rec['id_owner']]);
                }
            }

            if($owner['id']){

                $SsoId = $CardInfo->SsoId;
                $PetId = $CardInfo->PetId;

                $pet = \Yii::$app->db->createCommand('SELECT id_pet FROM elk.pets WHERE ext_id=\''.$CardInfo->PetId.'\'')->queryOne();
                $id_pet = $pet['id_pet'];

                $query='SELECT visits.id FROM visits ';
                $query.='LEFT JOIN visit_pets ON visit_pets.id_visit=visits.id ';
                $query.='WHERE (visit_pets.id_pet='.$id_pet.' AND visits.status=\'F\') OR visits.id_pet='.$id_pet.' ORDER BY visits.time_range;';

                $visit_ids = [];
                $visits = \Yii::$app->db->createCommand($query)->queryAll();
                foreach ($visits as $row) {
                    $visit_ids[] = $row['id'];
                }

                // $petElkPets = ElkPets::find()
                // ->select([
                //     'pets.id_pet',
                //     'public.visits.id'
                // ])
                // ->distinct()
                // ->where([
                // "ext_id" => $PetId,
                // ])
                // ->leftJoin('public.visits', 'elk.pets.id_pet=public.visits.id_pet')
                // ->leftJoin('public.visit_pets', 'public.visit_pets.id_pet=elk.pets.id_pet')
                // ->asArray()
                // ->all();

                // $visit_ids = [];
                // for ($i=0; $i < count($petElkPets) ; $i++) {
                //     $visit_ids[] = $petElkPets[$i]['id'];
                // }

                $pets = Pets::find()
                ->with('reg_certificate')
                ->with('pet_main_identification')
                ->with('pet_main_identification.ident_type')
                ->with('species')
                ->with('breeds')
                ->with('last_pet_rabies_vaccinations')
                ->where(['id' => $id_pet])
                ->all();

                $spec_fullname = '';
                $petData = [];

                foreach ($pets as $pet) {
                    $petData[] = [
                        'name'             => $pet->name ?? 'Не указан',
                        'species'          => $pet->species->name ?? 'Не указан',
                        'breed'            => $pet->breeds->name ?? 'Не указан',
                        'sex'              => $pet->sex == 'm' ? 'м' : 'ж' ?? 'Не указан',
                        'birthday'         => DateHelper::ageAtDate($pet->birthday, date('Y-m-d H:i:s')) ?? 'Не указан',
                        'ident_type'       => $pet->pet_main_identification->ident_type->name ?? 'Не указан',
                        'pet_ident'        => $pet->pet_main_identification->identification_code ?? 'Не указан',
                        'regnum'           => $pet->reg_certificate->number ?? 'Не указан',
                        'vaccination_date' => $pet->last_pet_rabies_vaccinations->date ?? 'Не указан',
                        'spec_fullname'    => $spec_fullname,
                    ];
                }

                $visits = Visits::find()
                    ->with('owner')
                    ->with('pet')
                    ->with('organization')
                    ->with('visitsGovService')
                    ->with('visitsGovService.service')
                    ->with('visitsGovService.visitServiceParamValues')
                    ->with('specialists')
                    ->with('specialists.user')
                    ->with('author_ref.user')
                    ->with('anamnesis')
                    ->with('clinicalSigns')
                    ->with('preDiagnosis')
                    ->with('finDiagnosis')
                    ->with('treatment')
                    ->with('assurance')
                    ->with('recommendations')
                    ->where(['id' => $visit_ids])
                    ->andWhere(['!=', 'channel', '5'])
                    ->orderBy(['start_dttm' => SORT_DESC])
                    ->all();

                $visitsData = [];
                foreach ($visits as $visit) {
                    if($visit->status == 'F'){
                        $visitsData[] = [
                            'id'              => $visit->id ?? '-',
                            'start_date'      => date('d.m.Y', strtotime(($visit->fact_start_dttm ?? $visit->start_dttm) ?? $visit->created_at)) ?? '-',
                            'clinic'          => $visit->organization->short_name ?? 'Не указан',
                            'owner'           => $visit->owner->fullname ?? 'Не указан',
                            'spec'            => $visit->specialists->user->fullname ?? 'Не указан',
                            'anamnesis'       => $visit->anamnesis->description ?? 'Не указан',
                            'clinicalSigns'   => $visit->clinicalSigns->description ?? 'Не указан',
                            'preDiagnosis'    => $visit->preDiagnosis->description ?? 'Не указан',
                            'finDiagnosis'    => $visit->finDiagnosis->description ?? 'Не указан',
                            'treatment'       => $visit->treatment->description ?? 'Не указан',
                            'assurance'       => $visit->assurance->description ?? 'Не указан',
                            'recommendations' => $visit->recommendations->description ?? ' ',
                            'serv'            => PdfVisitGeneratorHelper::getServices(new BillModel($visit->id, null, null, null)) ?? '-',
                            //'serv' => $visit->visitsGovService->service->name ?? 'Нет услуг',
                            'pricesum'        => PdfVisitGeneratorHelper::getPriceSum(new BillModel($visit->id, null, null, null)) ?? '-',
                            'reportName'      => $visit->visitsGovService->service->name ?? 'Нет услуг',
                            'nameIndicator'   => $visit->visitsGovService->service->briefname ?? 'Нет данных',
                            'indicatorData'   => $visit->visitsGovService->visitServiceParamValues->num_value ?? 'Нет данных',
                            'indicatorEd'     => $visit->visitsGovService->visitServiceParamValues->char_value ?? ' ',
                        ];

                        $pet = \app\models\db\VisitsGovServices::find()
                            ->with('service')
                            ->where([
                                'id_pet'   => $id_pet,
                                'id_visit' => $visit_ids
                            ])
                            ->all();

                        $petsData = [];
                        foreach ($pet as $price) {
                            $petsData[] = [
                                'cod'      => $price->service->cod ?? '-',
                                'count'    => $price->count ?? '-',
                                'services' => $price->service->name ?? '-',
                                'price'    => $price->service->price ?? '-',
                            ];
                        }
                    }
                }
                $currentOwners = PetsToOwner::find()
                    ->with('owner')
                    ->with('owner.fias_addresses')
                    ->with('owner.fact_fias_addresses')
                    ->with('owner.phoneMainContact')
                    ->with('owner.emailMainContact')
                    ->where(['pets_to_owner.id_pet' => $id_pet])
                    ->orderBy(['id_owner_type' => SORT_ASC])
                    ->all();

                $owner = $currentOwners[0]->owner;

                $ownerFields = [
                    'date'      => date('d.m.Y', strtotime($owner->created_at)),
                    'name'      => $owner->fullname,
                    'addr'      => $owner->fias_addresses->full_address ?? 'Не указан',
                    'fact_addr' => $owner->fact_fias_addresses->full_address ?? 'Не указан',
                    'phone'     => $owner->phoneMainContact->name ?? 'Не указан',
                    'email'     => $owner->emailMainContact->name ?? 'Не указан',
                ];

                $entrepreneurs = [];
                foreach ($currentOwners as $entrep) {
                    if ($entrep->id_owner_type == 2) {
                        $entrepFields = [];
                        $entrepFields['name'] = $entrep->owner->fullname;
                        $entrepFields['phone'] = $entrep->owner->phoneMainContact->name ?? 'Не указан';
                        $entrepFields['email'] = $entrep->owner->emailMainContact->name ?? 'Не указан';
                        $entrepreneurs[] = $entrepFields;
                    }
                }

                $exHistory = PetOwnersHistory::find()
                    ->with('owner')
                    ->with('owner.fact_fias_addresses')
                    ->with('owner.fias_addresses')
                    ->with('owner.phoneMainContact')
                    ->with('owner.emailMainContact')
                    ->where([
                        'id_pet'         => $id_pet,
                        'owner_type_new' => 'Владелец'
                    ])
                    ->orderBy(['date' => SORT_DESC])
                    ->all();

                $ex = [];
                foreach ($exHistory as $record) {
                    $ex[] = [
                        'date'      => date('d.m.Y', strtotime($record->date)) ?? '??',
                        'name'      => $record->owner_name,
                        'addr'      => $record->owner->fias_addresses->full_address ?? 'Не указан',
                        'fact_addr' => $record->owner->fact_fias_addresses->full_address ?? 'Не указан',
                        'phone'     => $record->owner->phoneMainContact->name ?? 'Не указан',
                        'email'     => $record->owner->emailMainContact->name ?? 'Не указан'
                    ];
                }

                $historyData = [
                    'current' => [
                        'owner'         => $ownerFields,
                        'entrepreneurs' => $entrepreneurs
                    ],
                    'ex' => $ex,
                ];
                $petInfo = [
                    'pet'     => $petData,
                    'visit'   => $visitsData,
                    'history' => $historyData,
                    //  'servicePet' => $petsData,
                ];

                $generator = \Yii::$app->get('pdfGenerator');

                $card_data = $petInfo;

                if (empty($card_data)) {
                    return false;
                }

                $data = [
                    'data'         => $card_data['pet'],
                    'visits'       => $card_data['visit'],
                    'his'          => $card_data['history']['ex'],
                    'historyowner' => $card_data['history']['current']['owner'],
                    //'petService' => $card_data['servicePet'],
                ];

                try {
                    [$dir, $filename, $ext] = $generator->createDocument('pets_card', $data);
                } catch (\Exception $e) {

                    throw new ServerErrorHttpException($e->getMessage());
                }
                $dir = str_replace('\\','/', $dir);
                $file_type = 'pet';
                /** @var FileService $fileService */
                $fileService = \Yii::$app->fileService;
                $path = $dir . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
                $hash = $fileService->generateHash($filename);
                $old = FileResource::find()
                    ->andWhere(['entity_id' => $id_pet, 'entity_type' => $file_type])
                    ->orderBy(['created' => SORT_DESC])
                    ->one();

                try {
                    $old_path = $old ? $old->path : null;
                    $old_path ? $fileService->delete($file_type, $id_pet, $old_path) : null;
                    $fileResource = new FileResource();
                    $fileResource->hash = $hash;
                    $fileResource->path = '/upload/pdf/' . $filename . '.' . $ext;
                    $fileResource->name = $filename . '.' . $ext;
                    $fileResource->entity_id = $id_pet;
                    $fileResource->entity_type = $file_type;
                    $fileResource->save();
                    $fileService->attach($fileResource);
                } catch (\Throwable $e) {
                    $fileService->repository->delete($path);
                    throw new ServerErrorHttpException('Ошибка сохранения файла: ' . $e->getMessage());
                }

                $path = '';
                foreach($fileResource as $key => $value) {
                    if($key === 'path'){
                        //$path = 'https://'.$_SERVER['HTTP_HOST'].str_replace('\\','/', $value);
                        $path =  $_ENV['API_URL'] . str_replace('\\','/', $value);
                    }
                }

                $example = array(
                    'SsoId'=>$SsoId,
                    'PetId'=>$PetId,
                    'PetOutpatientCardLink'=> $path
                );
                return $example;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$CardInfo->SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }

    public function getPetVisitsFuture($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;

        //Представление списка предстоящих записей животного для авторизованного пользователя
        if($SsoId && $PetId){
            $this->reportSoapMethod(__FUNCTION__);
            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $pets_regs_array = array();
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){//найденное животное
                        $pet = Visits::find()
                        ->select([
                            'visits.*',
                            'visit_pets.*',
                            'visits_gov_services.id_service',
                            'services.name AS name_service',
                            'visits.channel AS Channel',
                            '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                            '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                            'organizations.name AS Organization',
                            'organizations.latitude AS Latitude',
                            'organizations.longitude AS Longitude',
                            'addresses.name AS Address',
                            'visits.id AS Id',
                            'visits.ticket_number AS TicketNumber',
                            'visits.duration AS Duration',
                            'users.fullname AS FullNameDoctor',
                            'users.f_fio AS FNameDoctor',
                            'users.i_fio AS INameDoctor',
                            'users.o_fio AS ONameDoctor',
                            'elk.pets.ext_id AS ext_id',
                        ])
                        ->where(["visit_pets.id_pet" =>$arr_pets[$i]['id']])
                        ->andWhere(['or',
                            ['visits.status'=>'N']
                        ])
                        ->andFilterWhere(['>=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i")])//Ограничение по времени
                        ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                        ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                        ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                        ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                        ->leftJoin('users', 'specialists.id_user=users.id')
                        ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                        ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                        ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                        ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                        ->asArray()
                        ->orderBy('LOWER(VISITS.TIME_RANGE)::date DESC')
                        ->all();

                        //$arr_pets[$i]['Id'] = $arr_pets[$i]['ext_id'];unset($arr_pets[$i]['ext_id']);

                        foreach ($pet as &$value) {
                            $importantKeys = &$value;
                            $element = [];
                            foreach(array_keys($importantKeys) as $key){
                                if ($key === "Id") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Organization") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Address") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Latitude") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Longitude") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Channel") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "TicketNumber") {$element += [$key => $importantKeys[$key]];}

                                //if ($key === "Editable") {$element += [$key => '0'];}//0-Нельзя редактировать, 1-Можно редактировать
                                $element += ['Editable' => '0'];//Пока костылим

                                if ($key === "Duration") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "FullNameDoctor") {
                                    //$element += [$key => $importantKeys[$key]];
                                    $element += [$key => $importantKeys['FNameDoctor'].','.$importantKeys['INameDoctor'].','.$importantKeys['ONameDoctor']];
                                }
                                if ($key === "slot_date") {$element += ["Date" => $importantKeys[$key]];}
                                if ($key === "slot_time") {$element += ["Slot" => $importantKeys[$key]];}
                                if ($key === "id_service") {
                                    $id_visit_gov = $importantKeys['id_visit'];

                                    $pet_services_ = VisitsGovServices::find()
                                    ->select([
                                        'gov_services.id AS id',
                                        'gov_services.name AS name',
                                        'gov_services.id_service_type AS type',
                                    ])
                                    ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                                    ->where(['id_visit' => $id_visit_gov])
                                    ->asArray()
                                    ->all();

                                    $pet_services_result = [];
                                    foreach ($pet_services_ as $pet_service){
                                        $pet_services_result[] = ['ServiceId' => $pet_service['id'], 'ServiceType' => $pet_service['type'], 'ServiceName' => $pet_service['name']];
                                    }
                                    $element += ["ServicesList" => array('Service'=> $pet_services_result)];
                                }
                            }
                            array_push($pets_regs_array, $element);
                        }
                    }
                }

                $resultPetsList = ['RegistrationsList' => $pets_regs_array ]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);
            return false;
        }
    }

    public function referenceMosruErrors(){
        $this->reportSoapMethod(__FUNCTION__);

        $error_array = array();
        // for($i=0;$i<=5;$i++){
        //     $element = [];

        //     $element += ['Name' => ''.$i.''];
        //     $element += ['Name2' => ''.$i.''];

        //     array_push($error_array, $element);
        // }
        $element = [];
        $element += ['Name' => 'передача новому владельцу'];
        array_push($error_array, $element);

        $element = [];
        $element += ['Name' => 'смерть'];
        array_push($error_array, $element);

        $element = [];
        $element += ['Name' => 'животное предложено по ошибке'];
        array_push($error_array, $element);

        $resultMosruErrors = ['MosruErrorsList' => $error_array]; //отправляем в soap

        return $resultMosruErrors;
    }

    public function referenceMosruDeleteReasons(){
        $this->reportSoapMethod(__FUNCTION__);

        $error_array = array();
        $element = [];
        $element += ['Id' => '1'];
        $element += ['Name' => 'передача другому владельцу'];
        array_push($error_array, $element);

        $element = [];
        $element += ['Id' => '2'];
        $element += ['Name' => 'смерть'];
        array_push($error_array, $element);

        $element = [];
        $element += ['Id' => '3'];
        $element += ['Name' => 'иное'];
        array_push($error_array, $element);

        $resultMosruErrors = ['MosruDeleteReasonsList' => $error_array]; //отправляем в soap

        return $resultMosruErrors;
    }

    public function getPetListFull($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;

        if($SsoId){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = \Yii::$app->db->createCommand('SELECT id_owner FROM elk.owners WHERE sso_id=\''.$SsoId.'\';')->queryOne();
            //$owner = PetOwners::findOne(['sso_id' => $SsoId]);
            //if($owner){
            if($owner['id_owner']){
                $query = Pets::find()
                    ->select([
                        'public.pets.id',
                        'public.pets.name',
                        'public.pets.sex',
                        'public.pets.birthday',
                        'public.pets.id_breed AS breed',
                        'public.pets.id_species AS species',
                        'elk.pets.ext_id AS ext_id',
                        'public.fias_addresses.city AS city',
                        'public.fias_addresses.street AS street',
                        'public.fias_addresses.house AS house',
                        'public.fias_addresses.room AS room',
                        'public.pet_identification.identification_code AS identification',
                        'public.pet_identification.id_ident_type AS ident_type',
                        //'public.breeds.name AS breed',
                    ])

                    ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                    ->leftJoin('public.fias_addresses', 'public.pets.id_fias_address=public.fias_addresses.id')
                    ->leftJoin('public.pet_identification', 'public.pets.id=public.pet_identification.id_pet AND pet_identification.main_flag=true')
                    ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')

                    ->with('last_pet_rabies_vaccinations')
                    ->with(['pet_rabies_vaccinations' => function ($query) {
                        $query->select([
                            'pet_rabies_vaccination.id',
                            'pet_rabies_vaccination.id_pet',
                            'pet_rabies_vaccination.id_vaccine',
                            'pet_rabies_vaccination.drug_name',
                            'pet_rabies_vaccination.producer_name',
                            'pet_rabies_vaccination.batch',
                            'pet_rabies_vaccination.production_date',
                            'pet_rabies_vaccination.expiry_date',
                            'pet_rabies_vaccination.date',
                            'pet_rabies_vaccination.valid_until',
                            'pet_rabies_vaccination.id_organization',
                            'pet_rabies_vaccination.id_specialist',
                            'pet_rabies_vaccination.mosru_organization',
                            'organizations.name AS name_organization',
                            'files.hash AS file_hash',
                        ])->leftJoin(
                            'organizations',
                            'pet_rabies_vaccination.id_organization = organizations.id AND pet_rabies_vaccination.id_organization IS NOT NULL'
                        )->leftJoin(
                            'files',
                            'pet_rabies_vaccination.id = files.entity_id AND files.entity_type=\'vac-rab-mos-ru\''
                        );
                    }])
                    ->with(['pet_ectoparasites' => function ($query) {
                        $query->select([
                            'pet_ectoparasites.id',
                            'pet_ectoparasites.id_pet',
                            'pet_ectoparasites.id_drug',
                            'pet_ectoparasites.drug_name',
                            'pet_ectoparasites.producer_name',
                            'pet_ectoparasites.date',
                            'pet_ectoparasites.id_organization',
                            'pet_ectoparasites.id_specialist',
                            'pet_ectoparasites.mosru_organization',
                            'organizations.name AS name_organization',
                        ])->leftJoin(
                            'organizations',
                            'pet_ectoparasites.id_organization = organizations.id'
                        );
                    }])
                    ->with(['pet_other_vaccinations' => function ($query) {
                        $query->select([
                            'pet_other_vaccinations.id',
                            'pet_other_vaccinations.id_pet',
                            'pet_other_vaccinations.id_vaccine',
                            'pet_other_vaccinations.drug_name',
                            'pet_other_vaccinations.producer_name',
                            'pet_other_vaccinations.batch',
                            'pet_other_vaccinations.production_date',
                            'pet_other_vaccinations.expiry_date',
                            'pet_other_vaccinations.date',
                            'pet_other_vaccinations.valid_until',
                            'pet_other_vaccinations.id_organization',
                            'pet_other_vaccinations.id_specialist',
                            'pet_other_vaccinations.mosru_organization',
                            'organizations.name AS name_organization',
                            'files.hash AS file_hash',
                        ])->leftJoin(
                            'organizations',
                            'pet_other_vaccinations.id_organization = organizations.id'
                        )->leftJoin(
                            'files',
                            'pet_other_vaccinations.id = files.entity_id AND files.entity_type=\'vac-other-mos-ru\''
                        );
                    }])


                    ->where(['public.pets_to_owner.id_owner' => $owner['id_owner']])
                    //->andWhere(['IS NOT', 'elk.pets.ext_id', null]);
                    ->andWhere(['AND',
                        ['public.pets.mosru'=>'true'],
                        ['public.pets_to_owner.id_owner_type'=>'1'],
                    ]);
                //->leftJoin('public.breeds', 'public.pets.id_breed=public.breeds.id')
                $arr_pets = $query->asArray()->all();

                for ($i = 0; $i < count($arr_pets); $i++) {
                    $pet_visits = Visits::find()
                    ->select([
                        'visits.*',
                        'visits.id AS id_visit',
                        'visit_pets.*',
                        'visits_gov_services.id_service',
                        'services.name AS name_service',
                        'visits.channel AS Channel',

                        'visits.status AS Status',

                        '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                        '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                        'organizations.name AS Organization',
                        'organizations.latitude AS Latitude',
                        'organizations.longitude AS Longitude',
                        'addresses.name AS Address',
                        'visits.id AS Id',
                        'visits.ticket_number AS TicketNumber',
                        'visits.duration AS Duration',
                        'users.fullname AS FullNameDoctor',
                        'users.f_fio AS FNameDoctor',
                        'users.i_fio AS INameDoctor',
                        'users.o_fio AS ONameDoctor',
                        'elk.pets.ext_id AS ext_id',
                    ])
                    //->where(["visit_pets.id_pet" =>$arr_pets[$i]['IntId']])//
                    ->where(["visit_pets.id_pet" =>$arr_pets[$i]['id']])
                    ->andWhere(['OR',
                        ['visits.status'=>'N'],
                        ['visits.status'=>'F']
                    ])
                    ->andFilterWhere(['>=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i", strtotime('-3 years'))])//Ограничение по времени!!!
                    ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                    ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                    ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                    ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                    ->leftJoin('users', 'specialists.id_user=users.id')
                    ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                    ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                    ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                    ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                    ->asArray()
                    ->orderBy('LOWER(VISITS.TIME_RANGE)::date DESC')
                    ->all();

                    // $pet_vac_ecto = Pets::find()
                    // ->with('pet_rabies_vaccinations')
                    // ->with('pet_other_vaccinations')
                    // ->with('pet_ectoparasites')
                    // ->with('last_pet_rabies_vaccinations')
                    // //->where(['id' => $arr_pets[$i]['IntId']])
                    // ->where(['id' => $arr_pets[$i]['id']])
                    // ->asArray()
                    // ->all();

                    // Добавляем статусы вакцинации
                    // <!--0-Вакцинация просрочена; 1-До окончания вакцинации 30 дней; 2-Вакцинирован-->

                    $today = date("Y-m-d");
                    $check_time = $arr_pets[$i]['last_pet_rabies_vaccinations']['valid_until'];

                    $diference = strtotime($check_time) - strtotime($today); // разница между двумя датами в секундах
                    $days = $diference / 86400; // секунды в сутках

                    $vaccinationStatus = '0';
                    if($check_time){
                        if($days > 30){
                            $vaccinationStatus = '2';
                        }
                        if($days < 30 && $days > 0){
                            $vaccinationStatus = '1';
                        }
                        if($days < 0){
                            $vaccinationStatus = '0';
                        }
                    }

                    $arr_pets[$i] += ['VaccinationStatus' => $vaccinationStatus];

                    //Вакцинации от бешенства + остальные
                    $pet_vac_array = array();
                    foreach ($arr_pets[$i]['pet_rabies_vaccinations'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                            if ($key === "producer_name") {
                                if($importantKeys[$key]){
                                    $element += ['ProducerName' => $importantKeys[$key]];
                                }else{
                                    $element += ['ProducerName' => ''];
                                }
                            }
                            if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}

                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    if($importantKeys['mosru_organization']){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $element += ['Organization' => ''];
                                    }
                                    $element += ['Editable' => '1'];
                                }else{
                                    //$org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    // $element += ['Organization' => $org['name']];

                                    $element += ['Organization' => $importantKeys['name_organization']];
                                    $element += ['Editable' => '0'];
                                }
                            }

                            if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}

                            $element += ['Type' => '1'];//Бешенство

                            //GUID
                            if($importantKeys['file_hash']){
                                $element += ['GUID' => $importantKeys['file_hash']];
                            }else{
                                $element += ['GUID' => ''];
                            }
                            // $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['id'].' AND entity_type=\'vac-rab-mos-ru\';')->queryOne();
                            // if($file['hash']){
                            //     $element += ['GUID' => $file['hash']];
                            // }else{
                            //     $element += ['GUID' => ''];
                            // }
                            //GUID
                        }
                        array_push($pet_vac_array, $element);
                    }
                    foreach ($arr_pets[$i]['pet_other_vaccinations'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}

                            if ($key === "producer_name") {
                                if($importantKeys[$key]){
                                    $element += ['ProducerName' => $importantKeys[$key]];
                                }else{
                                    $element += ['ProducerName' => ''];
                                }
                            }

                            if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}

                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    if($importantKeys['mosru_organization']){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $element += ['Organization' => ''];
                                    }
                                    $element += ['Editable' => '1'];
                                }else{
                                    $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    $element += ['Organization' => $org['name']];
                                    $element += ['Editable' => '0'];
                                }
                            }

                            if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}

                            $element += ['Type' => '0'];//Остальное

                            //GUID
                            if($importantKeys['file_hash']){
                                $element += ['GUID' => $importantKeys['file_hash']];
                            }else{
                                $element += ['GUID' => ''];
                            }
                            // $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['id'].' AND entity_type=\'vac-other-mos-ru\';')->queryOne();
                            // if($file['hash']){
                            //     $element += ['GUID' => $file['hash']];
                            // }else{
                            //     $element += ['GUID' => ''];
                            // }
                            //GUID
                        }
                        array_push($pet_vac_array, $element);
                    }
                    //Вакцинации от бешенства + остальные

                    //Обработки от эктопаразитов
                    $pet_ecto_array = array();
                    foreach ($arr_pets[$i]['pet_ectoparasites'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                            if ($key === "producer_name") {
                                if($importantKeys[$key]){
                                    $element += ['ProducerName' => $importantKeys[$key]];
                                }else{
                                    $element += ['ProducerName' => ''];
                                }
                            }
                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    if($importantKeys['mosru_organization']){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $element += ['Organization' => ''];
                                    }
                                    $element += ['Editable' => '1'];
                                }else{
                                    $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    $element += ['Organization' => $org['name']];
                                    $element += ['Editable' => '0'];
                                }
                            }
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}
                        }
                        array_push($pet_ecto_array, $element);
                    }
                    //Обработки от эктопаразитов//

                    $pets_regs_array = array();
                    //$pets_regs_flag=0;
                    foreach ($pet_visits as &$value) {
                        $importantKeys = &$value;
                        $el_pet_filter = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "Id") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Organization") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Address") {
                                if($importantKeys[$key]){
                                    $el_pet_filter += [$key => $importantKeys[$key]];
                                }else{
                                    $el_pet_filter += [$key => ''];
                                }
                            }
                            if ($key === "Latitude") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Longitude") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Channel") {
                                $el_pet_filter += [$key => $importantKeys[$key]];

                                if($importantKeys[$key] == 5){
                                    $el_pet_filter += ['Editable' => '1'];

                                    if($importantKeys['mosru_organization']){
                                        $el_pet_filter += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $el_pet_filter += ['Organization' => ''];
                                    }

                                    if($importantKeys['mosru_address']){
                                        $el_pet_filter += ['Address' => $importantKeys['mosru_address']];
                                    }else{
                                        $el_pet_filter += ['Address' => ''];
                                    }

                                    if($importantKeys['mosru_specialist']){
                                        $el_pet_filter += ['FullNameDoctor' => $importantKeys['mosru_specialist']];
                                    }else{
                                        $el_pet_filter += ['FullNameDoctor' => ''];
                                    }
                                }else{
                                    $el_pet_filter += ['Editable' => '0'];
                                }

                                $file = \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['Id'].' AND entity_type=\'visits-mos-ru\';')->queryOne();
                                if($file['hash']){
                                    $el_pet_filter += ['GUID' => $file['hash']];
                                }else{
                                    $el_pet_filter += ['GUID' => ''];
                                }
                            }
                            if ($key === "TicketNumber") {$el_pet_filter += [$key => $importantKeys[$key]];}

                            if ($key === "Duration") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "FullNameDoctor") {
                                //$el_pet_filter += [$key => $importantKeys[$key]];
                                $el_pet_filter += [$key => $importantKeys['FNameDoctor'].','.$importantKeys['INameDoctor'].','.$importantKeys['ONameDoctor']];
                            }
                            if ($key === "slot_date") {$el_pet_filter += ["Date" => $importantKeys[$key]];}
                            if ($key === "slot_time") {$el_pet_filter += ["Slot" => $importantKeys[$key]];}
                            if ($key === "id_service") {
                                $id_visit_gov = $importantKeys['id_visit'];

                                $pet_services_ = VisitsGovServices::find()
                                ->select([
                                    'gov_services.name AS name',
                                    'gov_services.id AS id',
                                    'gov_services.id_service_type AS type',
                                ])
                                ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                                ->where(['id_visit' => $id_visit_gov])
                                ->asArray()
                                ->all();

                                $pet_services_result = [];
                                foreach ($pet_services_ as $pet_service){
                                    $pet_services_result[] = ['ServiceId' => $pet_service['id'], 'ServiceName' => $pet_service['name'], 'ServiceType' => $pet_service['type']];
                                }
                                $el_pet_filter += ["ServicesList" => array('Service'=> $pet_services_result)];
                            }
                        }

                        // if($importantKeys['Channel'] == '5'){
                        //     //исключаем ручные по КДИ и ЛИ
                        //     $pet_services_ = VisitsGovServices::find()
                        //     ->select([
                        //         'gov_services.name AS name',
                        //         'gov_services.id AS id',
                        //         'gov_services.id_service_type AS type',
                        //     ])
                        //     ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                        //     ->where(['id_visit' => $importantKeys['id_visit']])
                        //     ->asArray()
                        //     ->all();

                        //     $flag=1;
                        //     foreach ($pet_services_ as $pet_service){
                        //         if($pet_service['type'] == 5 || $pet_service['type'] == 4){
                        //             $flag=0;
                        //         }
                        //     }
                        //     if($flag){
                        //         array_push($pets_regs_array, $el_pet_filter);
                        //     }
                        // }else{

                            if($importantKeys['Status'] == 'N'){
                                //СПЕЦИАЛЬНАЯ ПРОВЕРКА ДЛЯ НОВЫХ ПРИЁМОВ
                                //Проверяем дату
                                $slot_date_time = new \DateTime($importantKeys['slot_date'].' '.$importantKeys['slot_time']);
                                $current_date_time = new \DateTime;
                                if($slot_date_time > $current_date_time){
                                    array_push($pets_regs_array, $el_pet_filter);
                                }
                            }else{
                                array_push($pets_regs_array, $el_pet_filter);
                            }
                        // }
                    }

                    //if($arr_pets[$i]['ext_id'] == '' && count($pets_regs_array) == 0){
                    //убираем пока не готовы предложки
                    //
                    if($arr_pets[$i]['ext_id'] == ''){
                        //Убираем животное из списка
                        //unset($arr_pets[$i]);
                        $arr_pets[$i]='';
                    }else{
                        //Данные по животному
                        $arr_pets[$i]['IntId'] = $arr_pets[$i]['id'];unset($arr_pets[$i]['id']);
                        $arr_pets[$i]['NickName'] = $arr_pets[$i]['name'];unset($arr_pets[$i]['name']);
                        $arr_pets[$i]['Sex'] = $arr_pets[$i]['sex'];unset($arr_pets[$i]['sex']);
                        $arr_pets[$i]['Breed'] = $arr_pets[$i]['breed'];unset($arr_pets[$i]['breed']);
                        $arr_pets[$i]['Species'] = $arr_pets[$i]['species'];unset($arr_pets[$i]['species']);
                        $arr_pets[$i]['Birthday'] = $arr_pets[$i]['birthday'];unset($arr_pets[$i]['birthday']);
                        $arr_pets[$i]['City'] = $arr_pets[$i]['city'];unset($arr_pets[$i]['city']);
                        $arr_pets[$i]['Street'] = $arr_pets[$i]['street'];unset($arr_pets[$i]['street']);
                        $arr_pets[$i]['House'] = $arr_pets[$i]['house'];unset($arr_pets[$i]['house']);
                        $arr_pets[$i]['Room'] = $arr_pets[$i]['room'];unset($arr_pets[$i]['room']);
                        $arr_pets[$i]['IdentificationCode'] = $arr_pets[$i]['identification'];unset($arr_pets[$i]['identification']);
                        $arr_pets[$i]['IdentificationType'] = $arr_pets[$i]['ident_type'];unset($arr_pets[$i]['ident_type']);
                        $arr_pets[$i]['PetId'] = $arr_pets[$i]['ext_id'];unset($arr_pets[$i]['ext_id']);
                        //Данные по животному

                        $arr_pets[$i] += ['RegistrationsList' => $pets_regs_array];
                        $arr_pets[$i] += ['VaccinationsList' => $pet_vac_array];
                        $arr_pets[$i] += ['EctoparasitesList' => $pet_ecto_array];
                    }
                }

                $resultPets = ['Pet'=>$arr_pets ];
                $resultPetsList = ['PetsListFull' => $resultPets ]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящий параметр SSO ID является обязательным.', 404);

            return false;
        }
    }

    public function getPetListFull2($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;

        if($SsoId){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = \Yii::$app->db->createCommand('SELECT id_owner FROM elk.owners WHERE sso_id=\''.$SsoId.'\';')->queryOne();
            //$owner = PetOwners::findOne(['sso_id' => $SsoId]);
            //if($owner){
            if($owner['id_owner']){
                $query = Pets::find()
                    ->select([
                        'public.pets.id',
                        'public.pets.name',
                        'public.pets.sex',
                        'public.pets.birthday',
                        'public.pets.id_breed AS breed',
                        'public.pets.id_species AS species',
                        'elk.pets.ext_id AS ext_id',
                        'public.fias_addresses.city AS city',
                        'public.fias_addresses.street AS street',
                        'public.fias_addresses.house AS house',
                        'public.fias_addresses.room AS room',
                        'public.pet_identification.identification_code AS identification',
                        'public.pet_identification.id_ident_type AS ident_type',
                        //'public.breeds.name AS breed',
                    ])

                    ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                    ->leftJoin('public.fias_addresses', 'public.pets.id_fias_address=public.fias_addresses.id')
                    ->leftJoin('public.pet_identification', 'public.pets.id=public.pet_identification.id_pet AND pet_identification.main_flag=true')
                    ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')

                    ->with('last_pet_rabies_vaccinations')
                    ->with(['pet_rabies_vaccinations' => function ($query) {
                        $query->select([
                            'pet_rabies_vaccination.id',
                            'pet_rabies_vaccination.id_pet',
                            'pet_rabies_vaccination.id_vaccine',
                            'pet_rabies_vaccination.drug_name',
                            'pet_rabies_vaccination.producer_name',
                            'pet_rabies_vaccination.batch',
                            'pet_rabies_vaccination.production_date',
                            'pet_rabies_vaccination.expiry_date',
                            'pet_rabies_vaccination.date',
                            'pet_rabies_vaccination.valid_until',
                            'pet_rabies_vaccination.id_organization',
                            'pet_rabies_vaccination.id_specialist',
                        ]);
                    }])
                    ->with(['pet_ectoparasites' => function ($query) {
                        $query->select([
                            'pet_ectoparasites.id',
                            'pet_ectoparasites.id_pet',
                            'pet_ectoparasites.id_drug',
                            'pet_ectoparasites.drug_name',
                            'pet_ectoparasites.producer_name',
                            'pet_ectoparasites.date',
                            'pet_ectoparasites.id_organization',
                            'pet_ectoparasites.id_specialist',
                        ]);
                    }])
                    ->with(['pet_other_vaccinations' => function ($query) {
                        $query->select([
                            'pet_other_vaccinations.id',
                            'pet_other_vaccinations.id_pet',
                            'pet_other_vaccinations.id_vaccine',
                            'pet_other_vaccinations.drug_name',
                            'pet_other_vaccinations.producer_name',
                            'pet_other_vaccinations.batch',
                            'pet_other_vaccinations.production_date',
                            'pet_other_vaccinations.expiry_date',
                            'pet_other_vaccinations.date',
                            'pet_other_vaccinations.valid_until',
                            'pet_other_vaccinations.id_organization',
                            'pet_other_vaccinations.id_specialist',
                        ]);
                    }])


                    ->where(['public.pets_to_owner.id_owner' => $owner['id_owner']])
                    //->andWhere(['IS NOT', 'elk.pets.ext_id', null]);
                    ->andWhere(['AND',
                        ['public.pets.mosru'=>'true'],
                        ['public.pets_to_owner.id_owner_type'=>'1'],
                    ]);
                //->leftJoin('public.breeds', 'public.pets.id_breed=public.breeds.id')
                $arr_pets = $query->asArray()->all();

                for ($i = 0; $i < count($arr_pets); $i++) {
                    $pet_visits = Visits::find()
                    ->select([
                        'visits.*',
                        'visits.id AS id_visit',
                        'visit_pets.*',
                        'visits_gov_services.id_service',
                        'services.name AS name_service',
                        'visits.channel AS Channel',

                        'visits.status AS Status',

                        '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                        '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                        'organizations.name AS Organization',
                        'organizations.latitude AS Latitude',
                        'organizations.longitude AS Longitude',
                        'addresses.name AS Address',
                        'visits.id AS Id',
                        'visits.ticket_number AS TicketNumber',
                        'visits.duration AS Duration',
                        'users.fullname AS FullNameDoctor',
                        'users.f_fio AS FNameDoctor',
                        'users.i_fio AS INameDoctor',
                        'users.o_fio AS ONameDoctor',
                        'elk.pets.ext_id AS ext_id',
                    ])
                    //->where(["visit_pets.id_pet" =>$arr_pets[$i]['IntId']])//
                    ->where(["visit_pets.id_pet" =>$arr_pets[$i]['id']])
                    ->andWhere(['OR',
                        ['visits.status'=>'N'],
                        ['visits.status'=>'F']
                    ])
                    ->andFilterWhere(['>=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i", strtotime('-3 years'))])//Ограничение по времени!!!
                    ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                    ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                    ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                    ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                    ->leftJoin('users', 'specialists.id_user=users.id')
                    ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                    ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                    ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                    ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                    ->asArray()
                    ->orderBy('LOWER(VISITS.TIME_RANGE)::date DESC')
                    ->all();

                    $pet_vac_ecto = Pets::find()
                    ->with('pet_rabies_vaccinations')
                    ->with('pet_other_vaccinations')
                    ->with('pet_ectoparasites')
                    ->with('last_pet_rabies_vaccinations')
                    //->where(['id' => $arr_pets[$i]['IntId']])
                    ->where(['id' => $arr_pets[$i]['id']])
                    ->asArray()
                    ->all();

                    // Добавляем статусы вакцинации
                    // <!--0-Вакцинация просрочена; 1-До окончания вакцинации 30 дней; 2-Вакцинирован-->

                    $today = date("Y-m-d");
                    $check_time = $pet_vac_ecto[0]['last_pet_rabies_vaccinations']['valid_until'];

                    $diference = strtotime($check_time) - strtotime($today); // разница между двумя датами в секундах
                    $days = $diference / 86400; // секунды в сутках

                    $vaccinationStatus = '0';
                    if($pet_vac_ecto[0]['last_pet_rabies_vaccinations']['valid_until']){
                        if($days > 30){
                            $vaccinationStatus = '2';
                        }
                        if($days < 30 && $days > 0){
                            $vaccinationStatus = '1';
                        }
                        if($days < 0){
                            $vaccinationStatus = '0';
                        }
                    }

                    $arr_pets[$i] += ['VaccinationStatus' => $vaccinationStatus];

                    //Вакцинации от бешенства + остальные
                    $pet_vac_array = array();
                    foreach ($pet_vac_ecto[0]['pet_rabies_vaccinations'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                            if ($key === "producer_name") {
                                if($importantKeys[$key]){
                                    $element += ['ProducerName' => $importantKeys[$key]];
                                }else{
                                    $element += ['ProducerName' => ''];
                                }
                            }
                            if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}

                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    if($importantKeys['mosru_organization']){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $element += ['Organization' => ''];
                                    }
                                    $element += ['Editable' => '1'];
                                }else{
                                    $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    $element += ['Organization' => $org['name']];
                                    $element += ['Editable' => '0'];
                                }
                            }

                            if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}

                            $element += ['Type' => '1'];//Бешенство

                            //GUID
                            $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['id'].' AND entity_type=\'vac-rab-mos-ru\';')->queryOne();
                            if($file['hash']){
                                $element += ['GUID' => $file['hash']];
                            }else{
                                $element += ['GUID' => ''];
                            }
                            //GUID
                        }
                        array_push($pet_vac_array, $element);
                    }
                    foreach ($pet_vac_ecto[0]['pet_other_vaccinations'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}

                            if ($key === "producer_name") {
                                if($importantKeys[$key]){
                                    $element += ['ProducerName' => $importantKeys[$key]];
                                }else{
                                    $element += ['ProducerName' => ''];
                                }
                            }

                            if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}

                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    if($importantKeys['mosru_organization']){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $element += ['Organization' => ''];
                                    }
                                    $element += ['Editable' => '1'];
                                }else{
                                    $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    $element += ['Organization' => $org['name']];
                                    $element += ['Editable' => '0'];
                                }
                            }

                            if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}

                            $element += ['Type' => '0'];//Остальное

                            //GUID
                            $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['id'].' AND entity_type=\'vac-other-mos-ru\';')->queryOne();
                            if($file['hash']){
                                $element += ['GUID' => $file['hash']];
                            }else{
                                $element += ['GUID' => ''];
                            }
                            //GUID
                        }
                        array_push($pet_vac_array, $element);
                    }
                    //Вакцинации от бешенства + остальные

                    //Обработки от эктопаразитов
                    $pet_ecto_array = array();
                    foreach ($pet_vac_ecto[0]['pet_ectoparasites'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                            if ($key === "producer_name") {
                                if($importantKeys[$key]){
                                    $element += ['ProducerName' => $importantKeys[$key]];
                                }else{
                                    $element += ['ProducerName' => ''];
                                }
                            }
                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    if($importantKeys['mosru_organization']){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $element += ['Organization' => ''];
                                    }
                                    $element += ['Editable' => '1'];
                                }else{
                                    $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    $element += ['Organization' => $org['name']];
                                    $element += ['Editable' => '0'];
                                }
                            }
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}
                        }
                        array_push($pet_ecto_array, $element);
                    }
                    //Обработки от эктопаразитов//

                    $pets_regs_array = array();
                    //$pets_regs_flag=0;
                    foreach ($pet_visits as &$value) {
                        $importantKeys = &$value;
                        $el_pet_filter = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "Id") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Organization") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Address") {
                                if($importantKeys[$key]){
                                    $el_pet_filter += [$key => $importantKeys[$key]];
                                }else{
                                    $el_pet_filter += [$key => ''];
                                }
                            }
                            if ($key === "Latitude") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Longitude") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "Channel") {
                                $el_pet_filter += [$key => $importantKeys[$key]];

                                if($importantKeys[$key] == 5){
                                    $el_pet_filter += ['Editable' => '1'];

                                    if($importantKeys['mosru_organization']){
                                        $el_pet_filter += ['Organization' => $importantKeys['mosru_organization']];
                                    }else{
                                        $el_pet_filter += ['Organization' => ''];
                                    }

                                    if($importantKeys['mosru_address']){
                                        $el_pet_filter += ['Address' => $importantKeys['mosru_address']];
                                    }else{
                                        $el_pet_filter += ['Address' => ''];
                                    }

                                    if($importantKeys['mosru_specialist']){
                                        $el_pet_filter += ['FullNameDoctor' => $importantKeys['mosru_specialist']];
                                    }else{
                                        $el_pet_filter += ['FullNameDoctor' => ''];
                                    }
                                }else{
                                    $el_pet_filter += ['Editable' => '0'];
                                }

                                $file = \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['Id'].' AND entity_type=\'visits-mos-ru\';')->queryOne();
                                if($file['hash']){
                                    $el_pet_filter += ['GUID' => $file['hash']];
                                }else{
                                    $el_pet_filter += ['GUID' => ''];
                                }
                            }
                            if ($key === "TicketNumber") {$el_pet_filter += [$key => $importantKeys[$key]];}

                            if ($key === "Duration") {$el_pet_filter += [$key => $importantKeys[$key]];}
                            if ($key === "FullNameDoctor") {
                                //$el_pet_filter += [$key => $importantKeys[$key]];
                                $el_pet_filter += [$key => $importantKeys['FNameDoctor'].','.$importantKeys['INameDoctor'].','.$importantKeys['ONameDoctor']];
                            }
                            if ($key === "slot_date") {$el_pet_filter += ["Date" => $importantKeys[$key]];}
                            if ($key === "slot_time") {$el_pet_filter += ["Slot" => $importantKeys[$key]];}
                            if ($key === "id_service") {
                                $id_visit_gov = $importantKeys['id_visit'];

                                $pet_services_ = VisitsGovServices::find()
                                ->select([
                                    'gov_services.name AS name',
                                    'gov_services.id AS id',
                                    'gov_services.id_service_type AS type',
                                ])
                                ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                                ->where(['id_visit' => $id_visit_gov])
                                ->asArray()
                                ->all();

                                $pet_services_result = [];
                                foreach ($pet_services_ as $pet_service){
                                    $pet_services_result[] = ['ServiceId' => $pet_service['id'], 'ServiceName' => $pet_service['name'], 'ServiceType' => $pet_service['type']];
                                }
                                $el_pet_filter += ["ServicesList" => array('Service'=> $pet_services_result)];
                            }
                        }

                        // if($importantKeys['Channel'] == '5'){
                        //     //исключаем ручные по КДИ и ЛИ
                        //     $pet_services_ = VisitsGovServices::find()
                        //     ->select([
                        //         'gov_services.name AS name',
                        //         'gov_services.id AS id',
                        //         'gov_services.id_service_type AS type',
                        //     ])
                        //     ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                        //     ->where(['id_visit' => $importantKeys['id_visit']])
                        //     ->asArray()
                        //     ->all();

                        //     $flag=1;
                        //     foreach ($pet_services_ as $pet_service){
                        //         if($pet_service['type'] == 5 || $pet_service['type'] == 4){
                        //             $flag=0;
                        //         }
                        //     }
                        //     if($flag){
                        //         array_push($pets_regs_array, $el_pet_filter);
                        //     }
                        // }else{

                            if($importantKeys['Status'] == 'N'){
                                //СПЕЦИАЛЬНАЯ ПРОВЕРКА ДЛЯ НОВЫХ ПРИЁМОВ
                                //Проверяем дату
                                $slot_date_time = new \DateTime($importantKeys['slot_date'].' '.$importantKeys['slot_time']);
                                $current_date_time = new \DateTime;
                                if($slot_date_time > $current_date_time){
                                    array_push($pets_regs_array, $el_pet_filter);
                                }
                            }else{
                                array_push($pets_regs_array, $el_pet_filter);
                            }
                        // }
                    }

                    if($arr_pets[$i]['ext_id'] == '' && count($pets_regs_array) == 0){
                        //Убираем животное из списка
                        //unset($arr_pets[$i]);
                        $arr_pets[$i]='';
                    }else{
                        //Данные по животному
                        $arr_pets[$i]['IntId'] = $arr_pets[$i]['id'];unset($arr_pets[$i]['id']);
                        $arr_pets[$i]['NickName'] = $arr_pets[$i]['name'];unset($arr_pets[$i]['name']);
                        $arr_pets[$i]['Sex'] = $arr_pets[$i]['sex'];unset($arr_pets[$i]['sex']);
                        $arr_pets[$i]['Breed'] = $arr_pets[$i]['breed'];unset($arr_pets[$i]['breed']);
                        $arr_pets[$i]['Species'] = $arr_pets[$i]['species'];unset($arr_pets[$i]['species']);
                        $arr_pets[$i]['Birthday'] = $arr_pets[$i]['birthday'];unset($arr_pets[$i]['birthday']);
                        $arr_pets[$i]['City'] = $arr_pets[$i]['city'];unset($arr_pets[$i]['city']);
                        $arr_pets[$i]['Street'] = $arr_pets[$i]['street'];unset($arr_pets[$i]['street']);
                        $arr_pets[$i]['House'] = $arr_pets[$i]['house'];unset($arr_pets[$i]['house']);
                        $arr_pets[$i]['Room'] = $arr_pets[$i]['room'];unset($arr_pets[$i]['room']);
                        $arr_pets[$i]['IdentificationCode'] = $arr_pets[$i]['identification'];unset($arr_pets[$i]['identification']);
                        $arr_pets[$i]['IdentificationType'] = $arr_pets[$i]['ident_type'];unset($arr_pets[$i]['ident_type']);
                        $arr_pets[$i]['PetId'] = $arr_pets[$i]['ext_id'];unset($arr_pets[$i]['ext_id']);
                        //Данные по животному

                        $arr_pets[$i] += ['RegistrationsList' => $pets_regs_array];
                        $arr_pets[$i] += ['VaccinationsList' => $pet_vac_array];
                        $arr_pets[$i] += ['EctoparasitesList' => $pet_ecto_array];
                    }
                }

                $resultPets = ['Pet'=>$arr_pets ];
                $resultPetsList = ['PetsListFull' => $resultPets ]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящий параметр SSO ID является обязательным.', 404);

            return false;
        }
    }

    //Ectoparasites
    public function getPetEctoparasites($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        if(!$SsoId || !$PetId) throw new NotFoundHttpException(json_encode(['error' => 'SSOID и PetId - обязательные параметры']));
        $rec = \Yii::$app->db->createCommand('SELECT id_owner FROM elk.owners WHERE sso_id=\''.$SsoId.'\';')->queryOne();
        if (!$rec) throw new NotFoundHttpException(json_encode(['error' => 'Владелец не найден']));
        $query = Pets::find()
        ->select([
            'public.pets.id  AS id',
            'elk.pets.ext_id AS ext_id',
        ])
        ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
        ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
        ->where(['public.pets_to_owner.id_owner' => $rec['id_owner']])
        ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

        $arr_pets = $query->asArray()->all();

        if (!sizeof($arr_pets)) throw new NotFoundHttpException(json_encode(['error' => 'Питомец не найден']));

        $pet_=1;
        for ($i = 0; $i < count($arr_pets); $i++) {
            if($arr_pets[$i]['ext_id'] == $PetId){
                $pet_=0;
            }
        }
        if($pet_) throw new NotFoundHttpException(json_encode(['error' => 'Питомец не найден']));

        $pet_ecto_array = array();
        for ($i = 0; $i < count($arr_pets); $i++) {
            if($arr_pets[$i]['ext_id'] == $PetId){//найденное животное
                $pet_vac_ecto = Pets::find()
                ->with('pet_ectoparasites')
                ->where(['id' => $arr_pets[$i]['id']])
                ->asArray()
                ->all();

                foreach ($pet_vac_ecto[0]['pet_ectoparasites'] as &$value) {
                    $importantKeys = &$value;
                    $element = [];
                    foreach(array_keys($importantKeys) as $key){
                        if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                        if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                        if ($key === "id_organization") {
                            if($importantKeys[$key] == ''){
                                if($importantKeys['mosru_organization']){
                                    $element += ['Organization' => $importantKeys['mosru_organization']];
                                }else{
                                    $element += ['Organization' => ''];
                                }

                                $element += ['Editable' => '1'];
                            }else{
                                $element += ['Organization' => $importantKeys[$key]];
                                $element += ['Editable' => '0'];
                            }
                        }
                        if ($key === "producer_name") {$element += ['ProducerName' => $importantKeys[$key]];}
                        if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}
                    }
                    array_push($pet_ecto_array, $element);
                }
            }
        }

        $resultPetsList = ['EctoparasitesList' => $pet_ecto_array]; //отправляем в soap

        return $resultPetsList;
    }

    public function postPetEctoparasites($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $DrugName = isset($ServiceInfo->DrugName) ? $ServiceInfo->DrugName : null;
        $Date = isset($ServiceInfo->Date) ? $ServiceInfo->Date : null;
        $ProducerName = isset($ServiceInfo->ProducerName) ? $ServiceInfo->ProducerName : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;

        if(
            $SsoId &&
            $PetId &&
            $DrugName &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            // $ServiceInfo->SsoId
            // $ServiceInfo->PetId
            // $ServiceInfo->DrugName
            // $ServiceInfo->ProducerName
            // $ServiceInfo->Date
            // $ServiceInfo->Organization

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id'] == ''){
                //пробуем залезть напрямую в таблицу
                $rec = \Yii::$app->db->createCommand('SELECT id_owner FROM elk.owners WHERE sso_id=\''.$SsoId.'\';')->queryOne();
                if($rec['id_owner'] != ''){
                    $owner = PetOwners::findOne(['id' => $rec['id_owner']]);
                }
            }
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $query='INSERT INTO pet_ectoparasites ';
                $query.='(mosru_organization, drug_name, producer_name, date, type_tmc, id_pet, created_at, updated_at) ';
                $query.='VALUES (';
                if($Organization){
                    $query.='\''.$Organization.'\',';###
                }else{
                    $query.='NULL,';
                }
                $query.='\''.$DrugName.'\',';
                if($ProducerName){
                    $query.='\''.$ProducerName.'\',';
                }else{
                    $query.='NULL,';
                }
                $query.='\''.$Date.'\',';
                $query.='\'drug\',';
                $query.='\''.$id_pet.'\',';
                $query.='NOW()::timestamp(0),';
                $query.='NOW()::timestamp(0)';
                $query.=');';

                \Yii::$app->db->createCommand($query)->execute();
                $IdPetEctoparasites = \Yii::$app->db->getLastInsertID();

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$IdPetEctoparasites.' по обработке от эктопаразитов успешно добавлена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID, DrugName и Date являются обязательными.', 404);

            return false;
        }
    }

    public function putPetEctoparasites($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $DrugName = isset($ServiceInfo->DrugName) ? $ServiceInfo->DrugName : null;
        $ProducerName = isset($ServiceInfo->ProducerName) ? $ServiceInfo->ProducerName : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $Date = isset($ServiceInfo->Date) ? $ServiceInfo->Date : null;

        if(
            $SsoId &&
            $PetId &&
            $RecId &&
            $DrugName &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            //$SsoId
            //$PetId
            //$RecId
            //$DrugName
            //$ProducerName
            //$Date
            //$Organization

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_ectoparasites WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' по обработке от эктопаразитов не найдена.', 404);
                    return false;
                }

                if($rec['id_drug'] && $rec['id_organization'] && $rec['id_specialist']){
                    throw new HttpException(200, 'Запись #'.$RecId.' по обработке от эктопаразитов не может быть изменена.', 404);
                    return false;
                }

                $query='UPDATE pet_ectoparasites SET ';
                if($Organization){
                    $query.='mosru_organization=\''.$Organization.'\',';
                }else{
                    $query.='mosru_organization=NULL,';
                }
                $query.='drug_name=\''.$DrugName.'\',';
                $query.='producer_name=\''.$ProducerName.'\',';
                $query.='date=\''.$Date.'\',';
                $query.="updated_at=NOW()::timestamp(0)";#updated_at
                $query.=' WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' по обработке от эктопаразитов успешно изменена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID, DrugName и Date являются обязательными.', 404);

            return false;
        }
    }

    public function deletePetEctoparasites($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;

        if(
            $SsoId &&
            $PetId &&
            $RecId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_ectoparasites WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' по обработке от эктопаразитов не найдена.', 404);
                    return false;
                }

                if($rec['id_drug'] && $rec['id_organization'] && $rec['id_specialist']){
                    throw new HttpException(200, 'Запись #'.$RecId.' по обработке от эктопаразитов не может быть удалена.', 404);
                    return false;
                }

                $query='DELETE FROM pet_ectoparasites WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' по обработке от эктопаразитов успешно удалена.'
                );
                return $info;

            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID и IdEctoparasite являются обязательными.', 404);

            return false;
        }
    }
    //Ectoparasites

    public function infoblockServicesList($ServiceInfo){
        $query='SELECT gov_services.id AS id, gov_services.name AS name, gov_services.price AS price, gov_services.id_service_type AS type FROM public.gov_services ';
        $query.='WHERE gov_services.id=663 OR gov_services.id=255 OR gov_services.id=569';
        $pet_services_ = \Yii::$app->db->createCommand($query)->queryAll();
        
        $pet_services_result = [];
        foreach ($pet_services_ as $pet_service){
            $pet_services_result[] = ['Id' => $pet_service['id'], 'Type' => $pet_service['type'], 'Name' => $pet_service['name'], 'Price' => $pet_service['price']];
        }

        return ["ServicesList" => array('Service'=> $pet_services_result)];
    }

    //Visits
    public function getPetVisitsHistory($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        
        if($SsoId){
            $this->reportSoapMethod(__FUNCTION__);

            // RegistrationsList	[array]	нет	Массив  (будут выбираться только прошедшие приемы)
            // Registration	[object]	нет	Приемы
            // Date	date	нет	Дата приема дд.мм.гггг
            // Slot	date	нет	Слот времени hh:mm
            // Duration	integer	нет	Длительность
            // NameClinic	string	нет	Наименование клиники
            // FullNameDoctor	string	нет	ФИО врача
            // TicketNumber	string	нет	Номер талона
            // channel	integer	нет	Канал записи
            // Services	[object]	нет	Массив услуг
            // Id	string	нет	Код услуги

            $owner = \Yii::$app->db->createCommand('SELECT id_owner FROM elk.owners WHERE sso_id=\''.$SsoId.'\';')->queryOne();

            if($owner['id_owner']){
                $query = Pets::find()
                ->select([
                    'public.pets.name',
                    'public.pets.id_breed AS breed',
                    'public.pets.id_species AS species_id',
                    'public.species.name AS species_name',
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])
                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->leftJoin('public.species', 'public.species.id=public.pets.id_species')
                ->where(['public.pets_to_owner.id_owner' => $owner['id_owner']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pets_regs_array = array();
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id']){//для животных с PET ID
                        $visit = Visits::find()
                        ->select([
                            'visits.*',
                            'visit_pets.*',
                            'visits_gov_services.id_service',
                            'services.name AS name_service',
                            'visits.channel AS Channel',
                            '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                            '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                            'organizations.id AS OrganizationId',
                            'organizations.name AS Organization',
                            'organizations.latitude AS Latitude',
                            'organizations.longitude AS Longitude',
                            'addresses.name AS Address',
                            'visits.id AS Id',
                            'visits.ticket_number AS TicketNumber',
                            'visits.duration AS Duration',
                            'specialists.id AS SpecialistId',
                            'users.fullname AS FullNameDoctor',
                            'users.f_fio AS FNameDoctor',
                            'users.i_fio AS INameDoctor',
                            'users.o_fio AS ONameDoctor',
                            'elk.pets.ext_id AS ext_id',
                        ])
                        ->where(["visit_pets.id_pet" =>$arr_pets[$i]['id']])
                        ->andWhere(['or',
                            ['visits.status'=>'F']
                        ])
                        ->andFilterWhere(['<=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i")])//Ограничение по времени
                        ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                        ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                        ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                        ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                        ->leftJoin('users', 'specialists.id_user=users.id')
                        ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                        ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                        ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                        ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                        ->asArray()
                        ->orderBy('LOWER(VISITS.TIME_RANGE)::date DESC')
                        ->all();

                        foreach ($visit as &$value) {
                            if(isset($visit)){
                                $importantKeys = &$value;
                                $element = [];
                                foreach(array_keys($importantKeys) as $key){
                                    if($importantKeys["Channel"] != 5){
                                        $element += ["Animal" => ["PetId" => $arr_pets[$i]['ext_id'], "NicknameAnimal" => $arr_pets[$i]['name'], "SpeciesId" => $arr_pets[$i]['species_id'], "SpeciesName" => $arr_pets[$i]['species_name']]];
                                        if ($key === "slot_date") {$element += ["Date" => $importantKeys[$key]];}
                                        if ($key === "slot_time") {$element += ["Slot" => $importantKeys[$key]];}
                                        if ($key === "id_service") {
                                            $id_visit_gov = $importantKeys['id_visit'];
        
                                            $pet_services_ = VisitsGovServices::find()
                                            ->select([
                                                'gov_services.id AS id',
                                                'gov_services.name AS name',
                                                'gov_services.id_service_type AS type',
                                            ])
                                            ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                                            ->where(['id_visit' => $id_visit_gov])
                                            ->asArray()
                                            ->all();
        
                                            $pet_services_result = [];
                                            foreach ($pet_services_ as $pet_service){
                                                $pet_services_result[] = ['ServiceId' => $pet_service['id'], 'ServiceType' => $pet_service['type'], 'ServiceName' => $pet_service['name']];
                                            }
                                            $element += ["ServicesList" => array('Service'=> $pet_services_result)];
                                        }
                                        $element += ["Organization" => ["Id" => $importantKeys['OrganizationId'], "Name" => $importantKeys['Organization'], "Address" => $importantKeys['Address'], "Latitude" => $importantKeys['Latitude'], "Longitude" => $importantKeys['Longitude']]];
                                        $element += ["Specialist" => ["Id" => $importantKeys['SpecialistId'], "Name" => $importantKeys['FullNameDoctor']]];
                                    }
                                }
                                if($element){
                                    array_push($pets_regs_array, $element);
                                }
                            }
                        }
                    }
                }

                $resultPetsList = ['RegistrationsList' => $pets_regs_array ]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }

    public function getPetVisitsPast($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;

        if(
            $SsoId &&
            $PetId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            // RegistrationsList	[array]	нет	Массив  (будут выбираться только прошедшие приемы)
            // Registration	[object]	нет	Приемы
            // Date	date	нет	Дата приема дд.мм.гггг
            // Slot	date	нет	Слот времени hh:mm
            // Duration	integer	нет	Длительность
            // NameClinic	string	нет	Наименование клиники
            // FullNameDoctor	string	нет	ФИО врача
            // TicketNumber	string	нет	Номер талона
            // channel	integer	нет	Канал записи
            // Services	[object]	нет	Массив услуг
            // Id	string	нет	Код услуги

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $pets_regs_array = array();
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){//найденное животное
                        $pet = Visits::find()
                        ->select([
                            'visits.*',
                            'visit_pets.*',
                            'visits_gov_services.id_service',
                            'services.name AS name_service',
                            'visits.channel AS Channel',
                            '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                            '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                            'organizations.name AS Organization',
                            'organizations.latitude AS Latitude',
                            'organizations.longitude AS Longitude',
                            'addresses.name AS Address',
                            'visits.id AS Id',
                            'visits.ticket_number AS TicketNumber',
                            'visits.duration AS Duration',
                            'users.fullname AS FullNameDoctor',
                            'users.f_fio AS FNameDoctor',
                            'users.i_fio AS INameDoctor',
                            'users.o_fio AS ONameDoctor',
                            'elk.pets.ext_id AS ext_id',
                        ])
                        ->where(["visit_pets.id_pet" =>$arr_pets[$i]['id']])
                        ->andWhere(['or',
                            ['visits.status'=>'F']
                        ])
                        ->andFilterWhere(['<=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i")])//Ограничение по времени
                        ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                        ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                        ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                        ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                        ->leftJoin('users', 'specialists.id_user=users.id')
                        ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                        ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                        ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                        ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                        ->asArray()
                        ->orderBy('LOWER(VISITS.TIME_RANGE)::date DESC')
                        ->all();

                        //$arr_pets[$i]['Id'] = $arr_pets[$i]['ext_id'];unset($arr_pets[$i]['ext_id']);

                        foreach ($pet as &$value) {
                            $importantKeys = &$value;
                            $element = [];
                            foreach(array_keys($importantKeys) as $key){
                                if ($key === "Id") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Organization") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Address") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Latitude") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Longitude") {$element += [$key => $importantKeys[$key]];}

                                if ($key === "Channel") {
                                    $element += [$key => $importantKeys[$key]];

                                    if($importantKeys[$key] == 5){
                                        $element += ['Editable' => '1'];
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                        $element += ['Address' => $importantKeys['mosru_address']];
                                        $element += ['FullNameDoctor' => $importantKeys['mosru_specialist']];
                                    }else{
                                        $element += ['Editable' => '0'];
                                    }

                                    $file = \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['Id'].' AND entity_type=\'visits-mos-ru\';')->queryOne();
                                    if($file['hash']){
                                        $element += ['GUID' => $file['hash']];
                                    }else{
                                        $element += ['GUID' => ''];
                                    }

                                }

                                if ($key === "TicketNumber") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "Duration") {$element += [$key => $importantKeys[$key]];}
                                if ($key === "FullNameDoctor") {
                                    //$element += [$key => $importantKeys[$key]];
                                    $element += [$key => $importantKeys['FNameDoctor'].','.$importantKeys['INameDoctor'].','.$importantKeys['ONameDoctor']];
                                }
                                if ($key === "slot_date") {$element += ["Date" => $importantKeys[$key]];}
                                if ($key === "slot_time") {$element += ["Slot" => $importantKeys[$key]];}
                                if ($key === "id_service") {
                                    $id_visit_gov = $importantKeys['id_visit'];

                                    $pet_services_ = VisitsGovServices::find()
                                    ->select([
                                        'gov_services.id AS id',
                                        'gov_services.name AS name',
                                        'gov_services.id_service_type AS type',
                                    ])
                                    ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                                    ->where(['id_visit' => $id_visit_gov])
                                    ->asArray()
                                    ->all();

                                    $pet_services_result = [];
                                    foreach ($pet_services_ as $pet_service){
                                        $pet_services_result[] = ['ServiceId' => $pet_service['id'], 'ServiceType' => $pet_service['type'], 'ServiceName' => $pet_service['name']];
                                    }
                                    $element += ["ServicesList" => array('Service'=> $pet_services_result)];
                                }
                            }
                            // if($importantKeys['Channel'] == '5'){
                            //     //исключаем ручные по КДИ и ЛИ
                            //     $pet_services_ = VisitsGovServices::find()
                            //     ->select([
                            //         'gov_services.name AS name',
                            //         'gov_services.id AS id',
                            //         'gov_services.id_service_type AS type',
                            //     ])
                            //     ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                            //     ->where(['id_visit' => $importantKeys['id_visit']])
                            //     ->asArray()
                            //     ->all();

                            //     $flag=1;
                            //     foreach ($pet_services_ as $pet_service){
                            //         if($pet_service['type'] == 5 || $pet_service['type'] == 4){
                            //             $flag=0;
                            //         }
                            //     }
                            //     if($flag){
                            //         array_push($pets_regs_array, $el_pet_filter);
                            //     }
                            // }else{
                                array_push($pets_regs_array, $element);
                            // }
                        }
                    }
                }

                $resultPetsList = ['RegistrationsList' => $pets_regs_array ]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }

    public function getPetVisitsPrint($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        if (!$SsoId || !$RecId) throw new BadRequestException('Входящие параметры SSO ID и REC ID являются обязательными.', 400);
        $this->reportSoapMethod(__FUNCTION__);
        $owner = PetOwners::findOne(['sso_id' => $SsoId]);
        if (!$owner) throw new NotFoundHttpException("Владелец SSO ID $SsoId не найден.", 404);
        $visit = \Yii::$app->db->createCommand("SELECT id, channel FROM visits WHERE id = $RecId AND id_owner = {$owner['id']}")->queryOne();
        if (!$visit) throw new NotFoundHttpException("Приём #$RecId не найден.", 404);
        $controller = new DescriptionsController('descriptions', \Yii::$app->module);
        $res = $controller->actionFile($visit['id'], 'A4', true);
        $fileHash = $visit['channel'] == 5 ? \Yii::$app->db->createCommand("SELECT hash FROM files WHERE entity_id=$RecId AND entity_type='visits-mos-ru'")->queryOne()['hash'] : '';
        return [
            'GUID' => $fileHash,
            'ServiceLink' => \Yii::$app->params['url_api'] . "{$res['result']['url']}"
        ];
    }
    
    public function postPetVisits($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $Date = isset($ServiceInfo->Date) ? $ServiceInfo->Date : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $Address = isset($ServiceInfo->Address) ? $ServiceInfo->Address : null;
        $NameDoctor = isset($ServiceInfo->NameDoctor) ? $ServiceInfo->NameDoctor : null;
        $LastNameDoctor = isset($ServiceInfo->LastNameDoctor) ? $ServiceInfo->LastNameDoctor : null;
        $PatrNameDoctor = isset($ServiceInfo->PatrNameDoctor) ? $ServiceInfo->PatrNameDoctor : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        if(
            $SsoId &&
            $PetId &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            #$SsoId
            #$PetId
            #$Date
            #$Organization
            #$Address
            #$NameDoctor
            #$LastNameDoctor
            #$PatrNameDoctor
            #$GUID

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $query='INSERT INTO visits ';
                $query.='(';
                $query.='mosru_organization, ';
                $query.='mosru_address, ';
                $query.='mosru_specialist, ';

                $query.='status, ';#1
                $query.='is_paid, ';#2
                $query.='id_owner, ';#3
                $query.='id_pet, ';#4
                $query.='fact_start_dttm, ';#5
                $query.='fact_end_dttm, ';#6
                $query.='cooldown, ';#7
                $query.='time_range, ';#8
                $query.='time_range_without_cooldown, ';#8.1
                $query.='channel, ';#9
                $query.='duration, ';#10
                $query.='start_dttm, ';#11
                $query.='type, ';#12
                $query.='variety, ';#12.1
                $query.='created_at, ';#13
                $query.='updated_at';#14
                $query.=') ';
                $query.='VALUES (';
                if($Organization){
                    $query.='\''.$Organization.'\',';
                }else{
                    $query.='NULL,';
                }
                if($Address){
                    $query.='\''.$Address.'\',';
                }else{
                    $query.='NULL,';
                }
                if($LastNameDoctor || $NameDoctor || $PatrNameDoctor){
                    $query.='\''.$LastNameDoctor.','.$NameDoctor.','.$PatrNameDoctor.'\',';
                }else{
                    $query.='NULL,';
                }

                $query.='\'F\',';#1
                $query.='\'false\',';#2
                $query.=''.$owner['id'].',';#3
                $query.=''.$id_pet.',';#4
                $query.='\''.$Date.' 00:00:00\',';#5
                $query.='\''.$Date.' 00:00:00\',';#6
                $query.='\'0\',';#7
                $query.='tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';#8
                $query.='tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';#8.1
                $query.='\'5\',';#9 <- новый канал 5
                $query.='\'10\',';#10
                $query.='\''.$Date.' 00:00:00\',';#11
                $query.='\'VISIT\',';#12
                $query.='\'SINGLE\',';#12.1
                $query.='NOW()::timestamp(0),';#13
                $query.='NOW()::timestamp(0)';#14
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();
                $id_visit = \Yii::$app->db->getLastInsertID();

                #ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
                $query='INSERT INTO visit_pets ';
                $query.='(id_pet, id_visit, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="".$id_pet.",";
                $query.="".$id_visit.",";
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0)";#updated_at
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ ФАЙЛ К ПРИЁМУ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$id_visit.",";
                    $query.="'visits-mos-ru',";
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Приём #'.$id_visit.' успешно добавлен.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID и Date являются обязательными.', 404);

            return false;
        }
    }
    public function putPetVisits($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $Date = isset($ServiceInfo->Date) ? $ServiceInfo->Date : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $Address = isset($ServiceInfo->Address) ? $ServiceInfo->Address : null;
        $NameDoctor = isset($ServiceInfo->NameDoctor) ? $ServiceInfo->NameDoctor : null;
        $LastNameDoctor = isset($ServiceInfo->LastNameDoctor) ? $ServiceInfo->LastNameDoctor : null;
        $PatrNameDoctor = isset($ServiceInfo->PatrNameDoctor) ? $ServiceInfo->PatrNameDoctor : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        if(
            $SsoId &&
            $PetId &&
            $RecId &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            //$SsoId
            //$PetId
            //$Date
            //$Organization
            //$Address
            //$NameDoctor
            //$LastNameDoctor
            //$PatrNameDoctor
            //$GUID

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Приём #'.$RecId.' не найден.', 404);
                    return false;
                }

                if($rec['channel'] != 5){
                    throw new HttpException(200, 'Приём #'.$RecId.' не может быть изменен.', 404);
                    return false;
                }

                $query='UPDATE visits SET ';#name_organization,
                if($Organization){
                    $query.='mosru_organization=\''.$Organization.'\',';
                }else{
                    $query.='mosru_organization=NULL,';
                }
                if($Address){
                    $query.='mosru_address=\''.$Address.'\',';
                }else{
                    $query.='mosru_address=NULL,';
                }
                if($LastNameDoctor || $NameDoctor || $PatrNameDoctor){
                    $query.='mosru_specialist=\''.$LastNameDoctor.','.$NameDoctor.','.$PatrNameDoctor.'\', ';
                }else{
                    $query.='mosru_specialist=NULL,';
                }

                $query.='fact_start_dttm=\''.$Date.' 00:00:00\',';
                $query.='fact_end_dttm=\''.$Date.' 00:00:00\',';
                $query.='start_dttm=\''.$Date.' 00:00:00\',';
                $query.='time_range=tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';
                $query.='time_range_without_cooldown=tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';
                $query.="updated_at=NOW()::timestamp(0)";#updated_at
                $query.=' WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                #УДАЛЯЕМ ФАЙЛ ОТ ПРИЁМА
                $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ ФАЙЛ К ПРИЁМУ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$RecId.",";
                    $query.="'visits-mos-ru',";
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }
                #ДОБАВЛЯЕМ ФАЙЛ К ПРИЁМУ

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Приём #'.$RecId.' успешно изменён.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID, REC ID и Date являются обязательными.', 404);

            return false;
        }
    }
    public function deletePetVisits($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;

        if(
            $SsoId &&
            $PetId &&
            $RecId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            //$SsoId
            //$PetId
            //$RecId

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Приём #'.$RecId.' не найден.', 404);
                    return false;
                }

                if($rec['channel'] != 5){
                    throw new HttpException(200, 'Приём #'.$RecId.' не может быть удалён.', 404);
                    return false;
                }

                $query='DELETE FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='DELETE FROM visit_pets WHERE id_visit='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';';
                \Yii::$app->db->createCommand($query)->execute();

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Приём #'.$RecId.' успешно удалён.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID, REC ID и Date являются обязательными.', 404);

            return false;
        }
    }
    //Visits

    //Vaccination
    public function getPetVaccinations($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;

        if(!$SsoId || !$PetId) throw new NotFoundHttpException(json_encode(['error' => 'SSOID и PetId - обязательные параметры']));
            $rec = \Yii::$app->db->createCommand('SELECT id_owner FROM elk.owners WHERE sso_id=\''.$SsoId.'\';')->queryOne();
            if (!$rec) throw new NotFoundHttpException(json_encode(['error' => 'Владелец не найден']));

            $query = Pets::find()
            ->select([
                'public.pets.id  AS id',
                'elk.pets.ext_id AS ext_id',
            ])
            ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
            ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
            ->where(['public.pets_to_owner.id_owner' => $rec['id_owner']])
            ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

            $arr_pets = $query->asArray()->all();

            if (!sizeof($arr_pets)) throw new NotFoundHttpException(json_encode(['error' => 'Питомец не найден']));

            $pet_=1;
            for ($i = 0; $i < count($arr_pets); $i++) {
                if($arr_pets[$i]['ext_id'] == $PetId){
                    $pet_=0;
                }
            }
            if($pet_) throw new NotFoundHttpException(json_encode(['error' => 'Питомец не найден']));

            $pet_vac_array = array();
            for ($i = 0; $i < count($arr_pets); $i++) {
                if($arr_pets[$i]['ext_id'] == $PetId){//найденное животное
                    $pet_vac_ecto = Pets::find()
                    ->with(['pet_rabies_vaccinations' => function ($query) {
                        $query->select([
                            'pet_rabies_vaccination.id',
                            'pet_rabies_vaccination.id_pet',
                            'pet_rabies_vaccination.id_vaccine',
                            'pet_rabies_vaccination.drug_name',
                            'pet_rabies_vaccination.producer_name',
                            'pet_rabies_vaccination.batch',
                            'pet_rabies_vaccination.production_date',
                            'pet_rabies_vaccination.expiry_date',
                            'pet_rabies_vaccination.date',
                            'pet_rabies_vaccination.valid_until',
                            'pet_rabies_vaccination.id_organization',
                            'pet_rabies_vaccination.id_specialist',
                            'pet_rabies_vaccination.mosru_organization',
                            'organizations.name AS name_organization',
                            'files.hash AS file_hash',
                        ])->leftJoin(
                            'organizations',
                            'pet_rabies_vaccination.id_organization = organizations.id AND pet_rabies_vaccination.id_organization IS NOT NULL'
                        )->leftJoin(
                            'files',
                            'pet_rabies_vaccination.id = files.entity_id AND files.entity_type=\'vac-rab-mos-ru\''
                        );
                    }])
                    ->with(['pet_other_vaccinations' => function ($query) {
                        $query->select([
                            'pet_other_vaccinations.id',
                            'pet_other_vaccinations.id_pet',
                            'pet_other_vaccinations.id_vaccine',
                            'pet_other_vaccinations.drug_name',
                            'pet_other_vaccinations.producer_name',
                            'pet_other_vaccinations.batch',
                            'pet_other_vaccinations.production_date',
                            'pet_other_vaccinations.expiry_date',
                            'pet_other_vaccinations.date',
                            'pet_other_vaccinations.valid_until',
                            'pet_other_vaccinations.id_organization',
                            'pet_other_vaccinations.id_specialist',
                            'pet_other_vaccinations.mosru_organization',
                            'organizations.name AS name_organization',
                            'files.hash AS file_hash',
                        ])->leftJoin(
                            'organizations',
                            'pet_other_vaccinations.id_organization = organizations.id'
                        )->leftJoin(
                            'files',
                            'pet_other_vaccinations.id = files.entity_id AND files.entity_type=\'vac-other-mos-ru\''
                        );
                    }])
                    ->where(['id' => $arr_pets[$i]['id']])
                    ->asArray()
                    ->all();

                    foreach ($pet_vac_ecto[0]['pet_rabies_vaccinations'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                            if ($key === "producer_name") {$element += ['ProducerName' => $importantKeys[$key]];}
                            if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}
                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    $element += ['Organization' => $importantKeys['mosru_organization']];
                                    $element += ['Editable' => '1'];
                                }else{
                                    $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    $element += ['Organization' => $org['name']];
                                    $element += ['Editable' => '0'];
                                }
                            }
                            if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}

                            $element += ['Type' => '1'];//Бешенство

                            //GUID
                            if($importantKeys['file_hash']){
                                $element += ['GUID' => $importantKeys['file_hash']];
                            }else{
                                $element += ['GUID' => ''];
                            }
                            //GUID
                        }
                        array_push($pet_vac_array, $element);
                    }
                    foreach ($pet_vac_ecto[0]['pet_other_vaccinations'] as &$value) {
                        $importantKeys = &$value;
                        $element = [];
                        foreach(array_keys($importantKeys) as $key){
                            if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                            if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                            if ($key === "producer_name") {$element += ['ProducerName' => $importantKeys[$key]];}
                            if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}
                            if ($key === "id_organization") {
                                if($importantKeys[$key] == ''){
                                    $element += ['Organization' => $importantKeys['mosru_organization']];
                                    $element += ['Editable' => '1'];
                                }else{
                                    $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                    $element += ['Organization' => $org['name']];
                                    $element += ['Editable' => '0'];
                                }
                            }
                            if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                            if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}
                            $element += ['Type' => '0'];//Остальное

                            //GUID
                            if($importantKeys['file_hash']){
                                $element += ['GUID' => $importantKeys['file_hash']];
                            }else{
                                $element += ['GUID' => ''];
                            }
                            //GUID
                        }
                        array_push($pet_vac_array, $element);
                    }
                }
            }

            $resultPetsList = ['VaccinationsList' => $pet_vac_array]; //отправляем в soap

            return $resultPetsList;
        
    }

    public function getPetVaccinations_old($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;

        if(
            $SsoId &&
            $PetId
        ){
            $this->reportSoapMethod(__FUNCTION__);
            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $pet_vac_array = array();
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){//найденное животное
                        $pet_vac_ecto = Pets::find()
                        ->with('pet_rabies_vaccinations')
                        ->with('pet_other_vaccinations')
                        ->where(['id' => $arr_pets[$i]['id']])
                        ->asArray()
                        ->all();

                        foreach ($pet_vac_ecto[0]['pet_rabies_vaccinations'] as &$value) {
                            $importantKeys = &$value;
                            $element = [];
                            foreach(array_keys($importantKeys) as $key){
                                if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                                if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                                if ($key === "producer_name") {$element += ['ProducerName' => $importantKeys[$key]];}
                                if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}
                                if ($key === "id_organization") {
                                    if($importantKeys[$key] == ''){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                        $element += ['Editable' => '1'];
                                    }else{
                                        $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                        $element += ['Organization' => $org['name']];
                                        $element += ['Editable' => '0'];
                                    }
                                }
                                if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                                if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}

                                $element += ['Type' => '1'];//Бешенство

                                //GUID
                                $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['id'].' AND entity_type=\'vac-rab-mos-ru\';')->queryOne();
                                if($file['hash']){
                                    $element += ['GUID' => $file['hash']];
                                }else{
                                    $element += ['GUID' => ''];
                                }
                                //GUID
                            }
                            array_push($pet_vac_array, $element);
                        }
                        foreach ($pet_vac_ecto[0]['pet_other_vaccinations'] as &$value) {
                            $importantKeys = &$value;
                            $element = [];
                            foreach(array_keys($importantKeys) as $key){
                                if ($key === "id") {$element += ['Id' => $importantKeys[$key]];}
                                if ($key === "drug_name") {$element += ['DrugName' => $importantKeys[$key]];}
                                if ($key === "producer_name") {$element += ['ProducerName' => $importantKeys[$key]];}
                                if ($key === "batch") {$element += ['Batch' => $importantKeys[$key]];}
                                if ($key === "id_organization") {
                                    if($importantKeys[$key] == ''){
                                        $element += ['Organization' => $importantKeys['mosru_organization']];
                                        $element += ['Editable' => '1'];
                                    }else{
                                        $org = \Yii::$app->db->createCommand('SELECT name FROM organizations WHERE id='.$importantKeys[$key].';')->queryOne();
                                        $element += ['Organization' => $org['name']];
                                        $element += ['Editable' => '0'];
                                    }
                                }
                                if ($key === "valid_until") {$element += ['ValidUntil' => $importantKeys[$key]];}
                                if ($key === "date") {$element += ['Date' => $importantKeys[$key]];}
                                $element += ['Type' => '0'];//Остальное

                                //GUID
                                $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$importantKeys['id'].' AND entity_type=\'vac-other-mos-ru\';')->queryOne();
                                if($file['hash']){
                                    $element += ['GUID' => $file['hash']];
                                }else{
                                    $element += ['GUID' => ''];
                                }
                                //GUID
                            }
                            array_push($pet_vac_array, $element);
                        }
                    }
                }

                $resultPetsList = ['VaccinationsList' => $pet_vac_array]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }
    public function postPetVaccinations($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $DrugName = isset($ServiceInfo->DrugName) ? $ServiceInfo->DrugName : null;
        $VaccinationType = isset($ServiceInfo->VaccinationType) ? $ServiceInfo->VaccinationType : null;
        $Date = isset($ServiceInfo->Date) ? $ServiceInfo->Date : null;
        $Batch = isset($ServiceInfo->Batch) ? $ServiceInfo->Batch : null;
        $ProducerName = isset($ServiceInfo->ProducerName) ? $ServiceInfo->ProducerName : null;
        $ValidUntil = isset($ServiceInfo->ValidUntil) ? $ServiceInfo->ValidUntil : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        if(
            $SsoId &&
            $PetId &&
            $DrugName &&
            $VaccinationType != '' &&
            $Date &&
            $ValidUntil
        ){
            $this->reportSoapMethod(__FUNCTION__);

            // $SsoId
            // $PetId
            // $DrugName 1.
            // $VaccinationType 2.
            // $ProducerName 3.
            // $Batch 4.
            // $Date 5.
            // $ValidUntil 6.
            // $Organization

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $query='';
                if($VaccinationType == 1){
                    $query='INSERT INTO pet_rabies_vaccination ';#name_organization,
                }else{
                    $query='INSERT INTO pet_other_vaccinations ';#name_organization,
                }
                $query.='(mosru_organization, drug_name, batch, valid_until, producer_name, date, type_tmc, id_pet, created_at, updated_at) ';
                $query.='VALUES (';
                if($Organization){
                    $query.='\''.$Organization.'\',';
                }else{
                    $query.='NULL,';
                }
                $query.='\''.$DrugName.'\',';
                $query.='\''.$Batch.'\',';
                $query.='\''.$ValidUntil.'\',';
                if($ProducerName){
                    $query.='\''.$ProducerName.'\',';
                }else{
                    $query.='NULL,';
                }
                $query.='\''.$Date.'\',';
                $query.='\'vaccine\',';
                $query.='\''.$id_pet.'\',';
                $query.='NOW()::timestamp(0),';
                $query.='NOW()::timestamp(0)';
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();
                $IdPetVaccination = \Yii::$app->db->getLastInsertID();

                #ДОБАВЛЯЕМ ФАЙЛ К ВАКЦИНАЦИИ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$IdPetVaccination.",";
                    if($VaccinationType == 1){
                        $query.="'vac-rab-mos-ru',";
                    }else{
                        $query.="'vac-other-mos-ru',";
                    }
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }
                #ДОБАВЛЯЕМ ФАЙЛ К ВАКЦИНАЦИИ

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$IdPetVaccination.' об вакцинации успешно добавлена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID, DrugName, VaccinationType, Date и ValidUntil являются обязательными.', 404);

            return false;
        }
    }
    public function putPetVaccinations($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $DrugName = isset($ServiceInfo->DrugName) ? $ServiceInfo->DrugName : null;
        $VaccinationType = isset($ServiceInfo->VaccinationType) ? $ServiceInfo->VaccinationType : null;
        $Date = isset($ServiceInfo->Date) ? $ServiceInfo->Date : null;
        $Batch = isset($ServiceInfo->Batch) ? $ServiceInfo->Batch : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $ProducerName = isset($ServiceInfo->ProducerName) ? $ServiceInfo->ProducerName : null;
        $ValidUntil = isset($ServiceInfo->ValidUntil) ? $ServiceInfo->ValidUntil : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        if(
            $SsoId &&
            $PetId &&
            $DrugName &&
            $VaccinationType != '' &&
            $Date &&
            $ValidUntil
        ){
            $this->reportSoapMethod(__FUNCTION__);

            // $SsoId
            // $PetId
            // $DrugName 1.
            // $VaccinationType 2.
            // $ProducerName 3.
            // $Batch 4.
            // $Date 5.
            // $ValidUntil 6.
            // $Organization

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec='';
                $rec_flag=0;

                //Ищем запись для апдейта
                if($VaccinationType == 1){
                    $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_rabies_vaccination WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                }else{
                    $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_other_vaccinations WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                }

                // 1 - бешенство
                // 0 - другие

                if($rec['id'] == ''){
                    //может быть в другой таблице
                    if($VaccinationType == 1){
                        $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_other_vaccinations WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                    }else{
                        $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_rabies_vaccination WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                    }

                    if($rec['id'] == ''){
                        throw new HttpException(200, 'Запись #'.$RecId.' по вакцинациям не найдена.', 404);
                        return false;
                    }else{
                        $rec_flag=1;
                        //меняем тип далее
                    }
                }

                if($rec['id_vaccine'] && $rec['id_organization'] && $rec['id_specialist']){
                    throw new HttpException(200, 'Запись #'.$RecId.' по вакцинациям не может быть изменена.', 404);
                    return false;
                }

                $RecId_='';
                if($rec_flag){
                    //
                    if($VaccinationType == 1){
                        $query='DELETE FROM pet_other_vaccinations WHERE id='.$RecId.';';
                        \Yii::$app->db->createCommand($query)->execute();
                    }else{
                        $query='DELETE FROM pet_rabies_vaccination WHERE id='.$RecId.';';
                        \Yii::$app->db->createCommand($query)->execute();
                    }
                    //

                    $query='';
                    if($VaccinationType == 1){
                        $query='INSERT INTO pet_rabies_vaccination ';#name_organization,
                    }else{
                        $query='INSERT INTO pet_other_vaccinations ';#name_organization,
                    }
                    $query.='(mosru_organization, drug_name, batch, valid_until, producer_name, date, type_tmc, id_pet, created_at, updated_at) ';
                    $query.='VALUES (';
                    if($Organization){
                        $query.='\''.$Organization.'\',';
                    }else{
                        $query.='NULL,';
                    }
                    $query.='\''.$DrugName.'\',';
                    $query.='\''.$Batch.'\',';
                    $query.='\''.$ValidUntil.'\',';
                    if($ProducerName){
                        $query.='\''.$ProducerName.'\',';
                    }else{
                        $query.='NULL,';
                    }
                    $query.='\''.$Date.'\',';
                    $query.='\'vaccine\',';
                    $query.='\''.$id_pet.'\',';
                    $query.='NOW()::timestamp(0),';
                    $query.='NOW()::timestamp(0)';
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                    $RecId_ = \Yii::$app->db->getLastInsertID();
                }else{
                    $query='';
                    if($VaccinationType == 1){
                        $query='UPDATE pet_rabies_vaccination SET ';
                    }else{
                        $query='UPDATE pet_other_vaccinations SET ';
                    }
                    if($Organization){
                        $query.='mosru_organization=\''.$Organization.'\',';
                    }else{
                        $query.='mosru_organization=NULL,';
                    }
                    $query.='drug_name=\''.$DrugName.'\',';

                    if($ValidUntil){$query.='valid_until=\''.$ValidUntil.'\',';}else{$query.='valid_until=NULL,';}
                    if($Batch){$query.='batch=\''.$Batch.'\',';}else{$query.='batch=NULL,';}
                    if($ProducerName){$query.='producer_name=\''.$ProducerName.'\',';}else{$query.='producer_name=NULL,';}

                    $query.='date=\''.$Date.'\',';
                    $query.="updated_at=NOW()::timestamp(0)";#updated_at
                    $query.=' WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                    \Yii::$app->db->createCommand($query)->execute();
                }


                #УДАЛЯЕМ ФАЙЛ ОТ ВАКЦИНАЦИИ
                if($VaccinationType == 1){
                    if($rec_flag){
                        $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'vac-other-mos-ru\';';
                        \Yii::$app->db->createCommand($query)->execute();
                    }else{
                        $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'vac-rab-mos-ru\';';
                        \Yii::$app->db->createCommand($query)->execute();
                    }
                }else{
                    if($rec_flag){
                        $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'vac-rab-mos-ru\';';
                        \Yii::$app->db->createCommand($query)->execute();
                    }else{
                        $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'vac-other-mos-ru\';';
                        \Yii::$app->db->createCommand($query)->execute();
                    }
                }
                #УДАЛЯЕМ ФАЙЛ ОТ ВАКЦИНАЦИИ

                if($rec_flag){
                    $RecId = $RecId_;
                }

                #ДОБАВЛЯЕМ ФАЙЛ К ВАКЦИНАЦИИ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$RecId.",";
                    if($VaccinationType == 1){
                        $query.="'vac-rab-mos-ru',";
                    }else{
                        $query.="'vac-other-mos-ru',";
                    }
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }
                #ДОБАВЛЯЕМ ФАЙЛ К ВАКЦИНАЦИИ

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' об вакцинации успешно изменена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID, DrugName, VaccinationType, Date и ValidUntil являются обязательными.', 404);

            return false;
        }
    }
    public function deletePetVaccinations($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $VaccinationType = isset($ServiceInfo->VaccinationType) ? $ServiceInfo->VaccinationType : null;

        if(
            $SsoId &&
            $PetId &&
            $RecId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            //$SsoId
            //$PetId
            //$RecId
            //$VaccinationType

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec='';
                if($VaccinationType == 1){
                    $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_rabies_vaccination WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                }else{
                    $rec = \Yii::$app->db->createCommand('SELECT * FROM pet_other_vaccinations WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                }

                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' об вакцинации не найдена.', 404);
                    return false;
                }

                if($rec['id_vaccine'] && $rec['id_organization'] && $rec['id_specialist']){
                    throw new HttpException(200, 'Запись #'.$RecId.' об вакцинации не может быть изменена.', 404);
                    return false;
                }

                $query='';
                if($VaccinationType == 1){
                    $query='DELETE FROM pet_rabies_vaccination WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                }else{
                    $query='DELETE FROM pet_other_vaccinations WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                }
                \Yii::$app->db->createCommand($query)->execute();

                #УДАЛЯЕМ ФАЙЛ ОТ ВАКЦИНАЦИИ
                if($VaccinationType == 1){
                    $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'vac-rab-mos-ru\';';
                    \Yii::$app->db->createCommand($query)->execute();
                }else{
                    $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'vac-other-mos-ru\';';
                    \Yii::$app->db->createCommand($query)->execute();
                }
                #УДАЛЯЕМ ФАЙЛ ОТ ВАКЦИНАЦИИ

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' об вакцинации успешно удалена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID и IdVaccinations являются обязательными.', 404);

            return false;
        }
    }
    //Vaccination

    //LR
    public function getPetLRServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;

        //Запрос на получение сведений по лабораторным исследованиям//service_types = 5
        if(
            $SsoId &&
            $PetId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $pet_services_array = array();
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){//найденное животное
                        $pet_visits = Visits::find()
                        ->select([
                            'visits.*',
                            'visits.id AS id_visit',
                            'visit_pets.*',
                            'visits_gov_services.id_service',
                            'services.name AS name_service',
                            '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                            '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                            'organizations.name AS Organization',
                            // 'organizations.latitude AS LatitudeClinic',
                            // 'organizations.longitude AS LongitudeClinic',
                            'addresses.name AS Address',
                            'visits.id AS Id',
                            'visits.ticket_number AS TicketNumber',
                            'visits.duration AS Duration',
                            'users.fullname AS FullNameDoctor',
                            'users.f_fio AS FNameDoctor',
                            'users.i_fio AS INameDoctor',
                            'users.o_fio AS ONameDoctor',
                            'elk.pets.ext_id AS ext_id',
                        ])
                        ->where([
                            "visit_pets.id_pet" =>$arr_pets[$i]['id'],
                            "visits.status" => "F"
                        ])
                        ->andFilterWhere(['>=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i", strtotime('-3 years'))])//Ограничение по времени
                        ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                        ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                        ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                        ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                        ->leftJoin('users', 'specialists.id_user=users.id')
                        ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                        ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                        ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                        ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                        ->asArray()
                        ->orderBy('LOWER(VISITS.TIME_RANGE)::date DESC')
                        ->all();

                        for ($j = 0; $j < count($pet_visits); $j++) {
                            if($pet_visits[$j]['id_service']){
                                $pet_services_ = VisitsGovServices::find()
                                ->select([
                                    'gov_services.id AS id',
                                    'gov_services.name AS name',
                                    'gov_services.id_service_type AS type',
                                ])
                                ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                                ->where(['id_visit' => $pet_visits[$j]['id_visit']])
                                ->asArray()
                                ->all();

                                foreach ($pet_services_ as $pet_service){
                                    if($pet_service['type'] == 5){
                                        $element = [];

                                        $element += ['Id' => $pet_visits[$j]['id_visit']];

                                        $element += ['ServiceId' => $pet_service['id']];
                                        $element += ['ServiceName' => $pet_service['name']];
                                        $element += ['ServiceType' => $pet_service['type']];

                                        $element += ['Date' => $pet_visits[$j]['slot_date']];
                                        //." ".$pet_visits[$j]['slot_time']

                                        //
                                        if($pet_visits[$j]['channel'] == 5){
                                            $element += ['Channel' => $pet_visits[$j]['channel']];
                                            $element += ['Editable' => '1'];
                                            $element += ['Organization' => $pet_visits[$j]['mosru_organization']];
                                            $element += ['Address' => $pet_visits[$j]['mosru_address']];
                                            $element += ['FullNameDoctor' => $pet_visits[$j]['mosru_specialist']];

                                            $file = \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$pet_visits[$j]['id_visit'].' AND entity_type=\'visits-mos-ru\';')->queryOne();

                                            if($file['hash']){
                                                $element += ['GUID' => $file['hash']];
                                            }else{
                                                $element += ['GUID' => ''];
                                            }
                                        }else{
                                            $element += ['Channel' => $pet_visits[$j]['channel']];
                                            $element += ['Editable' => '0'];
                                            $element += ['Organization' => $pet_visits[$j]['Organization']];
                                            $element += ['Address' => $pet_visits[$j]['Address']];
                                            //$element += ['FullNameDoctor' => $pet_visits[$j]['FullNameDoctor']];
                                            $element += ['FullNameDoctor' => $pet_visits[$j]['FNameDoctor'].','.$pet_visits[$j]['INameDoctor'].','.$pet_visits[$j]['ONameDoctor']];
                                            $element += ['GUID' => ''];
                                        }
                                        //

                                        array_push($pet_services_array, $element);
                                    }
                                }
                            }
                        }
                    }
                }

                $resultPetsList = ['ServicesList' => $pet_services_array]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }
    public function getPetLRPrintServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $ServiceId = isset($ServiceInfo->ServiceId) ? $ServiceInfo->ServiceId : null;

        //Запрос на ссылку для скачивания файлов по лабораторным исследованиям формате PDF
        if(
            $SsoId &&
            $RecId &&
            $ServiceId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' по лабораторным исследованиям не найдена.', 404);
                    return false;
                }

                $file_link = '';
                $file_guid ='';
                if($rec['channel'] == '5'){
                    $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';')->queryOne();
                    $file_guid=$file['hash'];
                }else{
                    $format = PdfGenerator::FORMAT_A4;
                    $visit = Visits::findOne(['id' => $RecId]);
                    $description_types = VisitDescriptionsModel::findAvailableDescriptionTypes($RecId);
                    $descriptions = ArrayHelper::index($visit->getVisitDescriptions()->all(), 'id_description_type');

                    /* @var $generator PdfGenerator */
                    $generator = \Yii::$app->get('pdfGenerator');
                    try {
                        $path = $generator->createVisitDescription(
                            compact('visit', 'description_types', 'descriptions'),
                            $format
                        );
                    } catch (\Exception $e) {
                        $this->errorResponse($visit, $e->getMessage());
                    }

                    $file_link=\Yii::$app->params['url_api'] . '/upload/pdf/' . $path[1] . '.' . $path[2];
                }

                $info = array(
                    'GUID' => $file_guid,
                    'ServiceLink' => $file_link
                );

                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$ServiceInfo->SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, REC ID и Service ID являются обязательными.', 404);

            return false;
        }
    }
    public function postPetLRAddingServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $ServiceId = isset($ServiceInfo->ServiceId) ? $ServiceInfo->ServiceId : null;
        $Date = isset($ServiceInfo->PetId) ? $ServiceInfo->Date : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $Address = isset($ServiceInfo->Address) ? $ServiceInfo->Address : null;
        $NameDoctor = isset($ServiceInfo->NameDoctor) ? $ServiceInfo->NameDoctor : null;
        $LastNameDoctor = isset($ServiceInfo->LastNameDoctor) ? $ServiceInfo->LastNameDoctor : null;
        $PatrNameDoctor = isset($ServiceInfo->PatrNameDoctor) ? $ServiceInfo->PatrNameDoctor : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        //Добавление сведений по лабораторным исследованиям
        if(
            $SsoId &&
            $PetId &&
            $ServiceId &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            #$SsoId
            #$PetId
            #$ServiceId
            #$Date
            #$Organization
            #$Address
            #$NameDoctor
            #$LastNameDoctor
            #$PatrNameDoctor
            #$GUID

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $query='INSERT INTO visits ';
                $query.='(';
                $query.='mosru_organization, ';
                $query.='mosru_address, ';
                $query.='mosru_specialist, ';

                $query.='status, ';#1
                $query.='is_paid, ';#2
                $query.='id_owner, ';#3
                $query.='id_pet, ';#4
                $query.='fact_start_dttm, ';#5
                $query.='fact_end_dttm, ';#6
                $query.='cooldown, ';#7
                $query.='time_range, ';#8
                $query.='time_range_without_cooldown, ';#8.1
                $query.='channel, ';#9
                $query.='duration, ';#10
                $query.='start_dttm, ';#11
                $query.='type, ';#12
                $query.='variety, ';#12.1
                $query.='created_at, ';#13
                $query.='updated_at';#14
                $query.=') ';
                $query.='VALUES (';
                if($Organization){
                    $query.='\''.$Organization.'\',';
                }else{
                    $query.='NULL,';
                }
                if($Address){
                    $query.='\''.$Address.'\',';
                }else{
                    $query.='NULL,';
                }
                if($LastNameDoctor || $NameDoctor || $PatrNameDoctor){
                    $query.='\''.$LastNameDoctor.','.$NameDoctor.','.$PatrNameDoctor.'\',';
                }else{
                    $query.='NULL,';
                }

                $query.='\'F\',';#1
                $query.='\'false\',';#2
                $query.=''.$owner['id'].',';#3
                $query.=''.$id_pet.',';#4
                $query.='\''.$Date.' 00:00:00\',';#5
                $query.='\''.$Date.' 00:00:00\',';#6
                $query.='\'0\',';#7
                $query.='tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';#8
                $query.='tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';#8.1
                $query.='\'5\',';#9 <- новый канал 5
                $query.='\'10\',';#10
                $query.='\''.$Date.' 00:00:00\',';#11
                $query.='\'VISIT\',';#12
                $query.='\'SINGLE\',';#12.1
                $query.='NOW()::timestamp(0),';#13
                $query.='NOW()::timestamp(0)';#14
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();
                $id_visit = \Yii::$app->db->getLastInsertID();

                #ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
                $query='INSERT INTO visit_pets ';
                $query.='(id_pet, id_visit, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="".$id_pet.",";
                $query.="".$id_visit.",";
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0)";#updated_at
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ УСЛУГУ К ВИЗИТУ
                $query='INSERT INTO visits_gov_services ';
                $query.='(id_pet, id_visit, id_service, count, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="".$id_pet.",";
                $query.="".$id_visit.",";
                $query.="".$ServiceId.",";
                $query.="'1',";
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0)";#updated_at
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ ФАЙЛ К ПРИЁМУ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$id_visit.",";
                    $query.="'visits-mos-ru',";
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$id_visit.' по лабораторным исследованиям успешно добавлена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }
    public function putPetLRAddingServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $ServiceId = isset($ServiceInfo->ServiceId) ? $ServiceInfo->ServiceId : null;
        $Date = isset($ServiceInfo->PetId) ? $ServiceInfo->Date : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $Address = isset($ServiceInfo->Address) ? $ServiceInfo->Address : null;
        $NameDoctor = isset($ServiceInfo->NameDoctor) ? $ServiceInfo->NameDoctor : null;
        $LastNameDoctor = isset($ServiceInfo->LastNameDoctor) ? $ServiceInfo->LastNameDoctor : null;
        $PatrNameDoctor = isset($ServiceInfo->PatrNameDoctor) ? $ServiceInfo->PatrNameDoctor : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        //Добавление сведений по лабораторным исследованиям
        if(
            $SsoId &&
            $PetId &&
            $RecId &&
            $ServiceId &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            // $SsoId
            // $PetId
            // $ServiceType
            // $NameService
            // $Date
            // $Organization
            // $Address
            // $NameDoctor
            // $LastNameDoctor
            // $PatrNameDoctor
            // $GUID

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' по лабораторным исследованиям не найдена.', 404);
                    return false;
                }

                if($rec['channel'] != 5){
                    throw new HttpException(200, 'Запись #'.$RecId.' по лабораторным исследованиям не может быть изменена.', 404);
                    return false;
                }

                $query='UPDATE visits SET ';

                if($Organization){
                    $query.='mosru_organization=\''.$Organization.'\',';
                }else{
                    $query.='mosru_organization=NULL,';
                }
                if($Organization){
                    $query.='mosru_address=\''.$Address.'\',';
                }else{
                    $query.='mosru_address=NULL,';
                }
                if($LastNameDoctor || $NameDoctor || $PatrNameDoctor){
                    $query.='mosru_specialist=\''.$LastNameDoctor.','.$NameDoctor.','.$PatrNameDoctor.'\',';
                }else{
                    $query.='mosru_specialist=NULL,';
                }
                $query.='fact_start_dttm=\''.$Date.' 00:00:00\',';
                $query.='fact_end_dttm=\''.$Date.' 00:00:00\',';
                $query.='start_dttm=\''.$Date.' 00:00:00\',';
                $query.='time_range=tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';
                $query.='time_range_without_cooldown=tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';
                $query.="updated_at=NOW()::timestamp(0)";#updated_at
                $query.=' WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='UPDATE visits_gov_services SET ';#name_organization,
                $query.='id_service='.$ServiceId.',';
                $query.="updated_at=NOW()::timestamp(0)";#updated_at
                $query.=' WHERE id_visit='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                #УДАЛЯЕМ ФАЙЛ ОТ ПРИЁМА
                $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ ФАЙЛ К ПРИЁМУ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$RecId.",";
                    $query.="'visits-mos-ru',";
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' по лабораторным исследованиям успешно изменена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SsoId, PetId, RecId, ServiceId и Date являются обязательными.', 404);

            return false;
        }
    }
    public function deletePetLRAddingServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;

        //Удаление сведений по лабораторным исследованиям
        if(
            $SsoId &&
            $PetId &&
            $RecId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            // $SsoId
            // $PetId
            // $RecId

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' сведений по лабораторным исследованиям не найдена.', 404);
                    return false;
                }

                if($rec['channel'] != 5){
                    throw new HttpException(200, 'Запись #'.$RecId.' сведений по лабораторным исследованиям не может быть удалена.', 404);
                    return false;
                }

                $query='DELETE FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='DELETE FROM visit_pets WHERE id_visit='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';';
                \Yii::$app->db->createCommand($query)->execute();

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' сведений по лабораторным исследованиям успешно удалена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID и IdService являются обязательными.', 404);

            return false;
        }
    }
    //LR

    //CDS
    public function getPetCDSServices($ServiceInfo){
        //Запрос на получение сведений по Клинико-диагностических исследований//service_types = 5
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;

        if(
            $SsoId &&
            $PetId
        ){
            $this->reportSoapMethod(__FUNCTION__);
            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $pet_services_array = array();
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){//найденное животное
                        $pet_visits = Visits::find()
                        ->select([
                            'visits.*',
                            'visits.id AS id_visit',
                            'visit_pets.*',
                            'visits_gov_services.id_service',
                            'services.name AS name_service',
                            '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                            '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                            'organizations.name AS Organization',
                            // 'organizations.latitude AS LatitudeClinic',
                            // 'organizations.longitude AS LongitudeClinic',
                            'addresses.name AS Address',
                            'visits.id AS Id',
                            'visits.ticket_number AS TicketNumber',
                            'visits.duration AS Duration',
                            'users.fullname AS FullNameDoctor',
                            'users.f_fio AS FNameDoctor',
                            'users.i_fio AS INameDoctor',
                            'users.o_fio AS ONameDoctor',
                            'elk.pets.ext_id AS ext_id',
                        ])
                        ->where([
                            "visit_pets.id_pet" =>$arr_pets[$i]['id'],
                            "visits.status" => "F"
                        ])
                        ->andFilterWhere(['>=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i", strtotime('-3 years'))])//Ограничение по времени
                        ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                        ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                        ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                        ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                        ->leftJoin('users', 'specialists.id_user=users.id')
                        ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                        ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                        ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                        ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                        ->asArray()
                        ->orderBy('LOWER(VISITS.TIME_RANGE)::date DESC')
                        ->all();

                        for ($j = 0; $j < count($pet_visits); $j++) {
                            if($pet_visits[$j]['id_service']){
                                $pet_services_ = VisitsGovServices::find()
                                ->select([
                                    'gov_services.id AS id',
                                    'gov_services.name AS name',
                                    'gov_services.id_service_type AS type',
                                ])
                                ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                                ->where(['id_visit' => $pet_visits[$j]['id_visit']])
                                ->asArray()
                                ->all();

                                foreach ($pet_services_ as $pet_service){
                                    if($pet_service['type'] == 4){
                                        $element = [];

                                        $element += ['Id' => $pet_visits[$j]['id_visit']];

                                        $element += ['ServiceId' => $pet_service['id']];
                                        $element += ['ServiceName' => $pet_service['name']];
                                        $element += ['ServiceType' => $pet_service['type']];

                                        $element += ['Date' => $pet_visits[$j]['slot_date']];
                                        //." ".$pet_visits[$j]['slot_time']

                                        //
                                        if($pet_visits[$j]['channel'] == 5){
                                            $element += ['Channel' => $pet_visits[$j]['channel']];
                                            $element += ['Editable' => '1'];
                                            $element += ['Organization' => $pet_visits[$j]['mosru_organization']];
                                            $element += ['Address' => $pet_visits[$j]['mosru_address']];
                                            $element += ['FullNameDoctor' => $pet_visits[$j]['mosru_specialist']];

                                            $file = \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$pet_visits[$j]['id_visit'].' AND entity_type=\'visits-mos-ru\';')->queryOne();
                                            if($file['hash']){
                                                $element += ['GUID' => $file['hash']];
                                            }else{
                                                $element += ['GUID' => ''];
                                            }
                                        }else{
                                            $element += ['Channel' => $pet_visits[$j]['channel']];
                                            $element += ['Editable' => '0'];
                                            $element += ['Organization' => $pet_visits[$j]['Organization']];
                                            $element += ['Address' => $pet_visits[$j]['Address']];
                                            //$element += ['FullNameDoctor' => $pet_visits[$j]['FullNameDoctor']];
                                            $element += ['FullNameDoctor' => $pet_visits[$j]['FNameDoctor'].','.$pet_visits[$j]['INameDoctor'].','.$pet_visits[$j]['ONameDoctor']];

                                            $element += ['GUID' => ''];
                                        }
                                        //

                                        array_push($pet_services_array, $element);
                                    }
                                }
                            }
                        }
                    }
                }

                $resultPetsList = ['ServicesList' => $pet_services_array]; //отправляем в soap

                return $resultPetsList;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$ServiceInfo->SsoId.' не найден.', 404);

                return false;
            }

        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }
    public function getPetCDSPrintServices($ServiceInfo){
        //Запрос на ссылку для скачивания файлов по клинико-диагностическим исследованиям формате PDF
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $ServiceId = isset($ServiceInfo->ServiceId) ? $ServiceInfo->ServiceId : null;

        if(
            $SsoId &&
            $RecId &&
            $ServiceId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' по клинико-диагностическим исследованиям не найдена.', 404);
                    return false;
                }

                $file_link = '';
                $file_guid ='';
                if($rec['channel'] == '5'){
                    $file= \Yii::$app->db->createCommand('SELECT hash FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';')->queryOne();
                    $file_guid=$file['hash'];
                }else{
                    $format = PdfGenerator::FORMAT_A4;
                    $visit = Visits::findOne(['id' => $RecId]);
                    $description_types = VisitDescriptionsModel::findAvailableDescriptionTypes($RecId);
                    $descriptions = ArrayHelper::index($visit->getVisitDescriptions()->all(), 'id_description_type');

                    /* @var $generator PdfGenerator */
                    $generator = \Yii::$app->get('pdfGenerator');
                    try {
                        $path = $generator->createVisitDescription(
                            compact('visit', 'description_types', 'descriptions'),
                            $format
                        );
                    } catch (\Exception $e) {
                        $this->errorResponse($visit, $e->getMessage());
                    }

                    $file_link=\Yii::$app->params['url_api'] . '/upload/pdf/' . $path[1] . '.' . $path[2];
                }

                $info = array(
                    'GUID' => $file_guid,
                    'ServiceLink' => $file_link
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, REC ID и Service ID являются обязательными.', 404);

            return false;
        }
    }
    public function postPetCDSAddingServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $ServiceId = isset($ServiceInfo->ServiceId) ? $ServiceInfo->ServiceId : null;
        $Date = isset($ServiceInfo->PetId) ? $ServiceInfo->Date : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $Address = isset($ServiceInfo->Address) ? $ServiceInfo->Address : null;
        $NameDoctor = isset($ServiceInfo->NameDoctor) ? $ServiceInfo->NameDoctor : null;
        $LastNameDoctor = isset($ServiceInfo->LastNameDoctor) ? $ServiceInfo->LastNameDoctor : null;
        $PatrNameDoctor = isset($ServiceInfo->PatrNameDoctor) ? $ServiceInfo->PatrNameDoctor : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        //Добавление сведений по клинико-диагностическим исследованиям
        if(
            $SsoId &&
            $PetId &&
            $ServiceId &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            #$SsoId
            #$PetId
            #$ServiceId
            #$Date
            #$Organization
            #$Address
            #$NameDoctor
            #$LastNameDoctor
            #$PatrNameDoctor
            #$GUID

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $query='INSERT INTO visits ';
                $query.='(';
                $query.='mosru_organization, ';
                $query.='mosru_address, ';
                $query.='mosru_specialist, ';

                $query.='status, ';#1
                $query.='is_paid, ';#2
                $query.='id_owner, ';#3
                $query.='id_pet, ';#4
                $query.='fact_start_dttm, ';#5
                $query.='fact_end_dttm, ';#6
                $query.='cooldown, ';#7
                $query.='time_range, ';#8
                $query.='time_range_without_cooldown, ';#8.1
                $query.='channel, ';#9
                $query.='duration, ';#10
                $query.='start_dttm, ';#11
                $query.='type, ';#12
                $query.='variety, ';#12.1
                $query.='created_at, ';#13
                $query.='updated_at';#14
                $query.=') ';
                $query.='VALUES (';
                if($Organization){
                    $query.='\''.$Organization.'\',';
                }else{
                    $query.='NULL,';
                }
                if($Address){
                    $query.='\''.$Address.'\',';
                }else{
                    $query.='NULL,';
                }
                if($LastNameDoctor || $NameDoctor || $PatrNameDoctor){
                    $query.='\''.$LastNameDoctor.','.$NameDoctor.','.$PatrNameDoctor.'\',';
                }else{
                    $query.='NULL,';
                }

                $query.='\'F\',';#1
                $query.='\'false\',';#2
                $query.=''.$owner['id'].',';#3
                $query.=''.$id_pet.',';#4
                $query.='\''.$Date.' 00:00:00\',';#5
                $query.='\''.$Date.' 00:00:00\',';#6
                $query.='\'0\',';#7
                $query.='tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';#8
                $query.='tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';#8.1
                $query.='\'5\',';#9 <- новый канал 5
                $query.='\'10\',';#10
                $query.='\''.$Date.' 00:00:00\',';#11
                $query.='\'VISIT\',';#12
                $query.='\'SINGLE\',';#12.1
                $query.='NOW()::timestamp(0),';#13
                $query.='NOW()::timestamp(0)';#14
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();
                $id_visit = \Yii::$app->db->getLastInsertID();

                #ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
                $query='INSERT INTO visit_pets ';
                $query.='(id_pet, id_visit, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="".$id_pet.",";
                $query.="".$id_visit.",";
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0)";#updated_at
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ УСЛУГУ К ВИЗИТУ
                $query='INSERT INTO visits_gov_services ';
                $query.='(id_pet, id_visit, id_service, count, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="".$id_pet.",";
                $query.="".$id_visit.",";
                $query.="".$ServiceId.",";
                $query.="'1',";
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0)";#updated_at
                $query.=');';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ ФАЙЛ К ПРИЁМУ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$id_visit.",";
                    $query.="'visits-mos-ru',";
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$id_visit.' по клинико-диагностическим исследованиям успешно добавлена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }
    public function putPetCDSAddingServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;
        $ServiceId = isset($ServiceInfo->ServiceId) ? $ServiceInfo->ServiceId : null;
        $Organization = isset($ServiceInfo->Organization) ? $ServiceInfo->Organization : null;
        $Address = isset($ServiceInfo->Address) ? $ServiceInfo->Address : null;
        $NameDoctor = isset($ServiceInfo->NameDoctor) ? $ServiceInfo->NameDoctor : null;
        $LastNameDoctor = isset($ServiceInfo->LastNameDoctor) ? $ServiceInfo->LastNameDoctor : null;
        $PatrNameDoctor = isset($ServiceInfo->PatrNameDoctor) ? $ServiceInfo->PatrNameDoctor : null;
        $Date = isset($ServiceInfo->PetId) ? $ServiceInfo->Date : null;
        $GUID = isset($ServiceInfo->GUID) ? $ServiceInfo->GUID : null;

        //Добавление сведений по клинико-диагностическим исследованиям
        if(
            $SsoId &&
            $PetId &&
            $RecId &&
            $ServiceId &&
            $Date
        ){
            $this->reportSoapMethod(__FUNCTION__);

            // $SsoId
            // $PetId
            // $RecId
            // $ServiceId
            // $NameService
            // $DateService
            // $Organization
            // $Address
            // $NameDoctor
            // $LastNameDoctor
            // $PatrNameDoctor
            // $GUID

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' по клинико-диагностическим исследованиям не найдена.', 404);
                    return false;
                }

                if($rec['channel'] != 5){
                    throw new HttpException(200, 'Запись #'.$RecId.' по клинико-диагностическим исследованиям не может быть изменена.', 404);
                    return false;
                }

                $query='UPDATE visits SET ';
                if($Organization){
                    $query.='mosru_organization=\''.$Organization.'\',';
                }else{
                    $query.='mosru_organization=NULL,';
                }
                if($Address){
                    $query.='mosru_address=\''.$Address.'\',';
                }else{
                    $query.='mosru_address=NULL,';
                }
                if($LastNameDoctor || $NameDoctor || $PatrNameDoctor){
                    $query.='mosru_specialist=\''.$LastNameDoctor.','.$NameDoctor.','.$PatrNameDoctor.'\',';
                }else{
                    $query.='mosru_specialist=NULL,';
                }

                $query.='fact_start_dttm=\''.$Date.' 00:00:00\',';
                $query.='fact_end_dttm=\''.$Date.' 00:00:00\',';
                $query.='start_dttm=\''.$Date.' 00:00:00\',';
                $query.='time_range=tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';
                $query.='time_range_without_cooldown=tsrange(\''.$Date.' 00:00:00\', (\''.$Date.' 00:00:00\'::TIMESTAMP + INTERVAL \'10 MINUTES\'), \'[)\'),';
                $query.="updated_at=NOW()::timestamp(0)";#updated_at
                $query.=' WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='UPDATE visits_gov_services SET ';#name_organization,
                $query.='id_service='.$ServiceId.',';
                $query.="updated_at=NOW()::timestamp(0)";#updated_at
                $query.=' WHERE id_visit='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                #УДАЛЯЕМ ФАЙЛ ОТ ПРИЁМА
                $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';';
                \Yii::$app->db->createCommand($query)->execute();

                #ДОБАВЛЯЕМ ФАЙЛ К ПРИЁМУ
                if($GUID){
                    $query='INSERT INTO files ';
                    $query.='(created, hash, path, entity_id, entity_type, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="NOW()::timestamp(0),";
                    $query.="'".$GUID."',";
                    $query.="'/',";
                    $query.="".$RecId.",";
                    $query.="'visits-mos-ru',";
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0)";#updated_at
                    $query.=');';
                    \Yii::$app->db->createCommand($query)->execute();
                }

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' по клинико-диагностическим исследованиям успешно изменена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID и PET ID являются обязательными.', 404);

            return false;
        }
    }
    public function deletePetCDSAddingServices($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $PetId = isset($ServiceInfo->PetId) ? $ServiceInfo->PetId : null;
        $RecId = isset($ServiceInfo->RecId) ? $ServiceInfo->RecId : null;

        //Удаление сведений по клинико-диагностическим исследованиям
        if(
            $SsoId &&
            $PetId &&
            $RecId
        ){
            $this->reportSoapMethod(__FUNCTION__);

            //$SsoId
            //$PetId
            //$RecId

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);
            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS NOT', 'elk.pets.ext_id', null]);

                $arr_pets = $query->asArray()->all();

                $pet_=1;
                $id_pet=0;
                for ($i = 0; $i < count($arr_pets); $i++) {
                    if($arr_pets[$i]['ext_id'] == $PetId){
                        $pet_=0;
                        $id_pet=$arr_pets[$i]['id'];
                    }
                }
                if($pet_){
                    throw new HttpException(200, 'Питомец PetID '.$PetId.' не найден.', 404);
                    return false;
                }

                $rec = \Yii::$app->db->createCommand('SELECT * FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';')->queryOne();
                if($rec['id'] == ''){
                    throw new HttpException(200, 'Запись #'.$RecId.' сведений по клинико-диагностическим исследованиям не найдена.', 404);
                    return false;
                }

                if($rec['channel'] != 5){
                    throw new HttpException(200, 'Запись #'.$RecId.' сведений по клинико-диагностическим исследованиям не может быть удалена.', 404);
                    return false;
                }

                $query='DELETE FROM visits WHERE id='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='DELETE FROM visit_pets WHERE id_visit='.$RecId.' AND id_pet='.$id_pet.';';
                \Yii::$app->db->createCommand($query)->execute();

                $query='DELETE FROM files WHERE entity_id='.$RecId.' AND entity_type=\'visits-mos-ru\';';
                \Yii::$app->db->createCommand($query)->execute();

                $info = array(
                    'Status' => 'OK',
                    'Note' => 'Запись #'.$RecId.' сведений по клинико-диагностическим исследованиям успешно удалена.'
                );
                return $info;
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SSO ID, PET ID и IdService являются обязательными.', 404);

            return false;
        }
    }
    //CDS

    public function putPetError($ServiceInfo){
        $SsoId = isset($ServiceInfo->SsoId) ? $ServiceInfo->SsoId : null;
        $IntId = isset($ServiceInfo->IntId) ? $ServiceInfo->IntId : null;
        $Comment = isset($ServiceInfo->Comment) ? $ServiceInfo->Comment : null;

        if(
            $SsoId &&
            $IntId &&
            $Comment
        ){
            $this->reportSoapMethod(__FUNCTION__);

            $owner = PetOwners::findOne(['sso_id' => $SsoId]);

            if($owner['id']){
                $query = Pets::find()
                ->select([
                    'public.pets.id  AS int_id',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->where(['public.pets_to_owner.id_owner' => $owner['id']]);

                $array_all_pets = $query->asArray()->all();

                if($IntId){
                    $pet_=1;
                    for ($i = 0; $i < count($array_all_pets); $i++) {
                        if($array_all_pets[$i]['int_id'] == $IntId){
                            $pet_=0;
                        }
                    }
                    if($pet_){
                        throw new HttpException(200, 'Питомец IntId '.$IntId.' не найден.', 404);
                        return false;
                    }

                    $query='UPDATE public.pets SET ';
                    $query.='mosru=\'false\',';
                    $query.='description=\''.$Comment.'\'';
                    $query.=' WHERE id='.$IntId.';';
                    \Yii::$app->db->createCommand($query)->execute();

                    $info = array(
                        'Status' => 'OK',
                        'Note' => 'Питомец #'.$IntId.' исключен из списка.'
                    );
                    return $info;
                }else{
                    throw new HttpException(200, 'Входящий параметр IntId является обязательным.', 404);
                    return false;
                }
            }else{
                throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);
                return false;
            }
        }else{
            throw new HttpException(200, 'Входящие параметры SsoId и IntId являются обязательными.', 404);
            return false;
        }
    }

    public function petList($VidgetInfo){
        $SsoId = isset($VidgetInfo->SsoId) ? $VidgetInfo->SsoId : null;

        $this->reportSoapMethod(__FUNCTION__);
        $owner = PetOwners::findOne(['sso_id' => $SsoId]);

        if($owner){
            $query = Pets::find()
                ->select([
                    'public.pets.id',
                    'public.pets.name',
                    'elk.pets.ext_id AS ext_id',
                ])

                ->leftJoin('public.pets_to_owner', 'public.pets.id=public.pets_to_owner.id_pet')
                ->leftJoin('elk.pets', 'public.pets.id=elk.pets.id_pet')
                ->andWhere(['public.pets_to_owner.id_owner' => $owner['id']])
                ->andWhere(['IS', 'public.pets.reg_expire_date', null]);

            $arr_pets = $query->asArray()->all();

            foreach($arr_pets as &$item){
                $item['Id'] = $item['id'];
                unset($item['id']);
                $item['NickName'] = $item['name'];
                unset($item['name']);
            }

            $arr_pets =  array_filter($arr_pets, function ($x) { return $x['ext_id'] != null; });

            for ($i = 0; $i < count($arr_pets); $i++) {
                $pet = Visits::find()
                ->select([
                'visits.*',
                'visit_pets.*',
                'visits_gov_services.id_service',
                'services.name AS name_service',
                'visits.channel AS Channel',
                '(LOWER(VISITS.TIME_RANGE)::date) AS slot_date',
                '(LOWER(VISITS.TIME_RANGE)::time) AS slot_time',
                    'organizations.name AS NameClinic',
                    'organizations.latitude AS LatitudeClinic',
                    'organizations.longitude AS LongitudeClinic',
                    'addresses.name AS AddressClinic',
                    'visits.ticket_number AS TicketNumber',
                    'visits.duration AS Duration',
                    'users.fullname AS FullNameDoctor',
                    'elk.pets.ext_id AS ext_id',
            ])
                ->where([
                "visit_pets.id_pet" =>$arr_pets[$i]['Id'],
                "visits.status" => "N"
                ])
                ->andFilterWhere(['>=', 'LOWER(VISITS.TIME_RANGE)::date', date("Y-m-d h:i")])
                ->leftJoin('organizations', 'visits.id_organization=organizations.id')
                ->leftJoin('addresses', 'organizations.id_address=addresses.id')
                ->leftJoin('visits_specialists', 'visits.id=visits_specialists.id_visit')
                ->leftJoin('specialists', 'visits_specialists.id_specialist=specialists.id')
                ->leftJoin('users', 'specialists.id_user=users.id')
                ->leftJoin('visit_pets', 'visits.id=visit_pets.id_visit')
                ->leftJoin('elk.pets', 'visit_pets.id_pet=elk.pets.id_pet')
                ->leftJoin('visits_gov_services', 'visits.id=visits_gov_services.id_visit')
                ->leftJoin('services', 'visits_gov_services.id_service=services.id')
                ->asArray()
                ->all();

                $pet_last_pet_rabies_vaccinations = Pets::find()
                ->with('last_pet_rabies_vaccinations')
                ->where(['id' => $arr_pets[$i]['Id']])
                ->asArray()
                ->all();

                // Добавляем статусы вакцинации
                // <!--0-Вакцинация просрочена; 1-До окончания вакцинации 30 дней; 2-Вакцинирован-->

                $today = date("Y-m-d");
                $check_time = $pet_last_pet_rabies_vaccinations[0]['last_pet_rabies_vaccinations']['valid_until'];

                $diference = strtotime($check_time) - strtotime($today); // разница между двумя датами в секундах
                $days = $diference / 86400; // секунды в сутках

                $vaccinationStatus = '0';
                if($pet_last_pet_rabies_vaccinations[0]['last_pet_rabies_vaccinations']['valid_until']){
                    if($days > 30){
                        $vaccinationStatus = '2';
                    }
                    if($days < 30 && $days > 0){
                        $vaccinationStatus = '1';
                    }
                    if($days < 0){
                        $vaccinationStatus = '0';
                    }
                }

                $arr_pets[$i] += ['VaccinationStatus' => $vaccinationStatus];

                $pet_filter = array();
                $arr_pets[$i]['Id'] = $arr_pets[$i]['ext_id'];
                unset($arr_pets[$i]['ext_id']);
                foreach ($pet as &$value) {
                    $importantKeys = &$value;
                    $el_pet_filter = [];
                    foreach(array_keys($importantKeys) as $key){
                        if ($key === "NameClinic") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "AddressClinic") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "LatitudeClinic") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "LongitudeClinic") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "Channel") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "TicketNumber") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "Duration") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "FullNameDoctor") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }
                        if ($key === "Id") {
                            $el_pet_filter += [$key => $importantKeys[$key]];
                        }

                        if ($key === "slot_date") {
                            $el_pet_filter += ["Date" => $importantKeys[$key]];

                        }
                        if ($key === "slot_time") {
                            $el_pet_filter += ["Slot" => $importantKeys[$key]];
                        }

                        if ($key === "id_service") {
                            $id_visit_gov = $importantKeys['id_visit'];

                            $pet_services_ = VisitsGovServices::find()
                            ->select([
                                'gov_services.name AS name',
                            ])
                            ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
                            ->where(['id_visit' => $id_visit_gov])
                            ->asArray()
                            ->all();

                            $pet_services_result = [];
                            foreach ($pet_services_ as $pet_service){
                                $pet_services_result[] = $pet_service['name'];
                            }
                            $el_pet_filter += ["Services" => array('ServiceValue'=>$pet_services_result)];
                        }
                    }

                    // Проверка на текущий день, время приема. Если текущее время больше времени приема, то прием не попадает в выборку
                    if($el_pet_filter['Date'] == date("Y-m-d")){
                        if(date("H:i:s") < date($el_pet_filter['Slot'])){
                            array_push($pet_filter, $el_pet_filter);
                        }
                    }else{
                        array_push($pet_filter, $el_pet_filter);
                    }
                }
                $arr_pets[$i] += ['RegistrationsList' => $pet_filter];
            }


            $resultPets = ['Pet'=>$arr_pets ];
            $resultPetsList = ['PetsList'=>$resultPets ];

            return $resultPetsList;
        }else{
            throw new HttpException(200, 'Владелец SSO ID '.$SsoId.' не найден.', 404);

            return false;
        }
    }

    public function putRefundData($refundDataRequest) {
        $this->reportSoapMethod(__FUNCTION__);
        $data = $refundDataRequest->RefundData;

        $visit_id = ETPMessage::find()
            ->select('visit_id')
            ->where(['service_number' => $data->ServiceNumber])
            ->andWhere(['!=', 'visit_id', 0])
            ->andWhere(['not', ['visit_id' => null]])
            ->orderBy(['id' => SORT_DESC])
            ->scalar();

        $visit = Visits::findOne(['id' => $visit_id]);

        if (!$visit) {
            throw new ETPException('Visit for provided ServiceNumber not found');
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new RefundEvent([
            'visit' => $visit,
            'bank' => $data->BankName,
            'corresponded_account' => $data->CorrespondedAccount,
            'org_name' => $data->OrganizationName,
            'bik' => $data->Bik,
            'client_account' => $data->ClientAccount,
            'client_fio' => $data->ClientFio,
        ]));

        (new MosruStatusSender())->visitPaymentDataReceived($visit_id);

        return [
            'result' => 'OK',
        ];
    }

    public function getCurrentRegistrations($getCurrentRegistrationsRequest){
        $this->reportSoapMethod(__FUNCTION__);

        $LastName = isset($getCurrentRegistrationsRequest->LastName) ? $getCurrentRegistrationsRequest->LastName : null;
        $FirstName = isset($getCurrentRegistrationsRequest->FirstName) ? $getCurrentRegistrationsRequest->FirstName : null;
        $Phone = isset($getCurrentRegistrationsRequest->Phone) ? $getCurrentRegistrationsRequest->Phone : null;
        $MiddleName = isset($getCurrentRegistrationsRequest->MiddleName) ? $getCurrentRegistrationsRequest->MiddleName : null;

        return CurrentRegistrationsHandler::getCurrentRegistrations($LastName, $FirstName, $Phone, $MiddleName);
    }

    /**
     * @param array $putApplicationRequest
     * @return \app\modules\soap\v2\models\etp\InstantResponse
     * @soap
     */
    public function putApplication($putApplicationRequest){
        $this->reportSoapMethod(__FUNCTION__);
        $this->module->log('Handling putApplication request', Logger::LEVEL_INFO);
        $message = new ApplicationMessage([
            'requestData' => $putApplicationRequest,
            'SystemId' => $this->systemId,
            'MessageId' => $this->messageId,
            'logger' => [$this->module, 'log'],
        ]);
        return $message->process();
    }

    public function putBooking($putBookingRequest){
        $this->reportSoapMethod(__FUNCTION__);

        $message = new BookingMessage([
            'requestData' => $putBookingRequest,
            'SystemId' => $this->systemId,
            'MessageId' => $this->messageId,
            'logger' => [$this->module, 'log'],
        ]);

        return $message->process();
    }

    /**
     * @param array $putStatusRequest
     * @return \app\modules\soap\v2\models\etp\InstantResponse
     * @soap
     */
    public function putStatus($putStatusRequest){
        $this->reportSoapMethod(__FUNCTION__);

        $message = new ApplicationStatusMessage([
            'requestData' => $putStatusRequest,
            'SystemId' => $this->systemId,
            'MessageId' => $this->messageId,
        ]);

        return $message->process();
    }

    public function messageId(?string $v): void
    {
        $this->messageId = $v;
    }

    public function systemId(?string $v): void
    {
        $this->systemId = $v;
    }
}
