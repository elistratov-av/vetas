<?php

namespace app\modules\adminfstek\models\search;

use app\common\validators\PGIdValidator;
use app\models\db\audit\LogUsersBlockAuto;
use yii\db\Expression;

/**
 * Class LogUsersBlockAutoSearch
 * @package app\modules\adminfstek\models\search
 */
class LogUsersBlockAutoSearch extends BaseSearchModel
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
    public $block_until;

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
            [['login'], 'filter', 'filter' => 'trim'],
            [['login'], 'filter', 'filter' => 'strip_tags'],
            [['login'], 'string', 'min' => 3, 'max' => 255],
            [['created_at', 'block_until'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        return LogUsersBlockAuto::find();
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

        $query->andFilterWhere(['ilike', 'login', $this->login]);

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
        if (!empty($this->block_until)) {
            $query->andWhere(new Expression("block_until::date = :block_until", [
                'block_until' => $this->block_until,
            ]));
        }
    }
}
