<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.11.18
 * Time: 16:39
 */

namespace app\modules\admin\data;

use Yii;
use yii\data\Sort;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\Request;

/**
 * Class AdminSort
 * @package app\modules\admin\data
 */
class AdminSort extends Sort
{
    /**
     * @param string $attribute
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function link($attribute, $options = [])
    {
        if (($direction = $this->getAttributeOrder($attribute)) !== null) {

            $class = $direction === SORT_DESC ? 'desc' : 'asc';
            if (isset($options['class'])) {
                $options['class'] .= ' ' . $class;
            } else {
                $options['class'] = $class;
            }
        }

        $url = $this->createUrl($attribute);
        $options['data-sort'] = $this->createSortParam($attribute);

        if (isset($options['label'])) {
            $label = $options['label'];
            unset($options['label']);
        } else {
            if (isset($this->attributes[$attribute]['label'])) {
                $label = $this->attributes[$attribute]['label'];
            } else {
                $label = Inflector::camel2words($attribute);
            }

        }
        $img = Html::tag('span','',  ['class' => 'glyphicon glyphicon-sort']);
        return $label . ' ' . Html::a($img, $url, $options);
    }

    /**
     * @param string $attribute
     * @param bool $absolute
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function createUrl($attribute, $absolute = false)
    {
        if (($params = $this->params) === null) {
            $request = Yii::$app->getRequest();
            $params = $request instanceof Request ? $request->getQueryParams() : [];
        }
        $params[$this->sortParam] = $this->createSortParam($attribute);
        $params[0] = $this->route === null ? Yii::$app->controller->getRoute() : $this->route;
        $urlManager = $this->urlManager === null ? Yii::$app->getUrlManager() : $this->urlManager;
        if ($absolute) {
            return $urlManager->createAbsoluteUrl($params);
        }
        return $urlManager->createUrl($params);
    }
}