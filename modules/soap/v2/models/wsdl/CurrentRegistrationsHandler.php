<?php

namespace app\modules\soap\v2\models\wsdl;

use app\common\helpers\PhoneHelper;
use app\common\models\VisitStatus;
use app\models\db\MosRuServices;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\wsdl\CurrentRegistrationsHandler as CurrentRegistrationsHandlerV1;
use app\modules\soap\v2\models\db\ETPMessage;
use app\modules\soap\v2\skeletons\current_registrations\CurrentRegistrationsRegistration;
use app\modules\soap\v2\skeletons\current_registrations\CurrentRegistrationsResponse;
use app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberAddressCall;
use app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberAnimal;
use app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberOwner;
use app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberRegistration;
use app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberResponse;
use app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberService;
use app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberServiceWrapper;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class CurrentRegistrationsHandler
 * @package app\modules\soap\v2\models\wsdl
 */
class CurrentRegistrationsHandler extends CurrentRegistrationsHandlerV1
{
    use HandlerTrait;

    /**
     * @param string $ServiceNumber
     * @return \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberResponse
     */
    public static function getRegistrationByServiceNumber($ServiceNumber)
    {
        $tableName = ETPMessage::tableName();

        $registrations = static::getQuery()
            ->addSelect([
                'base_declarant' => new Expression($tableName . ".message #>> '{ApplicationData,Declarant}'"),
            ])
            ->andWhere([
                'service_number' => $ServiceNumber,
            ])
            ->limit(1)
            ->asArray()
            ->all();

        $response = new RegByServiceNumberResponse();

        if (empty($registrations)) {
            return $response;
        }

        // $registrations = static::appendAddresses($registrations);
        // $registrations = static::appendServiceList($registrations);

        $registration = is_array($registrations) ? $registrations[0] : $registrations;

        $response->RegistrationByServiceNumber = static::getRegistrationObjectV2($registration);

        return $response;
    }

    /**
     * @param string $LastName
     * @param string $FirstName
     * @param string $Phone
     * @param string $MiddleName
     * @return \app\modules\soap\v2\skeletons\current_registrations\CurrentRegistrationsResponse
     */
    public static function getCurrentRegistrations($LastName, $FirstName, $Phone, $MiddleName = null)
    {
        //для неавторизованных пользователей метод ничего не возвращает
        $params = \Yii::$app->getModule('soap')->params['unauthorized_message'];
        if ($LastName == $params['LastName'] || $FirstName == $params['FirstName']) {
            return new CurrentRegistrationsResponse([
                'CurrentRegistrationsList' => new CurrentRegistrationsRegistration(),
            ]);
        }

        $tableName = ETPMessage::tableName();

        $query = static::getQuery()
            ->addSelect([
                'base_declarant' => new Expression($tableName . ".message #>> '{ApplicationData,Declarant}'"),
            ])
            ->where([
                'AND',
                ['phone' => PhoneHelper::extractMosRuPhoneNumber($Phone)],
                ['IN', 'visits.status', [VisitStatus::CHANGED, VisitStatus::NEW, VisitStatus::TRANSFER]],
                [$tableName . '.last_name' => $LastName],
                [$tableName . '.first_name' => $FirstName],
            ]);

        if (!empty($MiddleName)) {
            $query->andWhere([$tableName . '.middle_name' => $MiddleName]);
        }

        $registrations = $query->asArray()->all();

        if (empty($registrations)) {
            return new CurrentRegistrationsResponse([
                'CurrentRegistrationsList' => new CurrentRegistrationsRegistration(),
            ]);
        }

        // $registrations = static::appendAddresses($registrations);
        // $registrations = static::appendServiceList($registrations);

        $currentRegistrations = [];
        foreach ($registrations as $registration) {
            $currentRegistrations[] = static::getRegistrationObjectV2($registration);
        }

        return new CurrentRegistrationsResponse([
            'CurrentRegistrationsList' => new CurrentRegistrationsRegistration([
                'Registration' => $currentRegistrations,
            ]),
        ]);
    }

