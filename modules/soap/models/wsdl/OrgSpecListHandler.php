<?php

namespace app\modules\soap\models\wsdl;

use app\common\soap\SoapException;
use app\models\db\Organizations;
use app\modules\soap\models\etp\ETPHelper;
use app\modules\soap\models\MosruServices;
use app\modules\soap\models\Visits;
use app\modules\soap\skeletons\rq\GetOrgSpecList;
use app\modules\soap\skeletons\rq\OrgList;
use app\modules\soap\skeletons\types\OrgSpecListByDateType;
use app\modules\soap\skeletons\types\OrgSpecListType;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\validators\DateValidator;

/**
 * Class OrgSpecListHandler
 * @package app\modules\soap\models\wsdl
 */
class OrgSpecListHandler
{
    /**
     * @var int Кол-во ближайших орг-ций при вызове на дом
     */
    const NEAREST_NMB = 3;
    
    /**
     * @param OrgSpecListType|OrgSpecListByDateType $get_org_spec_list
     * @return GetOrgSpecList
     */
    public static function getOrgSpecList($get_org_spec_list)
    {
        $services = $get_org_spec_list->services;
        $callToHome = $get_org_spec_list->call_to_home ?? false;
        $dateHours = $get_org_spec_list->date_hours ?? null;

        $list = new OrgList();
        $list->org = self::prepareList($services, $callToHome, $dateHours);

        $response = new GetOrgSpecList();
        $response->org_spec_list = $list;

        return $response;
    }

