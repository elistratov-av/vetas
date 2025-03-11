<?php


namespace app\common\assets;


use yii\web\AssetBundle;

class StickyTableHeadersAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-sticky-table-headers/';

    public $js = [
        'js/jquery.stickytableheaders.min.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
