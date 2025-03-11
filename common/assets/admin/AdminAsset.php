<?php

namespace app\common\assets\admin;

use dmstr\web\AdminLteAsset;
use yii\web\AssetBundle;

/**
 * Class AdminAsset
 * @package app\common\assets\admin
 */
class AdminAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@app/common/assets/admin';
    /**
     * @var array
     */
    public $css = [
        'css/multiselect.css',
        'css/admin.css',
    ];
    /**
     * @var array
     */
    public $js = [];
    /**
     * @var array
     */
    public $depends = [
        AdminLteAsset::class,
    ];
}
