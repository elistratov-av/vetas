<?php

namespace app\modules\soap\controllers;

use app\common\soap\SoapAction;
use app\modules\adminfstek\traits\ExternalLogTrait;
use app\modules\soap\models\wsdl\CurrentRegistrationsHandler;
use app\modules\soap\models\wsdl\OrgSpecListHandler;
use app\modules\soap\models\wsdl\ReferencesHandler;
use app\modules\soap\models\wsdl\TimeSlotHandler;
use modules\adminfstek\behaviors\ExternalServiceAuth;
use yii\base\Controller;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class DefaultController extends BaseController
{
    use ExternalLogTrait;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = [];

        $authEnabled = ArrayHelper::getValue(\Yii::$app->params, 'external_services_auth_enabled');
        if ($authEnabled === true) {
            $behaviors['httpHeaderAuth'] = [
                'class' => ExternalServiceAuth::class,
                'pattern' => ArrayHelper::getValue($this->module->params, 'authToken'),
            ];
        }

        return $behaviors;
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        $result = parent::beforeAction($action);
        if ($result) {
            $this->logAuth();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        $soap = \Yii::$app->getModule('soap');
        return [
            'wsdl' => [
                'class' => SoapAction::class,
                'serviceUrl' => (!empty($soap->params['soapServiceUrl'])) ? $soap->params['soapServiceUrl'] : null,
                'wsdlUrl' => (!empty($soap->params['wsdlUrl'])) ? $soap->params['wsdlUrl'] : null,
                'wsdlOptions' => [
                    'namespace' => 'soap-service',
                    'serviceName' => 'Pets'
                ]
            ],
        ];
    }

    /**
     * @return \app\modules\soap\skeletons\species\SpeciesResponse
     * @soap
     */
    public function referenceSpecies()
    {
        return ReferencesHandler::getSpecies();
    }

    /**
     * @return \app\modules\soap\skeletons\orgs\OrgsResponse
     * @soap
     */
    public function referenceOrgs()
    {
        return ReferencesHandler::getOrgs();
    }

    /**
     * @return \app\modules\soap\skeletons\services\ServicesResponse
     * @param integer $species_id
     * @soap
     */
    public function referenceServices($species_id)
    {
        return ReferencesHandler::getServices($species_id);
    }

    /**
     * @param \app\modules\soap\skeletons\types\OrgSpecListType $get_org_spec_list
     * @return \app\modules\soap\skeletons\rq\GetOrgSpecList
     * @soap
     */
    public function orgSpecList($get_org_spec_list)
    {
        return OrgSpecListHandler::getOrgSpecList($get_org_spec_list);
    }

    /**
     * @param \app\modules\soap\skeletons\types\OrgSpecListByDateType $get_org_spec_list
     * @return \app\modules\soap\skeletons\rq\GetOrgSpecList
     * @soap
     */
    public function orgSpecListByDate($get_org_spec_list)
    {
        return OrgSpecListHandler::getOrgSpecList($get_org_spec_list);
    }

    /**
     * @soap
     * @param string $ServiceNumber
     * @return \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberResponse
     */
    public function getRegistrationByServiceNumber($ServiceNumber)
    {
       return CurrentRegistrationsHandler::getRegistrationByServiceNumber($ServiceNumber);
    }

    /**
     * @soap
     * @param string $LastName
     * @param string $FirstName
     * @param string $Phone
     * @param string $MiddleName
     * @return \app\modules\soap\skeletons\current_registrations\CurrentRegistrationsResponse
     */
    public function getCurrentRegistrations($LastName, $FirstName, $Phone, $MiddleName = NULL)
    {
        return CurrentRegistrationsHandler::getCurrentRegistrations($LastName, $FirstName, $Phone, $MiddleName);
    }

    /**
     * @soap
     * @param \app\modules\soap\skeletons\timeslots\TimeSlotRQ $get_time_slot_list
     * @return \app\modules\soap\skeletons\timeslots\TimeSlotsResponse
     * @throws \app\common\soap\SoapException
     * @throws \yii\db\Exception
     */
    public function timeSlotList($get_time_slot_list)
    {
        return TimeSlotHandler::getTimeSlotList($get_time_slot_list);
    }
}
