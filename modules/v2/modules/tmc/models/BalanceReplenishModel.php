<?php


namespace app\modules\v2\modules\tmc\models;

use app\common\validators\FullTrimValidator;
use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceFlow;
use app\models\db\tmc\ProductionForm;
use app\models\db\tmc\TmcBase;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Пополнение баланса
 * Доступно только для организаций
 * Пополнение балансов специалистов происходит путем передачи с баланса организации
 * и требует выдачи документов
 *
 * @package app\modules\v2\modules\tmc\models
 */
class BalanceReplenishModel
{
    /**
     * @var integer
     */
    protected $id_organization;

    public function __construct()
    {
        $user = \Yii::$app->user->getIdentity();
        $this->id_organization = $user->specialist->id_organization;
        if ($this->id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }
    }

    /**
     * Обработка массива данных о ТМЦ для постановки на баланс
     *
     * @param $type_tmc
     * @param $items
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function append($type_tmc, $items)
    {
        $items = $this->checkMainArray($items);

        // Добавляем по одному (или просто пополняем если есть такие на балансе)
        $transaction = Balance::getDb()->beginTransaction();

        foreach ($items as $key => $row){
            try {
                $this->_appendByOne($type_tmc, $row);
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw new BadRequestHttpException('ID - ' . $key . '. ' . $e->getMessage());
            }
        }
        $transaction->commit();
    }

    /**
     * Обработка массива данных об оборудовании для постановки на баланс
     *
     * @param $items
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function appendEquipment($items)
    {
        $items = $this->checkMainArray($items);

        // Добавляем по одному
        $transaction = Balance::getDb()->beginTransaction();

        foreach ($items as $key => $row){
            try {
                $this->_appendByOneEquipment($row);
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw new BadRequestHttpException('ID - ' . $key . '. ' . $e->getMessage());
            }
        }
        $transaction->commit();
    }

    /**
     * Сохранение оборудования
     *
     * @param $row
     * @throws BadRequestHttpException
     */
    protected function _appendByOneEquipment($row)
    {
        // Поля после валидации
        $row = $this->validateEquipment( $row);
        $balance_item = (new Balance($row));

        // Такое уже есть?
        $this->isEquipmentExist($balance_item);

        if (!$balance_item->save()) {
            $errors = $balance_item->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении оборудования' : implode(";", array_values($errors)));
        }
    }

