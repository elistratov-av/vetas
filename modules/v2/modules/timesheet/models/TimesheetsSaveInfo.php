<?php


namespace app\modules\v2\modules\timesheet\models;

use app\common\models\VisitStatus;
use app\models\db\Organizations;
use app\models\db\Shifts;
use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\models\db\VaccinationStation;
use app\models\db\Visits;
use DateTime;
use Exception;
use Throwable;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * ПРИМЕЧАНИЕ: ранее (в предыдущей реализации в том числе)
 * возникала проблема из-за запроса
 * {
 *    "range": {
 *        "date_from": "2018-10-07",
 *        "days_count": 1
 *    },
 *    "specialists": [
 *        89
 *    ],
 *    "timesheet": [
 *        {
 *            "id_specialist": 89,
 *            "id_shift": 176,
 *            "from": "2018-10-07 09:00:00",
 *            "to": "2018-10-08 09:00:00",
 *            "manual_shift": false,
 *            "id_shift_type": 1
 *        }
 *    ]
 * }
 * РЕШЕНИЕ
 * Валидация и удаление происходит по разным диапазонам
 *
 * _date_from = 2018-10-07 00:00:00                     ###
 *                                                      ### ПРИ УДАЛЕНИИ СТИРАЕМ ЭТОТ ДИАПАЗОН
 * _date_to_for_clear_old = 2018-10-08 00:00:00         ###
 *
 * Теперь прибавим ещё день к $date_to, чтобы валидация пропускала время to из массива таймшитов
 *
 * _date_from = 2018-10-07 00:00:00                     ###
 *                                                      ### ВАЛИДИРУЕМ ТАЙМШИТЫ ПО ЭТОМУ ДИАПАЗОНУ
 * _date_to = 2018-10-09 00:00:00                       ###
 *
 */
class TimesheetsSaveInfo extends Model
{
    /**
     * @var array
     */
    public $range;

    /**
     * @var integer[]
     */
    public $specialists;

    /**
     * @var integer
     */
    public $id_organization;

    /**
     * @var integer
     */
    public $vaccination_station_id;

    /**
     * Тамшиты (входные данные)
     *
     * @var array
     */
    public $timesheet;

    /**
     * Таймшиты провалидированные, с доп полями и разбитые массивами id_specialist => [..timesheets..]
     *
     * @var array
     */
    private $formatted_timesheets = [];

    /**
     * Ключ - дочерний temp_uid, значение - родительский temp_uid
     * Необходимо для проставления parent_id в timesheets таблице
     *
     * @see $this->validateAndMapParentChild()
     * @see $this->getParentIdForChildTimesheet()
     * @var array
     */
    private $map_child_to_parent;

    /**
     * Ключ temp_uid родительского таймшита (или любого врехоуровнего)
     * Значение - его id в БД после вставки
     * Необходимо для проставления parent_id в timesheets таблице
     *
     * @see $this->saveParentTimesheets()
     * @see $this->getParentIdForChildTimesheet()
     * @var array
     */
    private $map_patent_temp_uid_to_db_id;

    /**
     * Дата начала диапазона
     *
     * @var DateTime
     */
    private $_date_from;

    /**
     * Дата начала диапазона + days_count + 1 (что бы валидация пропускала суточные смены)
     *
     * @var DateTime
     */
    private $_date_to;

    /**
     * Дата начала диапазона + days_count
     * ИСПОЛЬЗУЕТСЯ ДЛЯ ОПРЕДЕЛЕНИЯ ДИАПАЗОНА ОЧИСТКИ ОТ СТАРЫХ ТАЙМШИТОВ
     *
     * @var DateTime
     */
    private $_date_to_for_clear_old;

    /**
     * Текущая дата
     *
     * @var DateTime
     */
    private $_date_today;

    /**
     * @var Shifts[]
     */
    private $_organization_shifts;

    /**
     * Список визитов попавших в указанный диапазон
     *
     * @var array
     */
    private $_formatted_visits_in_date_range;

