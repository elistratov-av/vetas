<?php

namespace app\assets;

use yii\web\AssetBundle;

class AutocompleteAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/jquery-ui.min.css',
    ];
    public $js = [
        'js/jquery-ui.min.js',
    ];
}
