<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.11.18
 * Time: 16:33
 */

namespace app\modules\admin\data;

use Yii;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;
use yii\data\Sort;

/**
 * Class AdminDataProvider
 * @package app\modules\admin\data
 */
class AdminDataProvider extends ActiveDataProvider
{
    private $_sort;


    /**
     * @return bool|Sort|null
     */
    public function getSort()
    {
        if ($this->_sort === null) {
            $this->setSort([]);
        }

        return $this->_sort;
    }


    /**
     * @param $value
     * @throws \yii\base\InvalidConfigException
     */
    public function setSort($value)
    {
        if (is_array($value)) {
            $config = ['class' => AdminSort::className()];
            if ($this->id !== null) {
                $config['sortParam'] = $this->id . '-sort';
            }
            $this->_sort = Yii::createObject(array_merge($config, $value));
        } elseif ($value instanceof Sort || $value === false) {
            $this->_sort = $value;
        } else {
            throw new InvalidArgumentException('Only Sort instance, configuration array or false is allowed.');
        }
    }
}