    /**
     * Формат даты в передаваемых timesheet объектах
     */
    const TIMESHEET_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Формат даты в прививочных пунктах
     */
    const VACCINATION_STATION_DATE_FORMAT = 'Y-m-d';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['id_organization', 'specialists', 'range', 'timesheet'],
                'required',
            ],
            ['id_organization', 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
            ['specialists', 'each', 'rule' => ['integer']],
            ['specialists', 'validateSpecialists', 'skipOnError' => true],
            ['range', 'validateRange', 'skipOnError' => true],
            ['vaccination_station_id', 'default', 'value' => null],
            [
                'vaccination_station_id',
                'exist',
                'targetClass' => VaccinationStation::class,
                'targetAttribute' => 'id',
                'skipOnError' => true,
            ],
        ];
    }

    /**
     * Проверим, что все врачи существуют в базе
     *
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     *
     * @see \yii\validators\InlineValidator
     */
    public function validateSpecialists($attribute, $params, $validator)
    {
        $count_specialists_in_db = Specialists::find()
            ->where(['IN', 'id', $this->specialists])
            ->count();

        if ($count_specialists_in_db !== count($this->specialists)) {
            $this->addError($attribute, 'Набор врачей specialists содержит несуществующего специалиста');
        }
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     *
     * @see \yii\validators\InlineValidator
     */
    public function validateRange($attribute, $params, $validator)
    {
        if (empty($this->range)) {
            $this->addError($attribute, 'Параметр range обязателен');
        }

        if (!is_array($this->range)) {
            $this->addError($attribute, 'Параметр range должен быть массивом');
        }

        if (!array_key_exists('days_count', $this->range)) {
            $this->addError($attribute, 'Параметр range должен содержать элемент days_count');
        }

        if (!is_integer($this->range['days_count'])) {
            $this->addError($attribute, 'Параметр range.days_count должен быть целым числом');
        }

        if (!array_key_exists('date_from', $this->range)) {
            $this->addError($attribute, 'Параметр range должен содержать элемент date_from');
        }

        $this->validateDate($this->range['date_from'], 'range.date_from');
    }

    /**
     * Валидация даты
     *
     * @param        $dateText
     * @param string $dateName
     */
    private function validateDate($dateText, $dateName = 'date_from')
    {
        $matches = [];
        $result = preg_match('~^((\d{4})\-(\d{1,2})\-(\d{1,2}))$~', $dateText, $matches);

        if ($result !== 1) {
            $this->addError($dateName, "Параметр {$dateName} имеет невалидный формат");
        } elseif (!isset($matches[2], $matches[3], $matches[4])) {
            $this->addError($dateName, "Параметр {$dateName} имеет невалидный формат");
        } elseif (checkdate($matches[3], $matches[4], $matches[2]) === false) {
            $this->addError($dateName, "Параметр {$dateName} имеет невалидный формат");
        }
    }

    /**
     * После валидации по rules можно подтянуть данные из бд
     * и инициализировать переменные
     */
    protected function initInternalVariables()
    {
        $days_count = $this->range['days_count'];

        $this->_date_from = date_create($this->range['date_from']);
        $temp_date = clone $this->_date_from;

        $this->_date_to_for_clear_old = $temp_date->modify("+ {$days_count} days");
        /*
         * +1 день к days_count,
         * что бы валидация дат таймшитов разрешала смены в сутки
         * и/или переходящие через сутки
         */
        // $days_count++;
        $temp_date = clone $this->_date_from;
        $this->_date_to = $temp_date->modify("+ {$days_count} days");

        $this->_date_today = date_create('today');

        $this->_organization_shifts = Shifts::find()
            ->where([
                'OR',
                [
                    'AND',
                    ['<>', 'shift_type.type', ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION],
                    ['id_organization' => $this->id_organization],
                ],
                ['shift_type.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION],
            ])
            ->joinWith('type')
            ->with('vaccinationStation')
            ->indexBy('id')
            ->all();
    }

    /**
     * Сохраняем все в бд
     *
     * @return integer|false Кол-во помеченных "к переносу" приемов | FALSE при ошибке
     *
     * @throws \yii\db\Exception
     */
    public function save()
    {
        try {
            $this->initInternalVariables();
            $this->validateAndFormatTimesheets();

            if ($this->hasErrors()) {
                return false;
            }

            return $this->internalSave();
        } catch (Exception | Throwable $e) {
            if (!empty(Timesheets::getDb()->transaction)) {
                Timesheets::getDb()->transaction->rollBack();
            }
            $this->addError('timesheet', 'Возникла ошибка при сохранении');
            return false;
        }
    }

    /**
     * Вынесено в отдельный метод ради удобства перехвата исключений
     *
     * @return integer Кол-во помеченных "к переносу" приемов
     * @throws \yii\db\Exception
     * @throws InvalidConfigException
     */
    protected function internalSave()

    {
      

        /*
         * Выбираем визиты, которые попадают в указанный диапазон
         * и если они не попадают в новое расписание - помечаем их "К переносу"
         */
        $this->loadVisitsInRange();

        if (!empty($this->_formatted_visits_in_date_range)) {
            $visits_to_transfer = [];
            foreach ($this->_formatted_visits_in_date_range as $visit) {
                if (!$this->isVisitIsOnTheNewTimesheet($visit)) {
                    $visits_to_transfer[] = $visit['visit_id'];
                }
            }
            // $count_visits_to_transfer = $this->markTransferVisits($visits_to_transfer);
            $count_visits_to_transfer = count($visits_to_transfer);

        } else {
            $count_visits_to_transfer = 0;
        }

        /*
         * У нас есть еще визиты в ОБЩУЮ живую очередь
         * Они не привязаны к специалисту, потому в общем порядке их не обработать
         * Обрабатываем отдельно
         */
        // $count_visits_to_transfer += (int)$this->markTransferCommonLiveQueueVisits();

        /* */

//        \Yii::debug($this->formatted_timesheets, 'timesheet_formatted');
//        \Yii::debug(VisitStatus::reverseAvailableSwitch(VisitStatus::TRANSFER), 'timesheet_visits_statuses_for_transfer');
//        \Yii::debug($this->map_patent_temp_uid_to_db_id, 'timesheet_formatted_map_uid_to_id_db');
//        \Yii::debug($this->map_child_to_parent, 'timesheet_formatted_map_child_to_parent');
//        \Yii::debug($visits_to_transfer, 'timesheet_visits_to_transfer');

        return $count_visits_to_transfer;
    }

    /**
     * Пометить визиты к переносу
     *
     * @param $visits_list
     *
     * @return int
     */
  

    /**
     * Проверяет - попадает ли указанный визит в новое расписание специалиста
     *
     * @param array $formatted_visit
     *
     * @return bool
     * @throws InvalidConfigException
     */
    protected function isVisitIsOnTheNewTimesheet($formatted_visit)
    {
        // Для данного спеца нет нового расписания
        $id_specialist = $formatted_visit['id_specialist'];

        if (!array_key_exists($id_specialist, $this->formatted_timesheets)) {
            return false;
        }

        $all_specialist_timesheet = array_merge(
            (array)$this->formatted_timesheets[$id_specialist]['childs'],
            (array)$this->formatted_timesheets[$id_specialist]['parents']
        );

        /*
         * Для
         * - НВП и живой очереди - только проверяем что в этот день есть соответсвующий тип
         * - для визитов с выездом на дом надо проверять
         *      а) есть пересечение с основным типом (например mos.ru или по телефону)
         *      б) есть пересечение с выездом на дом
         * - для остальных, что есть пересечение со сменой нужного типа
         */
        $result = $this->_isVisitOnNewTimesheet(
            $formatted_visit,
            $all_specialist_timesheet
        );

        if ($formatted_visit['type'] == Visits::TYPE_AT_HOME) {
            $result_at_home = $this->_isVisitOnNewAtHomeTimesheet(
                $formatted_visit,
                $all_specialist_timesheet
            );

            return ($result && $result_at_home);
        }

        return $result;

    }

    /**
     * Проверяет есть ли вхождение визита в один из таймшитов
     *
     * @param $formatted_visit
     * @param $specialist_all_formatted_timesheets
     *
     * @return bool
     * @throws InvalidConfigException
     */
    protected function _isVisitOnNewTimesheet($formatted_visit, $specialist_all_formatted_timesheets)
    {
        foreach ($specialist_all_formatted_timesheets as $timesheet) {
            $is_on = false;

            // Не тот тип
            if ($formatted_visit['shift_const_type'] !== $timesheet['shift_const_type']) {
                continue;
            }

            switch ($formatted_visit['shift_const_type']) {
                // Для этих - должно вмещаться во временные рамки таймшита
                case ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY:
                case ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT:
                case ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT:
                case ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME:
                    $is_on = (
                        $formatted_visit['date_from'] >= $timesheet['date_from']
                        &&
                        $formatted_visit['date_to'] <= $timesheet['date_to']
                    );
                    break;

                // Для этих - лишь бы в этот день было расписание соответсвующего типа
                case ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE:
                case ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE:
                    $is_on = (
                        $formatted_visit['date_from']->format('Y-m-d')
                        ==
                        $timesheet['date_from']->format('Y-m-d')
                    );
                    break;
                default:
                    $error = 'При обработке переноса приемов, встретился прием с неизвестным типом';
                    $this->addError('range', $error);
                    throw new InvalidConfigException($error);
            }

            if ($is_on) {
                return true; // нет смысла далее проверять
            }
        }
        return false;
    }

    /**
     * Проверяет, есть ли вхождение визита в таймшиты "Вызов на дом"
     *
     * @param $formatted_visit
     * @param $specialist_all_formatted_timesheets
     *
     * @return bool
     */
    protected function _isVisitOnNewAtHomeTimesheet($formatted_visit, $specialist_all_formatted_timesheets)
    {
        $is_on = false;
        foreach ($specialist_all_formatted_timesheets as $timesheet) {
            if ($timesheet['shift_const_type'] !== ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME) {
                continue;
            }

            $is_on = (
                $formatted_visit['date_from'] >= $timesheet['date_from']
                &&
                $formatted_visit['date_to'] <= $timesheet['date_to']
            );

            if ($is_on) {
                return true; // нет смысла далее проверять
            }
        }

        return false;
    }



    /**
     * Провалидируем оставшиеся, попутно перефоматировав timesheetы
     * под удобный для работы формат
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateAndFormatTimesheets()
    {
        /*
         * Проверяем формат и некоторые параметры, что бы далее спокойно работать с массивом
         * Если что не так - прерываем и возвращаем false
         */
        foreach ($this->timesheet as $timesheet) {
            if ($this->preValidateTimesheet($timesheet) !== true) {
                return false;
            }
        }

        $timesheets_by_specialist = ArrayHelper::index($this->timesheet, null, 'id_specialist');
        foreach ($timesheets_by_specialist as $id_specialist => &$timesheets) {

            $root_level = [];
            $can_be_child = [];
            $can_have_childs = [];

            $this->formatted_timesheets[$id_specialist]['parents'] = [];
            $this->formatted_timesheets[$id_specialist]['childs'] = [];

            foreach ($timesheets as $timesheet_key => &$timesheet) {
                /*
                 * Для дальнейшей работы добавляем доп. поля
                 */
                $timesheet['date_from'] = DateTime::createFromFormat(
                    self::TIMESHEET_DATE_FORMAT, $timesheet['from']
                );

                $timesheet['date_to'] = DateTime::createFromFormat(
                    self::TIMESHEET_DATE_FORMAT, $timesheet['to']
                );

                $timesheet['shift_const_type'] = $this->_organization_shifts[$timesheet['id_shift']]->type->type;
                $timesheet['shift_type_desc'] = $this->_organization_shifts[$timesheet['id_shift']]->type->description;


                // uid
                $timesheet['temp_uid'] = $id_specialist . '_' . $timesheet_key;

                /*
                 * Уровни и вложенность
                 */
                $timesheet['root_level_shift'] = $this->_organization_shifts[$timesheet['id_shift']]->type->canBeParent();
                $timesheet['can_be_child'] = $this->_organization_shifts[$timesheet['id_shift']]->type->canBeChild();
                $timesheet['overlap_category'] = $this->_organization_shifts[$timesheet['id_shift']]->type->overlap_category;

                // workday
                $timesheet['can_have_childs'] = $this->_organization_shifts[$timesheet['id_shift']]->type->canHaveChilds();

                if ($timesheet['root_level_shift']) {
                    $root_level[] = $timesheet;
                }

                if ($timesheet['can_be_child']) {
                    $can_be_child[] = $timesheet;
                }

                if ($timesheet['can_have_childs']) {
                    $can_have_childs[] = $timesheet;
                }

                /*
                 * Для привиочных пунктов надо еще проверить
                 * что ПП соответвует дате
                 */
                if (!$this->validateVaccinationStationTimesheet($timesheet)) {
                    return false;
                }
                /*
                 * Проверяем что все таймшиты не выпадают за пределы range
                 * (и что они смогли скастовать объект времени)
                 */
                if (!$this->isTimesheetHasCorrectRange($timesheet)) {
                    return false;
                }

                if (!$this->validateTimesheetDuration($timesheet)) {
                    return false;
                }

                /*
                 * Раскладываем дочерние/родительские по разным пачкам
                 * (родительские надо сохранять отдельно в первую очередь, чтобы получить их id)
                 * Работает для вложенности в 1
                 */
                if ($timesheet['root_level_shift'] || $timesheet['can_have_childs']) {
                    $this->formatted_timesheets[$id_specialist]['parents'][] = $timesheet;
                } else {
                    $this->formatted_timesheets[$id_specialist]['childs'][] = $timesheet;
                }
            } // --- END FOREACH SPEC TIMESHEET ---

            unset($timesheet);

            /*
             * Верхоуровневые не должны пересекаться
             */
            if (!$this->validateRootLevelShiftsIntersection($root_level)) {
                return false;
            }

            /*
             * Пересечения дочерних
             */
            if (!$this->validateOverlapChilds($this->formatted_timesheets[$id_specialist]['childs'])){
                return false;
            }

            /*
             * Дочерние должны входить в родительские + мапинг
             */
            if (!$this->validateAndMapParentChild(
                $can_have_childs, $can_be_child)
            ) {
                return false;
            }

        } // -- END FOREACH BY SPECIALIST ---

        unset($timesheets, $root_level, $can_be_child, $can_have_childs, $timesheets_by_specialist);
    }

    /**
     * Дополнительно проверяем смены с привиочными пунктами
     * @param $formatted_timesheet
     * @return bool
     */
    protected function validateVaccinationStationTimesheet($formatted_timesheet)
    {
        if ($formatted_timesheet['shift_const_type'] != ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION) {
            return true;
        }

        if (empty($this->_organization_shifts[$formatted_timesheet['id_shift']]->vaccinationStation)) {
            $this->addError(
                'timesheet',
                'Для смены  ' . $formatted_timesheet['shift_type_desc'] . ' не удалоь найти запись о привиочном пункте'
            );
            return false;
        }
        $vc_date = $this->_organization_shifts[$formatted_timesheet['id_shift']]->vaccinationStation->date;
        $ts_date = $formatted_timesheet['date_from']->format('Y-m-d');

        if ($vc_date != $ts_date) {

            $date = date_create_from_format(self::VACCINATION_STATION_DATE_FORMAT, $vc_date);
            $human_date_format = ($date != false) ? $date->format('d.m.Y') : $vc_date;

            $this->addError(
                'timesheet',
                'Смена "'
                . $this->_organization_shifts[$formatted_timesheet['id_shift']]->name
                . ' ['
                . $formatted_timesheet['shift_type_desc']
                . ']" может использоваться только '
                . $human_date_format
            );
            return false;
        }
        return true;
    }

    /**
     * Некоторые дочериние типы смен не могут персекаться. Проверяем
     *
     * @param $child_formatted_timesheets
     *
     * @return bool
     */
    protected function validateOverlapChilds($child_formatted_timesheets)
    {
        foreach ($child_formatted_timesheets as $key => $timesheet) {
            foreach ($child_formatted_timesheets as $check) {
                // С обедом и перерывом пересечения разрешены
                if ($check['overlap_category'] == ShiftType::OVERLAP_CATEGORY_IDLE ||
                    $timesheet['overlap_category'] == ShiftType::OVERLAP_CATEGORY_IDLE) {
                    continue;
                }

                // Между собой - тоже можно
                if ($check['overlap_category'] == $timesheet['overlap_category']) {
                    continue;
                }

                /*
                 * Памятка - нижнаяя граница timesheets включается в диапазон
                 * Верхняя - НЕТ
                 */
                if ($check['date_from'] >= $timesheet['date_from'] && $check['date_from'] < $timesheet['date_to']) {
                    $this->addError(
                        'timesheet',
                        'Смена ' . $check['shift_type_desc'] . ' не должна пересекаться со сменой ' . $timesheet['shift_type_desc']
                    );
                    return false;
                }
                if ($check['date_to'] >= $timesheet['date_from'] && $check['date_to'] <= $timesheet['date_to']) {
                    $this->addError(
                        'timesheet',
                        'Смена ' . $check['shift_type_desc'] . ' не должна пересекаться со сменой ' . $timesheet['shift_type_desc']
                    );
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Проверяем на пересечение верхоуровневые типы смен
     * (в сохраняеммом массиве таймшитов)
     *
     * @param $root_level_formatted_timesheets
     *
     * @return bool
     */
    protected function validateRootLevelShiftsIntersection($root_level_formatted_timesheets)
    {
        if (!is_array($root_level_formatted_timesheets) || count($root_level_formatted_timesheets) < 2) {
            return true;
        }

        /*
         * Отсортируем по дате начала,
         * тогда дата начала следующего диапазона должна быть больше или равна концу предыдущего
         *
         * Памятка - нижнаяя граница timesheets включается в диапазон
         * Верхняя - НЕТ
         */
        ArrayHelper::multisort($root_level_formatted_timesheets, ['date_from'], SORT_ASC);

        $prev = array_shift($root_level_formatted_timesheets);
        foreach ($root_level_formatted_timesheets as $next) {
            if ($next['date_from'] < $prev['date_to']) {
                $this->addError(
                    'timesheet',
                    'Один из дней содержит пересекающиеся смены (пересечение между рабочими и/или выходными и/или отпускными и/или больничными'
                );
                return false;
            }
            $prev = $next;
        }

        return true;
    }

    /**
     * Проверяем, что все дочерние входят в родительские
     *
     * @param $parent_formatted_timesheets
     * @param $child_formatted_timesheets
     *
     * @return bool
     */
    protected function validateAndMapParentChild($parent_formatted_timesheets, $child_formatted_timesheets)
    {
        if (empty($parent_formatted_timesheets) && !empty($child_formatted_timesheets)) {
            $this->addError(
                'timesheet',
                'Нельзя установить дочерние смены не указав родительскую'
            );
            return false;
        }
        /*
         * Перебираем все дочерние и ищем для них родительские
         * (нам достаточно, что бы дочерняя попала в любую родительскую целиком)
         */
        foreach ($child_formatted_timesheets as $child) {
            foreach ($parent_formatted_timesheets as $parent) {
                if ($child['date_from'] >= $parent['date_from'] && $child['date_to'] <= $parent['date_to']) {
                    // Маппинг
                    $this->map_child_to_parent[$child['temp_uid']] = $parent['temp_uid'];
                    continue 2; // нашли - давай следующий дочерний
                }
            }
            $this->addError('timesheet',
                'В одном из отредактированных дней есть дочерние смены по времени не входящие в родительские');
        }

        return true;
    }

    /**
     * Проверяем что длительность диапазона нового таймшита совпадает
     * с длительностью shift(смены), производным от которого является таймшит
     *
     * ПРИМЕЧАНИЕ: у нас есть еще заложенный заранее, но пока не обговоренный
     * флаг manual_shift = true|false который предполагалось использовать
     * для управления этой валидацией.
     * ПОКА ОН ИГНОРИРУЕТСЯ
     *
     * @param array $formatted_timesheet
     *
     * @return bool
     */
    protected function validateTimesheetDuration($formatted_timesheet)
    {
        $shift = $this->_organization_shifts[$formatted_timesheet['id_shift']];
        $timesheet_interval = $this->recalculateIntervalToMinutes(
            $formatted_timesheet['date_to']->diff($formatted_timesheet['date_from'])
        );

        if ($timesheet_interval !== $shift->duration) {
            $this->addError('timesheet', 'Переданый интервал таймшита не совпадает со временем расписания');
            return false;
        }
        return true;
    }

    /**
     * Предварительная валидация таймшита
     * - на формат
     * - существование смен
     * - существование спецов
     *
     * @param $timesheet
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function preValidateTimesheet($timesheet)
    {
        $organization_shifts = $this->_organization_shifts;
        $specialists = $this->specialists;

        $empty_timesheet = [
            'id_specialist',
            'id_shift',
            'from',
            'to',
            'manual_shift',
            'id_shift_type'
        ];

        $model = new DynamicModel(array_merge($empty_timesheet, $timesheet));

        $rules = [
            [['id_specialist', 'id_shift', 'from', 'to', 'manual_shift', 'id_shift_type'], 'required'],
            [['id_specialist', 'id_shift', 'id_shift_type'], 'integer'],
            [['from', 'to'], 'string'],
            ['manual_shift', 'boolean'],
            [
                'id_shift_type',
                function ($attribute, $params, $validator) use ($model, $organization_shifts) {
                    /*
                     * Смена есть
                     */
                    if (!array_key_exists($model['id_shift'], $organization_shifts)) {
                        return $model->addError($attribute, 'Передан некорректный id_shift');
                    }

                    /*
                     * Смена с правильным типом
                     */
                    if (empty($organization_shifts[$model['id_shift']]->id_type) ||
                        $model['id_shift_type'] != $organization_shifts[$model['id_shift']]->id_type) {
                        return $model->addError(
                            'id_shift_type',
                            'Передан id_shift_type не соответсвует указанной смене ' . $model['id_shift']
                        );
                    }
                }
            ],
            [
                'id_specialist',
                function ($attribute, $params, $validator) use ($model, $specialists) {
                    if (!in_array($model['id_specialist'], $specialists)) {
                        return $model->addError(
                            'id_specialist',
                            'Массив timesheet содержит запись к специалисту, который отсутствует в specialists'
                        );
                    }
                }
            ]
        ];

        foreach ($rules as $rule) {
            $model->addRule($rule[0], $rule[1]);
        }

        /*
         * Валидируем, и если есть ошибки добавляем их в основную модель
         */
        $model->validate();

        if ($model->hasErrors()) {
            $this->addErrors($model->getErrorSummary(true));
        }

        return !$model->hasErrors();
    }

    /**
     * Проверяет корректность дат в отдельном таймшите
     * - дата сконвертировалась
     * - границы верные
     * - не выходит за пределы range
     * - находиться в будущем
     *
     * @param $formatted_timesheet
     *
     * @return bool
     */
    protected function isTimesheetHasCorrectRange($formatted_timesheet)
    {
        if (empty($formatted_timesheet['date_from']) || empty($formatted_timesheet['date_to'])) {
            $this->addError('timesheet', 'Массив timesheet содержит запись с невалидной датой');
            return false;
        }

        if ($this->_date_today > $formatted_timesheet['date_from']) {
            $this->addError(
                'timesheet', 'Нельзя редактировать график в прошлом'
            );
            return false;
        }

        if ($formatted_timesheet['date_from'] > $formatted_timesheet['date_to']) {
            $this->addError(
                'timesheet', 'Один из дней содержит смену у которой время начала больше времени окончания'
            );
            return false;
        }

        if ($formatted_timesheet['date_from'] < $this->_date_from || $formatted_timesheet['date_to'] > $this->_date_to) {
            $this->addError(
                'timesheet',
                'Один из дней содержит запись у которой временной промежуток выходит за пределы указанные в поле range'
            );
            return false;
        }

        return true;
    }

  

    /**
     * извлекаем из временного интервала минуты, секунды ОКРУГЛЯЕМ ВВЕРХ,
     * то есть если есть хоть 1 секунда- то она уже считается за минуту
     *
     * @param \DateInterval $interval
     *
     * @return float|int
     */
    private function recalculateIntervalToMinutes(\DateInterval $interval)
    {
        return (int)(($interval->y * 365 * 24 * 60 * 60) +
            ($interval->m * 30 * 24 * 60) +
            ($interval->d * 24 * 60) +
            ($interval->h * 60) +
            $interval->i +
            ceil($interval->s / 60));
    }

    /**
     * Возвращает id родительского таймшита в бд по temp_uid дочернего
     * (необходимо что бы верно проставить связи parent_id в timesheets)
     *
     * ВЕРНЕТ FALSE если не удалось найти сопоставление
     *
     * @param $child_timesheet_temp_uid
     *
     * @return bool|mixed
     */
   

    /**
     * Выбираем визиты которые попадают в редатируемое пространство время-специалисты
     */
    protected function loadVisitsInRange()
    {
        $visits = Visits::find()
            ->select([
                'st.type AS shift_const_type',
                'visits.id AS visit_id',
                'visits.type',
                'vs.id_specialist',
                new Expression('lower(visits.time_range) AS time_lower'),
                new Expression('upper(visits.time_range) AS time_upper'),
            ])
            ->where([
                'AND',
                ['IN', 'vs.id_specialist', $this->specialists],
                ['IN', 'status', VisitStatus::reverseAvailableSwitch(VisitStatus::TRANSFER)],
                [
                    'OR',
                    [ // Обычные визиты
                        'AND',
                        ['NOT', ['st.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE]],
                        new Expression("time_range && tsrange(:date_from, :date_to,'()')", [
                            ':date_from' => $this->_date_from->format(self::TIMESHEET_DATE_FORMAT),
                            ':date_to' => $this->_date_to->format(self::TIMESHEET_DATE_FORMAT),
                        ]),
                    ],
                    [ // Живая очередь по спецу
                        'AND',
                        ['st.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE],
                        new Expression("visits.created_at <@ tsrange(:date_from, :date_to,'()')", [
                            ':date_from' => $this->_date_from->format(self::TIMESHEET_DATE_FORMAT),
                            ':date_to' => $this->_date_to->format(self::TIMESHEET_DATE_FORMAT),
                        ])
                    ]
                ],
            ])
            ->leftJoin('visits_specialists vs', 'vs.id_visit = visits.id')
            ->leftJoin('shift_type st', 'st.id = visits.channel')
            ->orderBy('id_specialist ASC, time_range ASC')
            ->asArray()
            ->all();

        // Кастуем время для сравнения
        foreach ($visits as &$visit) {
            $visit['date_from'] = date_create_from_format(self::TIMESHEET_DATE_FORMAT, $visit['time_lower']);
            $visit['date_to'] = date_create_from_format(self::TIMESHEET_DATE_FORMAT, $visit['time_upper']);
            $this->_formatted_visits_in_date_range[] = $visit;
        }
        unset($visit);
    }

    /**
     * "К переносу" оствшиеся сиротами записи в ОБЩУЮ ЖИВУЮ ОЧЕРЕДЬ
     *
     * @return int
     */
    protected function markTransferCommonLiveQueueVisits()
    {
        /*
         *  Запись в ОБЩУЮ живую очередь возможна,
         *  только если в этот день есть хотя бы у одного спеца живая очередь
         *
         *  В нашем случае, могут прислать массив новых расписаний так, что это правило не будет выполняться
         *  (если, например, очистить у спецов все дни)
         *
         *  Значит, сначала генерируем все сочетания "день" X "организация"
         *
         *         %_org_ids% , [%date_from% 00:00:00; %date_from% 23:59:59.999999]
         *         ... до ...
         *         %_org_ids% , [%date_to% 00:00:00; %date_to% 23:59:59.999999]
         *
         *
         *  И потом проверяем, что в данной организации, в данное время
         *  есть хотя бы один таймшит спеца с записью в живую очередь
         *  Если такого нет - выбираем все визиты которые:
         *      - за этот день
         *      - в этой организации
         *      - в живую очередь
         *      - не назначены специалисту (такие выбираются другим кейсом)
         *      - в нужном статусе
         *
         */
        $visits_query = (new Query())
            ->select('visits.id')
            // В этом диапазоне ...
            ->from([
                'range_query' => $this->buildSubQueryCommonLiveQueue_SpecialistToTimeRange()
            ])
            // ... нужны визиты, стыкующиеся по id_org + попадание created_at в сгенерированный диапазон, ...
            ->leftJoin(
                'visits',
                'range_query.id_organization = visits.id_organization AND visits.created_at <@ range_query.day_range'
            )
            ->leftJoin(
                'shift_type AS visit_shift_type',
                'visits.channel = visit_shift_type.id'
            )
            ->leftJoin(
                'visits_specialists',
                'visits.id = visits_specialists.id_visit'
            )
            // ... которые
            ->where([
                'AND',
                // только из общей живой очереди
                ['visit_shift_type.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE],
                ['IS', 'visits_specialists.id_specialist', null],
                // и нет в день приема ни у кого живой очереди
                ['not exists', $this->buildSubQueryCommonLiveQueue_ConditionTimesheetLiveQueueExistAtDay()],
                ['IN', 'status', VisitStatus::reverseAvailableSwitch(VisitStatus::TRANSFER)],
            ]);

        // Проставляем статус "к переносу"
        return Visits::updateAll([
            'status' => VisitStatus::TRANSFER
        ], [
            'IN',
            'id',
            $visits_query
        ]);
    }

    /**
     * Возвращает подзапрос с генерацией пространства
     *      "организации указанных специалистов" x "дни указанные в range"
     *
     * для большого запроса на выборку приемов в общую живую очередь,
     * которые остались без таймшитов
     *
     * @return \yii\db\ActiveQuery
     */
    protected function buildSubQueryCommonLiveQueue_SpecialistToTimeRange()
    {
        $exp = new Expression(
            "generate_series(
              date_trunc('day', (:date_from)::timestamp),
              date_trunc('day', (:date_to)::timestamp),
              '1 day'::interval
          ) "
        );

        return Specialists::find()
            ->select([
                'specialists.id_organization',
                new Expression("tsrange(days_series, days_series + interval '23:59:59.999999', '[]') AS day_range")
            ])
            ->distinct(true)
            ->leftJoin(
                ['days_series' => $exp],
                '1=1'
            )
            ->where([
                'IN',
                'specialists.id',
                $this->specialists
            ])
            // Параметры от Exp
            ->addParams([
                'date_from' => $this->_date_from->format(self::TIMESHEET_DATE_FORMAT),
                'date_to' => $this->_date_to_for_clear_old->format(self::TIMESHEET_DATE_FORMAT),
            ]);
    }

    /**
     * Возвращает подзапрос-условие, которое будет отсеивать дни,
     * в которые есть хотя бы одна запись в живую очередь на организацию
     *
     * (для большого запроса на выборку приемов в общую живую очередь,
     * которые остались без таймшитов)
     *
     * @return \yii\db\ActiveQuery
     */
    protected function buildSubQueryCommonLiveQueue_ConditionTimesheetLiveQueueExistAtDay()
    {
        return Timesheets::find()
            ->select(
                new Expression(1)
            )
            ->leftJoin('specialists', 'timesheets.id_specialist = specialists.id')
            ->leftJoin('shifts', 'timesheets.id_shift = shifts.id')
            ->leftJoin('shift_type', 'shifts.id_type = shift_type.id')
            ->where([
                'AND',
                ['shift_type.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE],
                // стыковка с основным запросом
                new Expression('timesheets.date && range_query.day_range'),
                new Expression('specialists.id_organization = range_query.id_organization')
            ])
            ->limit(1);
    }

    /**
     * @inheritDoc
     */
    public function getErrorSummary($showAllErrors)
    {
        $lines = parent::getErrorSummary($showAllErrors);
        if (!empty($lines)) {
            $lines = array_unique($lines);
        }

        return $lines;
    }

    /**
     * Так как используются insert/batchInsert, behaviors не работают
     *
     * @return int|null
     */
    private function blameableId()
    {
        if (\Yii::$app->has('user')) {
            return \Yii::$app->get('user')->id;
        }

        return null;
    }
}
