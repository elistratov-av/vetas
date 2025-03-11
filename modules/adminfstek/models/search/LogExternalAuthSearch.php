<?php

namespace app\modules\adminfstek\models\search;

use app\models\db\audit\LogExternalAuth;
use yii\db\Expression;

/**
 * Class LogExternalAuthSearch
 * @package app\modules\adminfstek\models\search
 */
class LogExternalAuthSearch extends BaseSearchModel
{
    /**
     * @var string
     */
    public $service_name;
    /**
     * @var string
     */
    public $protocol;
    /**
     * @var string
     */
    public $interface;
    /**
     * @var int
     */
    public $is_success;
    /**
     * @var string
     */
    public $created_at;
    /**
     * @var string
     */
    public $ip;

    /**
     * @var array
     */
    protected $defaultOrder = ['created_at' => SORT_DESC];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['is_success', 'integer'],
            [['service_name', 'protocol', 'interface', 'ip'], 'filter', 'filter' => 'trim'],
            [['service_name', 'protocol', 'interface', 'ip'], 'filter', 'filter' => 'strip_tags'],
            [['service_name', 'protocol', 'interface', 'ip'], 'string', 'min' => 3, 'max' => 255],
            [['created_at'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        return LogExternalAuth::find();
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere(['ilike', 'service_name', $this->service_name])
            ->andFilterWhere(['ilike', 'protocol', $this->protocol])
            ->andFilterWhere(['ilike', 'interface', $this->interface])
            ->andFilterWhere(['ilike', 'ip', $this->ip]);

        if ($this->is_success === '1') {
            $query->andWhere(['is_success' => true]);
        } elseif ($this->is_success === '0') {
            $query->andWhere(['is_success' => false]);
        }

        if (!empty($this->created_at)) {
            $query->andWhere(new Expression('created_at::date = :created_at', [
                'created_at' => $this->created_at,
            ]));
        }
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
