<?php

namespace app\modules\adminfstek\models\search;

use app\common\models\UserModel;
use app\common\validators\PGIdValidator;
use yii\db\Expression;

/**
 * Class UsersSearch
 * @package app\modules\adminfstek\models\search
 */
class UsersSearch extends BaseSearchModel
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $fullname;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $short_name;
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $last_login;

    /**
     * @var array
     */
    protected $defaultOrder = ['login' => SORT_ASC];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', PGIdValidator::class],
            [['fullname', 'short_name', 'login', 'email'], 'filter', 'filter' => 'trim'],
            [['fullname', 'short_name', 'login', 'email'], 'filter', 'filter' => 'strip_tags'],
            [['fullname', 'short_name', 'login', 'email'], 'string', 'min' => 3, 'max' => 255],
            [['last_login'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        return UserModel::find()
            ->joinWith(['specialists specialists', 'specialists.organization'], true);
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere([
            UserModel::tableName() . '.id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'fullname', $this->fullname])
            ->andFilterWhere(['ilike', 'login', $this->login])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['ilike', 'short_name', $this->short_name]);

        if (!empty($this->last_login)) {
            $query->andWhere(new Expression('last_login::date = :last_login', [
                'last_login' => $this->last_login,
            ]));
        }
    }
}
