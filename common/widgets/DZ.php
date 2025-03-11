<?php

namespace app\common\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\Request;
use yii\web\View;
use app\common\assets\DZAsset;

/**
 * Class DZ
 * @package app\common\widgets
 */
class DZ extends Widget
{
    /**
     * @var array
     */
    public $options = [];
    /**
     * @var array Dropzone settings
     * @see http://www.dropzonejs.com/#configuration
     */
    public $clientOptions = [];
    /**
     * @var array Dropzone events
     * @see http://www.dropzonejs.com/#dropzone-methods
     * @see http://www.dropzonejs.com/#events
     */
    public $clientEvents = [];


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        /* Note: if you set `$autoDiscover` to true you need to set class='dropzone' to element. */
        if (!isset($this->options['autoDiscover'])) {
            $this->options['autoDiscover'] = true;
        }
        if (!isset($this->options['createContainer'])) {
            $this->options['createContainer'] = true;
        }

        $this->registerAssets();
    }

    /**
     * Register asset bundle
     */
    public function registerAssets()
    {
        $view = $this->getView();

        DZAsset::register($view);

        if ($this->options['autoDiscover'] === false) {
            $view->registerJs('Dropzone.autoDiscover = false;', View::POS_END);
        }

        $this->clientOptions['headers'] = isset($this->clientOptions['headers']) ? $this->clientOptions['headers'] : [];
        $this->clientOptions['headers'][Request::CSRF_HEADER] = Yii::$app->request->getCsrfToken();

        $clientOptions = $this->clientOptions;

        if (!empty($this->clientEvents)) {
            $js = 'function() {' . PHP_EOL;
            foreach ($this->clientEvents as $event => $handler) {
                $js .= 'this.on(\'' . $event . '\', ' . $handler . ');' . PHP_EOL;
            }
            $js .= '}' . PHP_EOL;
            $clientOptions['init'] = new JsExpression($js);
        }

        $js = '';
        if ($this->options['autoDiscover'] === false) {
            $js .= '$(\'#' . $this->options['id'] . '\').dropzone(' . Json::encode($clientOptions) . ');';
        } else {
            $js .= 'Dropzone.options.' . $this->options['id'] . ' = ' . Json::encode($clientOptions) . ';' . PHP_EOL;
        }

        $view->registerJs($js);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';
        if ($this->options['createContainer'] === true) {
            $html .= '<div id="' . $this->options['id'] . '" class="dropzone"></div>';
        }

        return $html;
    }
}
