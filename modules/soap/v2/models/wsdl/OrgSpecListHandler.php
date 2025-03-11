<?php

namespace app\modules\soap\v2\models\wsdl;

use yii\validators\DateValidator;

use app\common\efsp\EfspWrapper;
use app\common\soap\SoapException;
use app\modules\soap\models\etp\ETPHelper;
use app\modules\soap\models\wsdl\OrgSpecListHandler as OrgSpecListHandlerV1;
use app\modules\soap\v2\skeletons\rq\OrgList;
use app\modules\soap\v2\skeletons\rq\OrgSpecList;
use app\modules\soap\v2\skeletons\types as SoapTypes;
use app\modules\soap\log as SoapLog;

/**
 * Class OrgSpecListHandler
 * @package app\modules\soap\v2\models\wsdl
 */
class OrgSpecListHandler extends OrgSpecListHandlerV1 implements SoapLog\LoggerAwareInterface
{
    use SoapLog\LoggerAwareTrait;
    use HandlerTrait;

    /**
     * @param SoapTypes\ServicesIdType $services
     * @param bool $callToHome
     * @param SoapTypes\DateHoursType $dateHours
     * @param null|string $fiasCode
     * 
     * @return soap\v2\skeletons\rq\OrgSpecList
     */
    public static function getOrgSpecList($services, $callToHome = false, $dateHours = null, $fiasCode = null)
    {
        $list = new OrgList();
        $orgs = self::prepareList($services, $callToHome, $dateHours, $fiasCode);
        $orgs = self::convertKeyCase($orgs);
        $list->Org = $orgs;

        $response = new OrgSpecList();
        $response->OrgSpecList = $list;

        return $response;
    }

    /**
     * @param SoapTypes\ServicesIdType $services
     * @param bool $callToHome
     * @param SoapTypes\DateHoursType $dateHours
     * @param null|string $fiasCode
     * 
     * @return array
     */
    protected static function prepareList($services, $callToHome = false, $dateHours = null, $fiasCode = null)
    {
        try {
            if (empty($services->Id)) {
                throw new SoapException("Не указаны ID услуг для поиска");
            }
            // TODO:mosru-fiascode-ready Раскомментировать, когда мосру начнёт присылать FiasCode при вызове на дом.
            // if ($callToHome && empty($fiasCode)) {
            //     throw new SoapException("При вызове на дом необходимо указание FiasCode");
            // }

            $servicesIds = is_array($services->Id) ? $services->Id : [$services->Id];

            self::checkServices($servicesIds, $callToHome);

            if (!is_null($dateHours)) {
                if (empty($dateHours)) {
                    throw new SoapException("Не передана дата");
                }

                self::normalizeDateHours($dateHours);
                self::validateDateHours($dateHours, $callToHome);

                // TODO: Почему не сделать это перед валидацией?
                if ($dateHours->Date == date('Y-m-d')) {
                    list($dateHours->HoursSince, $dateHours->HoursTill) = ETPHelper::prepareDate(
                        $callToHome, $dateHours->Date, $dateHours->HoursSince, $dateHours->HoursTill
                    );
                }
            }

            $addressCoords = null;
            if ($callToHome && !empty($fiasCode)) {
                // TODO:DI
                $efspWrapper =  new EfspWrapper();
                if (null === $addressCoords = $efspWrapper->getAddressCoords($fiasCode)) {
                    // TODO:mosru-fiascode-ready Раскомментировать, когда мосру начнёт присылать FiasCode при вызове на дом.
                    // throw new SoapException('Адрес по переданном коду ФИАС не найден, регистрация вызова на дом невозможна');
                }
            }

            return self::find($servicesIds, $dateHours, $callToHome, $addressCoords);
        } catch (\Exception $e) {
            \Yii::error('OrgSpecListHandler: Exception: '. $e->getMessage());
            return [];
        }
    }

    /**
     * @param SoapTypes\DateHoursType $dateHours
     */
    protected static function normalizeDateHours($dateHours)
    {
        if (empty($dateHours->Date)) {
            $dateHours->Date = date('Y-m-d');
        }

        if (empty($dateHours->HoursSince)) {
            $dateHours->HoursSince = '00:00';
        } elseif (2 < count($chunks = explode(':', $dateHours->HoursSince))) {
            $dateHours->HoursSince = implode(':', array_slice($chunks, 0, 2));
        }

        if (empty($dateHours->HoursTill)) {
            $dateHours->HoursTill = '23:59';
        } elseif (2 < count($chunks = explode(':', $dateHours->HoursTill))) {
            $dateHours->HoursTill = implode(':', array_slice($chunks, 0, 2));
        }
    }

    /**
     * @param SoapTypes\DateHoursType $dateHours
     * @param bool $callToHome
     * 
     * @throws SoapException
     */
    protected static function validateDateHours($dateHours, $callToHome)
    {
        $validator = new DateValidator();

        $validator->format = "php:Y-m-d";
        if (!$validator->validate($dateHours->Date)) {
            throw new SoapException("Передана неверная дата");
        }

        $validator->format = "php:H:i";

        if (!$validator->validate($dateHours->HoursSince)) {
            throw new SoapException("Передан неверный параметр HoursSince");
        }

        if (!$validator->validate($dateHours->HoursTill)) {
            throw new SoapException("Передан неверный параметр HoursTill");
        }

        $startTime = strtotime("{$dateHours->Date} {$dateHours->HoursSince}");
        $endTime = strtotime("{$dateHours->Date} {$dateHours->HoursTill}");

        if (isset($startTime) && isset($endTime) && $startTime >= $endTime) {
            throw new SoapException("Время начала должно быть меньше времени окончания");
        }
    }

    /**
     * @param SoapTypes\DateHoursType $dateHours
     * 
     * @return string[]
     */
    protected static function prepareDates($dateHours)
    {
        $dateFrom = $dateHours->Date . ' ' . (isset($dateHours->HoursSince) ? $dateHours->HoursSince : '00:00:00');
        $dateTo = $dateHours->Date . ' ' . (isset($dateHours->HoursTill) ? $dateHours->HoursTill : '23:59:59');

        return [$dateFrom, $dateTo];
    }
}
