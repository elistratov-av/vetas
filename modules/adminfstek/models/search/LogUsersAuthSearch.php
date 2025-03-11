<?php

namespace app\modules\adminfstek\models\search;

use app\common\validators\PGIdValidator;
use app\models\db\audit\LogUsersAuth;
use yii\db\Expression;

/**
 * Class LogUsersAuthSearch
 * @package app\modules\adminfstek\models\search
 */
class LogUsersAuthSearch extends BaseSearchModel
{
    /**
     * @var string
     */
    public $login;
    /**
     * @var int
     */
    public $id_user;
    /**
     * @var int
     */
    public $target;
    /**
     * @var int
     */
    public $type;
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
            [['id_user', 'target', 'type', 'is_success'], 'integer'],
            ['id_user', PGIdValidator::class],
            [['login', 'ip'], 'filter', 'filter' => 'trim'],
            [['login', 'ip'], 'filter', 'filter' => 'strip_tags'],
            [['login', 'ip'], 'string', 'min' => 3, 'max' => 255],
            [['created_at'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        return LogUsersAuth::find();
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere([
            'id_user' => $this->id_user,
            'target' => $this->target,
            'type' => $this->type,
        ]);

        $query->andFilterWhere(['ilike', 'login', $this->login])
            ->andFilterWhere(['ilike', 'ip', $this->ip]);

        if ($this->is_success === '1') {
            $query->andWhere(['is_success' => true]);
        } elseif ($this->is_success === '0') {
            $query->andWhere(['is_success' => false]);
        }

        if (!empty($this->created_at)) {
            $query->andWhere(new Expression("created_at::date = :created_at", [
                'created_at' => $this->created_at,
            ]));
        }
    }
}
