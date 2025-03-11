<?php

namespace app\modules\soap\models\wsdl;

use app\common\helpers\PhoneHelper;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberRegistration;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\modules\soap\models\etp\ETPMessage;
use app\modules\soap\skeletons\current_registrations\CurrentRegistrationsResponse;
use app\modules\soap\skeletons\current_registrations\CurrentRegistrationsRegistration;
use app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberResponse;
use app\common\models\VisitStatus;

/**
 * Class CurrentRegistrationsHandler
 * @package app\modules\soap\models\wsdl
 */
class CurrentRegistrationsHandler
{
    /**
     * @param string $ServiceNumber
     * @return \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberResponse
     */
    public static function getRegistrationByServiceNumber($ServiceNumber)
    {
        $registrations = static::getQuery()
            ->addSelect([
                'base_declarant' => new Expression("etp.message.message #>> '{xml,CoordinateDataMessage,SignService,Contacts,BaseDeclarant}'"),
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

        $registrations = static::appendAddresses($registrations);
        $registrations = static::appendServiceList($registrations);

        $registration = is_array($registrations) ? $registrations[0] : $registrations;

        $response->registration_by_servicenumber = static::getRegistrationObject($registration);

        return $response;
    }

    /**
     * @param string $LastName
     * @param string $FirstName
     * @param string $Phone
     * @param string $MiddleName
     * @return \app\modules\soap\skeletons\current_registrations\CurrentRegistrationsResponse
     */
    public static function getCurrentRegistrations($LastName, $FirstName, $Phone, $MiddleName = null)
    {
        //для неавторизованных пользователей метод ничего не возвращает
        $params = \Yii::$app->getModule('soap')->params['unauthorized_message'];
        if ($LastName == $params['LastName'] || $FirstName == $params['FirstName']) {
            return new CurrentRegistrationsResponse([
                'current_registation_list' => new CurrentRegistrationsRegistration(),
            ]);
        }

        $query = static::getQuery()
            ->where([
                'AND',
                ['phone' => PhoneHelper::extractMosRuPhoneNumber($Phone)],
                ['IN', 'visits.status', [VisitStatus::CHANGED, VisitStatus::NEW, VisitStatus::TRANSFER]],
                ['etp.message.last_name' => $LastName],
                ['etp.message.first_name' => $FirstName],
            ]);

        if (!empty($MiddleName)) {
            $query->andWhere(['etp.message.middle_name' => $MiddleName]);
        }

        $registrations = $query->asArray()->all();

        $registrations = static::appendAddresses($registrations);
        $registrations = static::appendServiceList($registrations);

        $currentRegistrations = [];
        foreach ($registrations as $registration) {
            $currentRegistrations[] = static::getRegistrationObject($registration);
        }

        return new CurrentRegistrationsResponse([
            'current_registation_list' => new CurrentRegistrationsRegistration([
                'registration' => $currentRegistrations,
            ]),
        ]);
    }

    /**
     * @param array $registration
     * @return RegByServiceNumberRegistration
     */
    protected static function getRegistrationObject(array $registration): RegByServiceNumberRegistration
    {
        $properties = static::prepareServiceProperties($registration);

        return new RegByServiceNumberRegistration($properties);
    }

    /**
     * Добавляет адреса к записям, если call_to_home = true
     * @param array $messages
     * @return array
     */
    protected static function appendAddresses($messages)
    {
        if (empty($messages)) {
            return $messages;
        }

        $list = ArrayHelper::index($messages, 'message_id');
        $messages_ids = array_keys($list);

        $addresses = static::selectAddresses($messages_ids);

        foreach ($addresses as $key => $value) {
            if (array_key_exists($key, $list)) {
                $list[$key]['address_call'] = $value;
            }
        }

        return array_values($list);
    }

    /**
     * @param array $messages
     * @return array
     */
    protected static function appendServiceList($messages)
    {
        if (empty($messages)) {
            return $messages;
        }

        $result = [];
        foreach ($messages as $message) {
            $message['servicelist'] = static::selectServices($message['message_id']);
            $result[] = $message;
        }

        return $result;
    }

    /**
     * @param int $id
     * @return array
     */
    protected static function selectServices($id)
    {
        $message = ETPMessage::find()
            ->select([
                new Expression("message->'xml'->'CoordinateDataMessage'->'SignService'->'CustomAttributes'->'ServiceProperties'->'servicelist'->'service' as services"),
            ])
            ->where(['id' => $id])
            ->asArray()
            ->one();

        $services = json_decode($message['services']);
        if (!is_array($services)) {
            $ids = [$services->service_id];
        } else {
            $ids = ArrayHelper::getColumn($services, 'service_id');
        }

        $result = (new Query())->from('mosru.services_all')
            ->where(['id' => $ids])
            ->all();

        return $result;
    }

    /**
     * @param array $messages_ids
     * @return array
     */
    protected static function selectAddresses($messages_ids)
    {
        $result = ETPMessage::find()
            ->select([
                'id',
                new Expression("message->'xml'->'CoordinateDataMessage'->'SignService'->'Contacts'->'BaseDeclarant'->'FactAddress'->>'FiasCode' AS fias_code"),
                new Expression("message->'xml'->'CoordinateDataMessage'->'SignService'->'Contacts'->'BaseDeclarant'->'FactAddress'->>'POBox' AS address_name"),
            ])
            ->where([
                'AND',
                ['IN', 'id', $messages_ids],
                new Expression("message->'xml'->'CoordinateDataMessage'->'SignService'->'CustomAttributes'->'ServiceProperties'->>'call_to_home' = 'true'"),
            ])
            ->asArray()
            ->all();

        return ArrayHelper::index($result, 'id');
    }

    /**
     * @return ActiveQuery
     */
    protected static function getQuery()
    {
        return ETPMessage::find()
            ->select([
                'message_id' => 'etp.message.id',
                'visit_id',
                'etp.message.service_number',
                'ticket_number' => 'visits.ticket_number',
                'specialist_id' => 'specialists.id_user',
                'org_id' => 'visits.id_organization',
                'visit_date' => 'visits.start_dttm',
                'visits.duration',
                'call_to_home' => new Expression("COALESCE((etp.message.message#>>'{xml,CoordinateDataMessage,SignService,CustomAttributes,ServiceProperties,call_to_home}')::boolean, false)"),
                'logs' => new Expression('(select  json_agg(etp.status_log.etp_status) from etp.status_log where etp.status_log.service_number = etp.message.service_number)'),
                'service_properties' => new Expression("message#>>'{xml,CoordinateDataMessage,SignService,CustomAttributes,ServiceProperties}'"),
            ])
            ->with(['animal' => function ($query) {
                /** @var $query ActiveQuery */
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
            ->innerJoin('visits', 'visits.id = etp.message.visit_id')
            ->innerJoin('visits_specialists', 'visits_specialists.id_visit = visits.id')
            ->innerJoin('specialists', 'specialists.id = visits_specialists.id_specialist');
    }

    /**
     * @param array $registration
     * @return array
     */
    protected static function prepareServiceProperties(array $registration): array
    {
        if (isset($registration['service_properties'])) {
            $service_properties = json_decode($registration['service_properties']);
            if (isset($service_properties->pet_id)) {
                $registration['animal']['pet_id'] = !is_array($service_properties->pet_id) ? $service_properties->pet_id : '';
            }
            unset($registration['service_properties']);
        }

        if (!isset($registration['animal']['pet_id'])) {
            $registration['animal']['pet_id'] = '';
        }

        $properties = [
            'service_number' => $registration['service_number'],
            'ticket_number' => $registration['ticket_number'],
            'specialist_id' => $registration['specialist_id'],
            'org_id' => $registration['org_id'],
            'servicelist' => $registration['servicelist'],
            'visit_date' => $registration['visit_date'],
            'duration' => $registration['duration'],
            'call_to_home' => $registration['call_to_home'],
            'animal' => $registration['animal'],
            'logs' => json_decode($registration['logs']),
        ];

        if (isset($registration['address_call'])) {
            $properties['address_call'] = $registration['address_call'];
        }
        if (isset($registration['base_declarant'])) {
            $owner = json_decode($registration['base_declarant']);
            $properties['owner'] = [
                'first_name' => (isset($owner->FirstName) && !empty($owner->FirstName)) ? $owner->FirstName : '',
                'middle_name' => (isset($owner->MiddleName) && !empty($owner->MiddleName)) ? $owner->MiddleName : '',
                'last_name' => (isset($owner->LastName) && !empty($owner->LastName)) ? $owner->LastName : '',
                'mobile_phone' => (isset($owner->MobilePhone) && !empty($owner->MobilePhone)) ? $owner->MobilePhone : '',
            ];
        } elseif (isset($registration['owner'])) {
            $properties['owner'] = $registration['owner'];
        }

        return $properties;
    }
}
