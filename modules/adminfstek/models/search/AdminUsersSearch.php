<?php

namespace app\modules\adminfstek\models\search;

use app\common\validators\PGIdValidator;
use app\models\db\admin\AdminUser;
use yii\db\Expression;

/**
 * Class AdminUsersSearch
 * @package app\modules\adminfstek\models\search
 */
class AdminUsersSearch extends BaseSearchModel
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $email;
    /**
     * @var int
     */
    public $role;
    /**
     * @var int
     */
    public $is_blocked;
    /**
     * @var int
     */
    public $temp_block;
    /**
     * @var string
     */
    public $last_login;
    /**
     * @var string
     */
    public $fullname;

    /**
     * @var array
     */
    protected $defaultOrder = ['login' => SORT_ASC];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['id', PGIdValidator::class],
            ['login', 'filter', 'filter' => 'trim'],
            ['login', 'filter', 'filter' => 'strip_tags'],
            ['login', 'string', 'min' => 3, 'max' => 255],
            ['fullname', 'filter', 'filter' => 'trim'],
            ['fullname', 'filter', 'filter' => 'strip_tags'],
            ['fullname', 'string', 'min' => 3, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'filter', 'filter' => 'strip_tags'],
            ['email', 'string', 'min' => 3, 'max' => 255],
            ['role', 'in', 'range' => AdminUser::roles()],
            [['is_blocked', 'temp_block'], 'boolean'],
            ['last_login', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        return AdminUser::find();
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query
            ->andFilterWhere(['ilike', 'login', $this->login])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['role' => $this->role]);

        if ($this->is_blocked !== null && $this->is_blocked !== '') {
            $query->andWhere(['is_blocked' => $this->is_blocked]);
        }
        if ($this->temp_block !== null && $this->temp_block !== '') {
            $now = (new \DateTime())->format('Y-m-d H:i:s');
            if ($this->temp_block == 1) {
                $query->andWhere(['not', ['block_until' => null]]);
                $query->andWhere(['>=', 'block_until', $now]);
            } elseif ($this->temp_block == 0) {
                $query->andWhere([
                    'or',
                    ['block_until' => null],
                    [
                        'and',
                        ['not', ['block_until' => null]],
                        ['<', 'block_until', $now],
                    ],
                ]);
            }
        }

        if (!empty($this->last_login)) {
            $query->andWhere(new Expression("last_login::date = :last_login", [
                'last_login' => $this->last_login,
            ]));
        }

        if (!empty($this->fullname)) {
            $query->andWhere([
                'or',
                ['ilike', 'f_fio', $this->fullname],
                ['ilike', 'i_fio', $this->fullname],
                ['ilike', 'o_fio', $this->fullname],
            ]);
        }
    }
}
