<?php

namespace app\common\helpers;

class TsRangeHelper
{
    public static function buildTsRange(string $startDttm, int $minutesLength)
    {
        if (!$minutesLength) {
            return null;
        }

        try {
            $dateEnd = new \DateTime($startDttm);
        } catch (\Throwable $e) {
            return null;
        }

        $interval = new \DateInterval("PT" . $minutesLength . "M");
        $dateEnd->add($interval);

        $timeRange = '["';
        $timeRange .= $startDttm . '","';
        $timeRange .= $dateEnd->format('Y-m-d H:i:s') . '")';

        return $timeRange;
    }
}
