<?php

namespace app\common\models;

class VisitStatus
{
    const
        NEW = 'N',
        ACCEPTED = 'P', // статус принято для НВП
        CHANGED = 'C',
        IN_WORK = 'W',
        CANCELED = 'A',
        FINISHED = 'F', // прием завершен и оплата подтверждена администратором
        TRANSFER = 'T',
        TIMEOUT = 'D', // время ожидания истекло/прошло 2 недели с момента начала визита, клиент не явился
        FINISHED_UNPAYED = 'O' // прием завершен, но не оплачен
    ;

    protected static $switchList = [
        self::NEW => [
            self::IN_WORK, self::CHANGED, self::CANCELED, self::TRANSFER
        ],
        self::CHANGED => [
            self::IN_WORK, self::CANCELED, self::TRANSFER
        ],
        self::IN_WORK => [
            self::FINISHED,
            self::CANCELED,
            self::FINISHED_UNPAYED
        ],
        self::FINISHED_UNPAYED => [
            self::FINISHED
        ],
        self::TRANSFER => [
            self::CHANGED, self::CANCELED
        ],
        self::CANCELED => false,
        self::FINISHED => false,
        self::TIMEOUT => false
    ];

    public static function getStatusList()
    {
        return [
            self::NEW, self::CHANGED, self::ACCEPTED, self::IN_WORK, self::CANCELED, self::FINISHED, self::TRANSFER, self::TIMEOUT, self::FINISHED_UNPAYED
        ];
    }

    public static function availableSwitch($status)
    {
        return self::$switchList[$status];
    }

    /**
     * Возвращает массив статусов, ИЗ КОТОРЫХ возможен переход в УКАЗАННЫЙ СТАТУС
     * или FALSE если невозможен переход в этот статус
     * @param $status
     * @return array|bool
     */
    public static function reverseAvailableSwitch($status)
    {
        $result = false;
        foreach (self::$switchList as $key => $statuses) {
            if ((is_array($statuses) && in_array($status, $statuses) ||
                (is_string($statuses) && $statuses == $status))) {
                $result[] = $key;
            }
        }

        return $result;
    }
}
