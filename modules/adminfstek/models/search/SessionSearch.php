<?php

namespace app\modules\adminfstek\models\search;

use app\common\models\UserModel;
use app\common\validators\PGIdValidator;
use app\models\db\admin\AdminUser;
use app\models\db\admin\Session;
use app\models\db\admin\SessionAdmin;
use app\models\db\SessionVetadmin;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class SessionSearch
 * @package app\modules\adminfstek\models\search
 */
class SessionSearch extends BaseSearchModel
{
    const TARGET_API = 1;
    const TARGET_ADMIN = 2;
    const TARGET_VETADMIN = 3;
    const TARGET_ANDROID = 4;

    /**
     * @var int
     */
    public $target;
    /**
     * @var int
     */
    public $id_user;
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $fullname;
    /**
     * @var string
     */
    public $last_active_at;
    /**
     * @var string
     */
    public $valid_until;
    /**
     * @var string
     */
    public $ip;

    /**
     * @var array
     */
    protected $sortAttributes = ['id_user', 'target', 'login', 'fullname', 'last_active_at', 'valid_until', 'ip'];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['target', 'in', 'range' => [self::TARGET_API, self::TARGET_ADMIN, self::TARGET_VETADMIN, self::TARGET_ANDROID]],
            ['id_user', PGIdValidator::class],
            [['login', 'ip', 'fullname'], 'filter', 'filter' => 'trim'],
            [['login', 'ip', 'fullname'], 'filter', 'filter' => 'strip_tags'],
            [['login', 'ip', 'fullname'], 'string', 'min' => 3, 'max' => 255],
            [['last_active_at', 'valid_until'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @return \yii\db\Expression
     */
    protected function getDefaultOrder()
    {
        return new Expression('coalesce(s.updated_at, s.created_at) desc');
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        $frontQuery = (new Query())
            ->from(Session::tableName() . ' s')
            ->leftJoin(UserModel::tableName() . ' u', 'u.id = s.id_user')
            ->select(new Expression('s.id::text'))
            ->addSelect(['s.id_user', 's.last_active_at', 's.valid_until', 's.ip', 's.ua'])
            ->addSelect(new Expression('(case when strpos(ua, \'VetasApp\') > 0 then ' . self::TARGET_ANDROID . ' else ' . self::TARGET_API . ' end) AS target'))
            ->addSelect('u.login, u.fullname');
        $vetadminQuery = (new Query())
            ->from(SessionVetadmin::tableName() . ' s')
            ->leftJoin(UserModel::tableName() . ' u', 'u.id = s.id_user')
            ->select('s.id')
            ->addSelect(['s.id_user', 's.last_active_at', 's.valid_until', 's.ip', 's.ua'])
            ->addSelect(new Expression(self::TARGET_VETADMIN . ' AS target'))
            ->addSelect('u.login, u.fullname')
            ->andWhere(['not', ['s.id_user' => null]])
            ->andWhere(['>', 'expire', time()]);
        $adminQuery = (new Query())
            ->from(SessionAdmin::tableName() . ' s')
            ->leftJoin(AdminUser::tableName() . ' u', 'u.id = s.id_user')
            ->select('s.id')
            ->addSelect(['s.id_user', 's.last_active_at', 's.valid_until', 's.ip', 's.ua'])
            ->addSelect(new Expression(self::TARGET_ADMIN . ' AS target'))
            ->addSelect('u.login')
            ->addSelect(new Expression('(u.f_fio || \' \' || u.i_fio || COALESCE(\' \' || u.o_fio, \'\')) AS fullname'))
            ->andWhere(['not', ['s.id_user' => null]])
            ->andWhere(['>', 'expire', time()]);

        $target = \Yii::$app->request->get('target');

        switch ($target) {
            case self::TARGET_API;
                return $frontQuery
                    ->andWhere(new Expression('strpos(ua, \'VetasApp\') = 0'));
            case self::TARGET_ANDROID;
                return $frontQuery
                    ->andWhere(['like', 'ua', 'VetasApp']);
            case self::TARGET_ADMIN;
                return $adminQuery;
            case self::TARGET_VETADMIN;
                return $vetadminQuery;
            default:
                return (new Query())
                    ->select('*')
                    ->from(['c' => $frontQuery
                        ->union($vetadminQuery)
                        ->union($adminQuery)]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere([
            'id_user' => $this->id_user,
        ]);

        $query->andFilterWhere(['ilike', 'login', $this->login])
            ->andFilterWhere(['ilike', 'fullname', $this->fullname])
            ->andFilterWhere(['ilike', 'ip', $this->ip]);

        if (!empty($this->last_active_at)) {
            $query->andWhere(new Expression('last_active_at::date = :last_active_at', [
                'last_active_at' => $this->last_active_at,
            ]));
        }
        if (!empty($this->valid_until)) {
            $query->andWhere(new Expression('valid_until::date = :valid_until', [
                'valid_until' => $this->valid_until,
            ]));
        }
    }

    /**
     * @return int
     */
    protected function defaultPagesize()
    {
        return 50;
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * @return array
     */
    public static function targetOptions()
    {
        return [
            self::TARGET_API => 'фронт',
            self::TARGET_ADMIN => 'админка',
            self::TARGET_VETADMIN => 'ветадминка',
            self::TARGET_ANDROID => 'андроид',
        ];
    }
}
