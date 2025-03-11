<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */
?>
<header class="main-header">

    <?= Html::a('<span class="logo-mini"></span><span class="logo-lg">ВетАС</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown user user-menu">
                    <a href="<?php echo Url::to(['profile/index']); ?>">
                        <span class="hidden-xs">Вы вошли как <?= Html::encode(Yii::$app->user->identity['login']) ?></span>
                    </a>

                </li>
                <li>
                    <a href="<?php echo Url::to(['site/logout']); ?>"
                       data-method="post"><i class="fa fa-sign-out"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
