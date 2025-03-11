<?php

namespace app\common\assets;

use yii\web\AssetBundle;

/**
 * Class DZAsset
 * @package common\assets
 */
class DZAsset extends AssetBundle
{
    public $sourcePath = '@vendor/enyo/dropzone/dist/min';

    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public $css = [
        //'basic.min.css',
        'dropzone.min.css',
    ];

    public $js = [
        'dropzone.min.js',
    ];
}
