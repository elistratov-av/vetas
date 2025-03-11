<?php

namespace app\modules\adminfstek\models\search;

use app\models\db\elk\ElkLog;
use app\models\db\etp\ETPMessage;
use app\models\db\mdm\MdmLog;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class LogExternalDataSearch
 * @package app\modules\adminfstek\models\search
 */
class LogExternalDataSearch extends BaseSearchModel
{
    /**
     * @var string
     */
    public $service_name;
    /**
     * @var string
     */
    public $date_time;
    /**
     * @var int
     */
    public $type;

    /**
     * @var array
     */
    protected $sortAttributes = ['service_name', 'date_time', 'type'];
    /**
     * @var array
     */
    protected $defaultOrder = ['date_time' => SORT_DESC];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['service_name'], 'filter', 'filter' => 'trim'],
            [['service_name'], 'filter', 'filter' => 'strip_tags'],
            [['service_name'], 'string', 'min' => 3, 'max' => 255],
            [['date_time'], 'date', 'format' => 'php:Y-m-d'],
            ['type', 'integer']
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        $elkQuery = 'select \'elk\' as service_name, created_at as date_time, 1 as type, "xml" as payload from ' . ElkLog::tableName();
        $mdmQuery = 'select \'mdm\' as service_name, created_at as date_time, 1 as type, to_json("data")::text as payload from ' . MdmLog::tableName();
        $mosruQuery = 'select \'mosru\' as service_name, created_at as date_time, 1 as type, to_json("message")::text as payload from ' . ETPMessage::tableName();

        $expr = '(' . $elkQuery . ' union all ' . $mdmQuery . ' union all ' . $mosruQuery . ') l';

        $query = (new Query())
            ->select('l.*')
            ->from(new Expression($expr));

        return $query;
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere(['ilike', 'service_name', $this->service_name]);

        if (!empty($this->date_time)) {
            $query->andWhere(new Expression('date_time::date = :date_time', [
                'date_time' => $this->date_time,
            ]));
        }

        $query->andFilterWhere(['type' => $this->type]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'service_name' => 'Внешняя система',
            'date_time' => 'Дата и время',
            'type' => 'Тип события',
        ];
    }

    /**
     * @return array
     */
    public static function typeOptions()
    {
        return [
            1 => 'загрузка',
            2 => 'выгрузка',
        ];
    }

    /**
     * @return array
     */
    public static function serviceOptions()
    {
        return [
            'elk' => 'elk',
            'mdm' => 'mdm',
            'mosru' => 'mosru',
        ];
    }
}
