<?php

namespace app\modules\soap\models\etp;

use app\modules\soap\models\etp\status\Status1050_1;
use app\modules\soap\models\etp\status\Status1050_2;
use app\modules\soap\models\etp\status\Status106999;
use app\modules\soap\models\etp\status\Status1080_3;
use app\modules\soap\models\etp\status\Status1080_4;
use app\modules\soap\models\etp\status\Status1080_5;
use app\modules\soap\models\etp\status\Status1090_1;
use app\modules\soap\models\etp\status\Status1090_2;
use app\modules\soap\models\etp\status\Status1152;
use app\modules\soap\models\etp\status\Status8011;
use app\modules\soap\models\etp\status\Status8021_1;
use app\modules\soap\models\etp\status\Status8021_2;
use app\modules\soap\models\etp\status\Status8031_1;
use app\modules\soap\models\etp\status\Status8031_2;
use app\modules\soap\models\etp\status\Status8031_3;
use app\modules\soap\models\etp\status\Status8031_4;
use app\modules\soap\models\Visits;
use app\modules\soap\models\etp\status\Status10090;
use app\modules\soap\models\etp\status\Status10091;
use app\modules\soap\models\etp\status\Status1010;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\etp\status\Status103099;
use app\modules\soap\models\etp\status\Status1050;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status1068;
use app\modules\soap\models\etp\status\Status1069;
use app\modules\soap\models\etp\status\Status1075;
use app\modules\soap\models\etp\status\Status1080_1;
use app\modules\soap\models\etp\status\Status1080_2;
use app\modules\soap\models\etp\status\Status1090;
use app\modules\soap\models\etp\status\Status1168;
use app\modules\soap\models\etp\status\Status1169;
use app\modules\soap\models\etp\status\Status8021;
use app\modules\soap\models\etp\status\StatusInterface;

class ETPHelper
{
    /**
     * @param $callToHome
     * @param $date
     * @param $hours_since
     * @param $hours_till
     * @return array
     */
    public static function prepareDate($callToHome, $date, $hours_since, $hours_till)
    {
        $startTime = strtotime("{$date} {$hours_since}");
        $endTime = strtotime("{$date} {$hours_till}");

        $changeTime = $callToHome ? Visits::CALL_TO_HOME_CHANGE_TIME : Visits::IN_CLINIC_CHANGE;

        if (($startTime - time()) < $changeTime) {
            $availableStarttime = time() + $changeTime;
            $hours_since = date('H:i', $availableStarttime);
            if ($endTime < $availableStarttime) {
                $hours_till = $hours_since;
            }
        }

        return [$hours_since, $hours_till];
    }

    /**
     * @param $status
     * @return StatusInterface
     * @throws \Exception
     */
    public static function getStatus($status)
    {
        switch ($status) {
            case Status1010::CODE:
                return new Status1010();

            case Status1050::CODE:
                return new Status1050();

            case Status1050_1::CODE:
                return new Status1050_1();

            case Status1050_2::CODE:
                return new Status1050_2();

            case Status1053::CODE:
                return new Status1053();

            case Status1068::CODE:
                return new Status1068();

            case Status1069::CODE:
                return new Status1069();

            case Status1075::CODE:
                return new Status1075();

            case Status1080_1::CODE:
                return new Status1080_1();

            case Status1080_2::CODE:
                return new Status1080_2();

            case Status1080_3::CODE:
                return new Status1080_3();

            case Status1080_4::CODE:
                return new Status1080_4();

            case Status1080_5::CODE:
                return new Status1080_5();

            case Status1090::CODE:
                return new Status1090();

            case Status1090_1::CODE:
                return new Status1090_1();

            case Status1090_2::CODE:
                return new Status1090_2();

            case Status1152::CODE:
                return new Status1152();

            case Status1168::CODE:
                return new Status1168();

            case Status1169::CODE:
                return new Status1169();

            case Status8011::CODE:
                return new Status8011();

            case Status8021::CODE:
                return new Status8021();

            case Status8021_1::CODE:
                return new Status8021_1();

            case Status8021_2::CODE:
                return new Status8021_2();

            case Status8031_1::CODE:
                return new Status8031_1();

            case Status8031_2::CODE:
                return new Status8031_2();

            case Status8031_3::CODE:
                return new Status8031_3();

            case Status8031_4::CODE:
                return new Status8031_4();

            case Status10090::CODE:
                return new Status10090();

            case Status10091::CODE:
                return new Status10091();

            case Status10190::CODE:
                return new Status10190();

            case Status10191::CODE:
                return new Status10191();

            case Status103099::CODE:
                return new Status103099();

            case Status106999::CODE:
                return new Status106999();

            default:
                throw new \Exception('Неизвестный статус');
        }

    }
}
