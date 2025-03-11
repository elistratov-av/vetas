<?php

namespace app\modules\adminfstek\models\search;

use app\common\validators\PGIdValidator;
use app\models\db\admin\AdminUser;
use app\models\db\audit\LogUsersAccessChange;
use yii\db\Expression;

/**
 * Class LogUsersAccessChangeSearch
 * @package app\modules\adminfstek\models\search
 */
class LogUsersAccessChangeSearch extends BaseSearchModel
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
     * @var int
     */
    public $created_by;
    /**
     * @var string
     */
    public $creator_login;

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
            [['target', 'type', 'is_success'], 'integer'],
            [['id_user', 'created_by'], PGIdValidator::class],
            [['login', 'creator_login'], 'filter', 'filter' => 'trim'],
            [['login', 'creator_login'], 'filter', 'filter' => 'strip_tags'],
            [['login', 'creator_login'], 'string', 'min' => 3, 'max' => 255],
            [['created_at'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        return LogUsersAccessChange::find()
            ->alias('log');
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere([
            'log.id_user' => $this->id_user,
            'log.target' => $this->target,
            'log.type' => $this->type,
            'log.created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['ilike', 'log.login', $this->login]);

        if ($this->is_success === '1') {
            $query->andWhere(['log.is_success' => true]);
        } elseif ($this->is_success === '0') {
            $query->andWhere(['log.is_success' => false]);
        }

        if (!empty($this->created_at)) {
            $query->andWhere(new Expression('log.created_at::date = :created_at', [
                'created_at' => $this->created_at,
            ]));
        }
        if (!empty($this->creator_login)) {
            $query->leftJoin(AdminUser::tableName() . ' adm', 'adm.id = log.created_by');
            $query->andWhere(['ilike', 'adm.login', $this->creator_login]);
        }
    }
}
