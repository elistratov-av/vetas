<?php

namespace app\modules\adminfstek\models\search;

use app\common\validators\PGIdValidator;
use app\models\db\audit\AuditLog;
use app\modules\audit\models\AuditLogHelper;
use yii\db\Expression;

/**
 * Class LogAuditSearch
 * @package app\modules\adminfstek\models\search
 */
class LogAuditSearch extends BaseSearchModel
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
    public $action;
    /**
     * @var string
     */
    public $date;
    /**
     * @var int
     */
    public $is_success;

    /**
     * @var array
     */
    protected $defaultOrder = ['date' => SORT_DESC];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id_user', PGIdValidator::class],
            [['login'], 'filter', 'filter' => 'trim'],
            [['login'], 'filter', 'filter' => 'strip_tags'],
            [['login'], 'string', 'min' => 3, 'max' => 255],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            ['action', 'in', 'range' => self::types()],
            ['is_success', 'integer'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildQuery($params = [])
    {
        return AuditLog::find();
    }

    /**
     * @inheritDoc
     */
    protected function buildFilter(&$query)
    {
        $query->andFilterWhere([
            'id_user' => $this->id_user,
            'action' => $this->action,
        ]);

        $query->andFilterWhere(['ilike', 'login', $this->login]);

        if (!empty($this->date)) {
            $query->andWhere(new Expression("date::date = :date", [
                'date' => $this->date,
            ]));
        }

        if ($this->is_success === '1') {
            $query->andWhere(['in', 'action', self::types()]);
        } elseif ($this->is_success === '0') {
            $query->andWhere(['action' => AuditLog::ACTION_SYSTEM_FAIL]);
        }
    }

    /**
     * @return array
     */
    public static function types()
    {
        return array_keys(self::typeOptions());
    }

    /**
     * @return array
     */
    public static function typeOptions()
    {
        $options = AuditLogHelper::$action_texts;
        unset($options[AuditLog::ACTION_SYSTEM_FAIL]);

        return $options;
    }

    /**
     * @return array
     */
    public static function successOptions()
    {
        return [
            0 => 'неудача',
            1 => 'успех',
        ];
    }
}
