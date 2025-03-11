<?php

namespace app\modules\admin\assets;

use dmstr\web\AdminLteAsset;
use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/admin/assets';
    public $css = [
        'css/multiselect.css',
        'css/admin.css'
    ];

    public $js = [

    ];

    public $depends = [
        AdminLteAsset::class
    ];
}