    /**
     * @param \app\modules\soap\skeletons\types\ServicesIdType[] $services
     * @param bool $callToHome
     * @param \app\modules\soap\skeletons\types\DateHoursType $dateHours
     * @return array
     */
    protected static function prepareList($services, $callToHome = false, $dateHours = null)
    {
        try {
            if (empty($services->id)) {
                throw new SoapException("Не указаны ID услуг для поиска");
            }

            $servicesIds = is_array($services->id) ? $services->id : array($services->id);

            self::checkServices($servicesIds, $callToHome);

            if (!is_null($dateHours)) {
                if (empty($dateHours)) {
                    throw new SoapException("Не передана дата");
                }

                if (empty($dateHours->hours_since)) {
                    $dateHours->hours_since = '00:00';
                }

                if (empty($dateHours->hours_till)) {
                    $dateHours->hours_till = '23:59';
                }

                $validator = new DateValidator();

                $validator->format = "php:Y-m-d";
                if (!$validator->validate($dateHours->date)) {
                    throw new SoapException("Передана неверная дата");
                }

                $validator->format = "php:H:i";

                if (!$validator->validate($dateHours->hours_since)) {
                    throw new SoapException("Передан неверный параметр hours_since");
                }

                if (!$validator->validate($dateHours->hours_till)) {
                    throw new SoapException("Передан неверный параметр hours_till");
                }

                $startTime = strtotime("{$dateHours->date} {$dateHours->hours_since}");
                $endTime = strtotime("{$dateHours->date} {$dateHours->hours_till}");

                if (isset($startTime) && isset($endTime) && $startTime >= $endTime) {
                    throw new SoapException("Время начала должно быть меньше времени окончания");
                }

                if ($dateHours->date == date('Y-m-d')) {
                    list($dateHours->hours_since, $dateHours->hours_till) = ETPHelper::prepareDate(
                        $callToHome, $dateHours->date, $dateHours->hours_since, $dateHours->hours_till
                    );
                }
            }

            return self::find($servicesIds, $dateHours, $callToHome);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param array $servicesIds
     * @param bool $call_to_home
     * @throws SoapException
     */
    protected static function checkServices(array $servicesIds, bool $call_to_home)
    {
        // По умолчанию все услуги доступны в клинике, такой случай не проверяем
        if (!$call_to_home) {
            return;
        }

        /** @var MosruServices[] $services */
        $services = MosruServices::find()
            ->where(['id' => $servicesIds])
            ->andWhere(['at_home' => false])
            ->all();

        if (!empty($services)) {
            $msg = "Следующие услуги не доступны для вызова на дом:";
            foreach ($services as $service) {
                $msg .= "\n#{$service->id} {$service->name}";
            }
            throw new SoapException($msg);
        }
    }

    /**
     * @param array $servicesIds
     * @param null|\stdClass $dateHours
     * @param bool $callToHome
     * @param array|null $addressCoords
     * 
     * @return array
     * 
     * @throws \yii\db\Exception
     */
    protected static function find(array $servicesIds, $dateHours = null, $callToHome = false, array $addressCoords = null) : array
    {
        $query = (new Query())
            ->from('services_specialists')
            ->distinct()
            ->select(['specialists.id_user', 'specialists.id_organization'])
            ->innerJoin('specialists', 'specialists.id = services_specialists.id_specialist AND specialists.expel_date IS NULL')
            ->where(['services_specialists.id_service' => $servicesIds])
            ->groupBy(['specialists.id_user', 'specialists.id_organization'])
            ->having(['count(*)' => count($servicesIds)]);
        //BUG если пользователь два раза заведен в клинику, то не отображается на mos.ru            

        $interval = ($callToHome) ? Visits::CALL_TO_HOME_CHANGE_TIME : Visits::IN_CLINIC_CHANGE;
        if ($dateHours) {
            list($dateFrom, $dateTo) = static::prepareDates($dateHours);
        } else {
            $dateFrom = date('Y-m-d H:i:s', time() + $interval);
            $dateTo = date('Y-m-d 23:59:59', time() + 60 * 60 * 24 * 30); // 30 дней;
        }

        $start = (new \DateTime())->add(new \DateInterval("PT{$interval}S"));
        $startDate = new \DateTime($dateFrom);
        if ($start > $startDate) {
            $dateFrom = $start->format('Y-m-d H:i:s');
        }

        $service_ids_all='';
        for($i=0; $i<count($servicesIds); $i++){
            if($service_ids_all){$service_ids_all.=",";}
            $service_ids_all.=$servicesIds[$i];
        }

        $sql = '';
        $bef=($callToHome) ? Visits::CALL_TO_HOME_BEFORE_TIME : 0;
        $aff=($callToHome) ? Visits::CALL_TO_HOME_AFTER_TIME : MosruServices::getServicesCooldown($servicesIds);
        $dur=MosruServices::getServicesDuration($servicesIds);
        
        if($callToHome){
            $sql="select * from mosru.get_timesheets_call_to_home_v1('".$service_ids_all."','".$dateFrom."'::timestamp,'".$dateTo."'::timestamp, ".$bef.",".$aff.",".$dur.")";
        }else{
            $sql="select * from mosru.get_timesheets_v1('".$service_ids_all."','".$dateFrom."'::timestamp,'".$dateTo."'::timestamp, ".$bef.",".$aff.",".$dur.")";
        }
        $command = \Yii::$app->db->createCommand($sql);

        $result = $command->queryAll();
        if (empty($result)) {
            return [];
        }

        $grouppedByOrg = self::processResult($result);
        if (null === $addressCoords || 2 > count($grouppedByOrg)) {
            return array_values($grouppedByOrg);
        }

        // Иначе нужно отсортировать орг-ции по удалённости от адреса и вернуть первые N=self::NEAREST_NMB
        $orderByExpr = <<<sql
ST_Distance(
    'POINT({$addressCoords[0]} {$addressCoords[1]})'::geometry,
    ('POINT(' || o.latitude || ' ' || o.longitude || ')')::geometry
) ASC
sql;
        $nearestOrgs = Organizations::find()
            ->alias('o')
            ->select(['o.id'])
            ->where(['o.id' => ArrayHelper::getColumn($grouppedByOrg, 'id', false)])
            ->orderBy(new \yii\db\Expression($orderByExpr))
            ->limit(self::NEAREST_NMB)
            ->column()
        ;

        $filtered = array_map(function ($id) use ($grouppedByOrg) {
            return $grouppedByOrg[$id];
        }, $nearestOrgs);

        // !
        // Вернуть нужно строго индексированный массив, иначе SOAP-сервер не может сформировать ответ
        return array_values($filtered);
    }

    /**
     * Формирование для правильного ответа
     * @param array $organizations
     * @return array
     */
    protected static function processResult(array $organizations) : array
    {
        $result = [];
        foreach ($organizations as $organization) {
            if (!isset($result[$organization['id_organization']])) {
                $result[$organization['id_organization']] = [
                    'id' => $organization['id_organization'],
                    'specialists' => []
                ];
            }
            $result[$organization['id_organization']]['specialists'][] = $organization['id_user'];
        }

        return $result;
    }

    /**
     * @param \app\modules\soap\skeletons\types\DateHoursType $dateHours
     * @return string[]
     */
    protected static function prepareDates($dateHours)
    {
        $dateFrom = $dateHours->date . ' ' . (isset($dateHours->hours_since) ? $dateHours->hours_since : '00:00:00');
        $dateTo = $dateHours->date . ' ' . (isset($dateHours->hours_till) ? $dateHours->hours_till : '23:59:59');

        return [$dateFrom, $dateTo];
    }
}

