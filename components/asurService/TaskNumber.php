<?php

namespace app\common\components\asurService;

/**
 * Класс для генерации номера сообщения для передачи в АС УР
 *
 * Class TicketNumber
 * @package app\common\components\asurService
 */
class TaskNumber
{
    /** @var integer */
    private $number;

    /** @var string  */
    private $mask = '2071-9000142-010215-0000000/YY/Q';

    public function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * @return false|null|string
     * @throws \yii\db\Exception
     */
    public static function getNumber()
    {
        \Yii::$app->db->createCommand(
            'INSERT INTO asur.task_number(year, number) VALUES (:y, :n)
                  ON CONFLICT ON CONSTRAINT task_number_year_key 
                  DO UPDATE SET number = EXCLUDED.number + task_number.number',
            [
                ':y' => date('Y'),
                ':n' => 1
            ]
        )
            ->execute()
        ;

        return \Yii::$app->db->createCommand('select number from asur.task_number where year = :year', [
            ':year' => date('Y')
        ])
            ->queryScalar();
    }

    /**
     * Генерирует номер сообщения по заданной маске XXXX-SSSSSSS-AAAAAA-NNNNNNN/YY/Q, где:
     * • ХХХХ    – 20711
     * • SSSSSSS – 9000142*
     * • AAAAAA  – 010215*
     * • NNNNNNN – порядковый номер обращения за указанной государственной услугой в организации,
     *              зарегистрировавшей обращение, в текущем году
     * • YY      – последние 2 цифры года
     * • Q       – порядковый номер запроса
     *
     * Пример 2071-9000142-010215-000001/19/1
     *
     * @return string
     */
    public function make() :string
    {
        $ticket_number = $this->mask;
        //$ticket_number = str_replace("NNNNNNN", (string)sprintf("%07d", $number), $ticket_number);
        $ticket_number = str_replace('YY', date('y'), $ticket_number);
        $ticket_number = str_replace('Q', $this->number, $ticket_number);

        return $ticket_number;
    }

    /**
     * @return string
     */
    public function __toString() :string
    {
        return $this->make();
    }
}