    /**
     * @param $row
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    protected function validateEquipment($row)
    {
        // Те поля, которые готовы получить из запроса
        $empty_fields = [
            'id_tmc' => null,
            'inventory_number' => null,
            'manufactured_number' => null,
            'equipment_condition' => null,
            'registration_date' => null,
            'production_date' => null,
        ];

        // Исключаем то чего нет в списке, недостающие добавляем
        $row = array_intersect_key($row, $empty_fields);
        $row = array_merge($empty_fields, $row);

        // + текущая организация пользователя, тип ТМЦ
        $row = array_merge($row, [
            'id_organization' => $this->id_organization,
            'type_tmc' => TmcBase::TYPE_EQUIPMENT,
        ]);

        $rules = [
            [['id_tmc'], 'integer'],
            [
                ['equipment_condition'], 'in',
                'range' => [
                    Balance::EQUIPMENT_CONDITION_WORK,
                    Balance::EQUIPMENT_CONDITION_NOT_WORK,
                ],
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['inventory_number', 'manufactured_number'], 'string'],
            [['inventory_number', 'manufactured_number'], FullTrimValidator::class],
            [['registration_date', 'production_date'], 'date']
        ];

        $model = DynamicModel::validateData($row, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("; ", array_values($errors)));
        }

        return $model->attributes;
    }

    /**
     * Постановка на баланс организации если нет такого (см findBalanceId(...))
     * и пополнение текущего значения
     *
     * @param $type_tmc
     * @param $row
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    protected function _appendByOne($type_tmc, $row)
    {
        // Поля после валидации
        $row = $this->validateBalanceTmc($type_tmc, $row);

        $this->checkTmcByInventoryNumber($row);

        // Считаем кол-во в стандартных ед изм
        $count = $this->calcCount($row['id_production_form'], $row['count_in_production_form']);
        $balance = $this->findBalanceByAttributes($row);

        if (!$balance){ // новая запись на балансе
            $balance = new Balance($row);
            if (!$balance->save()) {
                $errors = $balance->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении баланса' : implode(";", array_values($errors)));
            }
        } else {
            $balance
                ->setAttribute('registration_date', $row['registration_date'])
                ->save();
        }

        // Пишем в таблицу - приход/расход
        // Триггер посчитает суммы сам
        $flow = new BalanceFlow([
            'id_tmc_balance' => $balance->id,
            'flow_type' => BalanceFlow::FLOW_TYPE_INCREASE,
            'flow_action' => BalanceFlow::FLOW_ACTION_INCOME_TO_ORG,
            'count' => $count,
        ]);

        if (!$flow->save()) {
            $errors = $flow->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении записи о приходе-расходе' : implode(";", array_values($errors)));
        }
    }

    /**
     * Валидируем поля ТМЦ (кроме оборудования)
     *
     * @param $type_tmc
     * @param $row
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateBalanceTmc($type_tmc, $row)
    {
        // Те поля, которые готовы получить из запроса
        $empty_fields = [
            'id_tmc' => null,
            'id_production_form' => null,
            'inventory_number' => null,
            'count_in_production_form' => null,
            'price' => null,
            'expiration_date' => null,
            'production_date' => null,
            'registration_date' => null,
        ];

        // Исключаем то чего нет в списке, недостающие добавляем
        $row = array_intersect_key($row, $empty_fields);
        $row = array_merge($empty_fields, $row);

        // + текущая организация пользователя, тип ТМЦ
        $row = array_merge($row, [
            'id_organization' => $this->id_organization,
            'type_tmc' => $type_tmc,
            //'registration_date' => $row['registration_date'] ? $row['registration_date'] : date('Y-m-d'),
        ]);

        $rules = [
            [['id_tmc', 'id_production_form'], 'integer'],
            [
                ['id_production_form'],
                'exist', 'skipOnError' => true, 'targetClass' => ProductionForm::class,
                'targetAttribute' => ['id_tmc' => 'id_tmc', 'type_tmc' => 'type_tmc', 'id_production_form' => 'id']
            ],
            [['price', 'count_in_production_form'], 'number'],
            [['price', 'count_in_production_form'], 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['inventory_number'], 'string'],
            [['inventory_number'], FullTrimValidator::class],
            [['expiration_date', 'production_date', 'registration_date'], 'date']
        ];

        $model = DynamicModel::validateData($row, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("; ", array_values($errors)));
        }

        return $model->attributes;
    }

    /**
     * Проверяем основной массив
     * и формируем массив ключ(id) => строка балансового ТМЦ
     *
     * @param $array
     * @return array
     * @throws BadRequestHttpException
     */
    protected function checkMainArray($array)
    {
        if (!is_array($array)){
            throw new BadRequestHttpException('Параметр items должен быть массивом');
        }

        $result = [];
        foreach ($array as $row){
            if (!is_array($row)){
                throw new BadRequestHttpException('Параметр items должен быть массивом массивов');
            }

            if (!array_key_exists('row_id', $row)){
                throw new BadRequestHttpException('Некоторые строки не содержат параметра row_id');
            }

            if (!is_numeric($row['row_id'])){
                throw new BadRequestHttpException('Параметр row_id должен быть числом');
            }

            if (array_key_exists($row['row_id'], $result)){
                throw new BadRequestHttpException('Массив items содержит элементы с повторяющимися row_id');
            }

            $key = $row['row_id'];
            unset($row['row_id']);
            $result[$key] = $row;
        }

        return $result;

    }

