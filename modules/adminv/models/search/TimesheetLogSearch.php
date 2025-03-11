<?php

namespace app\modules\adminv\models\search;

use app\models\db\audit\TimesheetLog;
use app\modules\admin\data\AdminDataProvider;
use yii\base\InvalidConfigException;
use yii\db\Expression;

class TimesheetLogSearch extends TimesheetLog
{

    /** @var int */
    public $id_timesheet;

    /** @var string */
    public $fio_initiator;

    /** @var int */
    public $id_specialist;

    /** @var string */
    public $date;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['id_timesheet'], 'trim'],
            [
                ['fio_initiator', 'id_specialist', 'id_timesheet', 'date'],
                'safe'
            ],
            [['id_specialist', 'id_timesheet'], 'integer'],
            ['fio_initiator', 'string'],
            [['date'], 'date'],
        ];
    }

    /**
     * @param array $params
     *
     * @return AdminDataProvider
     * @throws InvalidConfigException
     */
    public function search(array $params): AdminDataProvider
    {
        $this->load($params);
        $query = TimesheetLog::find();

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $dataProvider->setSort([
            'attributes' => [
                'id_timesheet',
                'id_specialist',
                'date',
            ],
            'defaultOrder' => [
                'date' => SORT_DESC,
                'id_timesheet' => SORT_DESC,
            ],
        ]);

        $query->andFilterWhere(
            ['id_timesheet' => $this->id_timesheet]
        );

        $query->andFilterWhere(
            ['ilike', 'fio_initiator', $this->fio_initiator]
        );

        $query->andFilterWhere(
            ['id_specialist' => $this->id_specialist]
        );

        if (!empty($this->date)) {
            $query->andWhere(new Expression("audit.timesheets_logs.date::date = :date", [
                'date' => $this->date
            ]));
        }

        return $dataProvider;
    }
}