    /**
     * @param array $registration
     * @return \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberRegistration
     */
    protected static function getRegistrationObjectV2(array $registration)
    {
        $service_properties = Json::decode(ArrayHelper::remove($registration, 'service_properties'));

        $Animal = new RegByServiceNumberAnimal(self::convertKeyCase(ArrayHelper::remove($registration, 'animal')));
        if (!empty($service_properties['PetId'])) {
            $Animal->PetId = $service_properties['PetId'];
        }
        if (empty($Animal->SexAnimal)) {
            $Animal->SexAnimal = ETP::SEX_ANIMAL_EMPTY;
        }

        $serviceList = [];
        foreach ($service_properties['ServiceList'] as $serviceData) {
            if (ArrayHelper::isAssociative($serviceData, false)) {
                $service = self::prepareServiceListEntry($serviceData);
                if ($service !== null) {
                    $serviceList[] = $service;
                }
            } else {
                foreach ($serviceData as $entry) {
                    $service = self::prepareServiceListEntry($entry);
                    if ($service !== null) {
                        $serviceList[] = $service;
                    }
                }
            }
        }
        $serviceListWrapper = new RegByServiceNumberServiceWrapper();
        $serviceListWrapper->Service = $serviceList;

        $declarant = Json::decode(ArrayHelper::remove($registration, 'base_declarant'));
        $Owner = new RegByServiceNumberOwner($declarant);

        if ($registration['call_to_home'] === true) {
            $AddressCall = new RegByServiceNumberAddressCall();
            $AddressCall->FiasCode = ArrayHelper::getValue($declarant, 'FactAddress.FiasCode');
            $AddressCall->AddressName = ArrayHelper::getValue($declarant, 'FactAddress.POBox');
        } else {
            $AddressCall = null;
        }

        if (!empty($registration['logs'])) {
            $registration['logs'] = Json::decode($registration['logs']);
        }

        $registrationObj = new RegByServiceNumberRegistration(self::convertKeyCase($registration));
        $registrationObj->Animal = $Animal;
        $registrationObj->Owner = $Owner;
        $registrationObj->AddressCall = $AddressCall;
        $registrationObj->ServiceList = $serviceListWrapper;

        return $registrationObj;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    protected static function getQuery()
    {
        $tableName = ETPMessage::tableName();

        return ETPMessage::find()
            ->select([
                'message_id' => $tableName . '.id',
                'visit_id',
                $tableName . '.service_number',
                'ticket_number' => 'visits.ticket_number',
                'specialist_id' => 'specialists.id_user',
                'org_id' => 'visits.id_organization',
                'visit_date' => 'visits.start_dttm',
                'visits.duration',
                'call_to_home' => new Expression("COALESCE((" . $tableName . ".message#>>'{ApplicationData,ServiceProperties,CallToHome}')::boolean, false)"),
                'logs' => new Expression('(select json_agg(etp.status_log.etp_status) from etp.status_log where etp.status_log.service_number = ' . $tableName . '.service_number)'),
                'service_properties' => new Expression($tableName . ".message#>>'{ApplicationData,ServiceProperties}'"),
            ])
            ->with(['animal' => function ($query) {
                /** @var $query \yii\db\ActiveQuery */
                $query->select([
                    'pets.id',
                    'id_species',
                    'id_breed',
                    'species_id' => 'id_species',
                    'species_name' => 'lower(species.name)',
                    'breed_id' => 'id_breed',
                    'breed_name' => 'lower(breeds.name)',
                    'nickname_animal' => 'pets.name',
                    'chip_animal' => new Expression(
                        "(select identification_code from pet_identification where id_pet = pets.id and id_ident_type = 1 and main_flag = true)"
                    ),
                    'birthdate_animal' => 'birthday',
                    new Expression(
                        "CASE 
                                WHEN (sex = 'f') THEN :female  
                                WHEN (sex = 'm') THEN :male
                                ELSE null
                            END AS sex_animal",
                        [
                            'female' => ETP::SEX_ANIMAL_FEMALE,
                            'male' => ETP::SEX_ANIMAL_MALE,
                        ]
                    ),
                ])
                    ->joinWith(['species', 'breed']);
            }])
            ->innerJoin('visits', 'visits.id = ' . $tableName . ' .visit_id')
            ->innerJoin('visits_specialists', 'visits_specialists.id_visit = visits.id')
            ->innerJoin('specialists', 'specialists.id = visits_specialists.id_specialist');
    }

    /**
     * @param array $serviceData
     * @return \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberService|null
     */
    private static function prepareServiceListEntry($serviceData)
    {
        if (empty($serviceData['ServiceId'])) {
            return null;
        }
        $service = new RegByServiceNumberService();
        $service->Id = $serviceData['ServiceId'];
        if (!empty($serviceData['Name'])) {
            $service->Name = $serviceData['Name'];
        } else {
            $mosruService = MosRuServices::findOne(['id' => $serviceData['ServiceId']]);
            if ($mosruService !== null) {
                $service->Name = $mosruService->name;
            }
        }

        return $service;
    }
}