    /**
     * @param array $attributes
     * @return Balance|null
     */
    protected function findBalanceByAttributes(array $attributes)
    {
        return Balance::find()
            ->where([
            'AND',

            // этот ТМЦ
            ['id_tmc' => $attributes['id_tmc']],
            ['type_tmc' => $attributes['type_tmc']],

            // стоящий на балансе у этой организации (и не личном балансе врача)
            ['id_organization' => $attributes['id_organization']],
            ['IS', 'id_specialist', NULL],

            // C этим инвентарным номером
            ['inventory_number' => $attributes['inventory_number']],

            //договорились что на балансе может быть
            // тот же препарат но с разной ценой
            ['price' => $attributes['price']]
        ])->one();
    }

    /**
     * @param Balance $balance_item
     * @return false
     * @throws BadRequestHttpException
     */
    protected function isEquipmentExist($balance_item)
    {
        /** @var Balance $exist */
        $exist = Balance::find()
            ->where([
                'AND',

                // этот ТМЦ
                ['id_tmc' => $balance_item->id_tmc],
                ['type_tmc' => $balance_item->type_tmc],

                // стоящий на балансе у этой организации
                ['id_organization' => $balance_item->id_organization],

                // C этим инвентарным номером или заводским номером
                [
                    'OR',
                    ['inventory_number' => $balance_item->inventory_number],
                    ['manufactured_number' => $balance_item->manufactured_number],
               ]
            ])->one();

        if (empty($exist)){
            return false;
        }

        if ($balance_item->inventory_number == $exist->inventory_number
            && $balance_item->manufactured_number == $exist->manufactured_number){
            throw new BadRequestHttpException(
                'Оборудование с с/н ' . $exist->manufactured_number . ' и и/н '  . $exist->inventory_number . ' уже существует'
            );
        }

        if ($balance_item->inventory_number == $exist->inventory_number){
            throw new BadRequestHttpException(
                'Оборудование с и/н '  . $exist->inventory_number . ' уже существует'
            );
        }

        if ($balance_item->manufactured_number == $exist->manufactured_number){
            throw new BadRequestHttpException(
                'Оборудование с с/н ' . $exist->manufactured_number  . ' уже существует'
            );
        }
    }

    /**
     * В поле count модели balance у нас храниться кол-во с стандартных ед измерения (см id_measure)
     * Однако пользователь вносит кол-во в формах производства
     * Например 5 упаковок
     * Надо посмотреть сколько в одной упаковке и пересчитать кол-во
     *
     * @param $id_production_form
     * @param $count_in_production_form
     * @return float|int
     * @throws InvalidConfigException
     */
    protected function calcCount($id_production_form, $count_in_production_form)
    {
        $volume = ProductionForm::find()
            ->select('volume')
            ->where(['id' => $id_production_form])
            ->scalar();

        if (empty($volume)){
            throw new InvalidConfigException('Ошибка при пересчете в стандартные ед. измерения');
        }

        return $volume * $count_in_production_form;
    }

    protected function checkTmcByInventoryNumber(array $attributes)
    {
        $balances = Balance::find()
            ->select(['production_date', 'expiration_date'])
            ->where([
                'inventory_number' => $attributes['inventory_number'],
                'id_tmc'           => $attributes['id_tmc']
            ])
            ->distinct()
            ->asArray()
            ->all();

        $compared_attributes = [$attributes['production_date'], $attributes['expiration_date']];
        foreach ($balances as $balance){
            $diff = array_diff($balance, $compared_attributes);
            if (!empty($diff)){
                throw new BadRequestHttpException("На балансе есть ТМЦ с серийным номером {$attributes['inventory_number']}, "
                . "датой изготовления ${balance['production_date']} и датой истечения срока годности ${balance['expiration_date']}");
            }
        }

        return true;
    }

}
