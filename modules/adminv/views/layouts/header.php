<?php

use app\modules\adminv\models\forms\LoginForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$css = <<<CSS

.main-header .navbar {
    margin-left: 320px;
}

.main-header .logo {
    height: 54px;
    width: 320px;
}

.content-header {
    padding: 15px 15px 0;
}

CSS;

$this->registerCss($css);

/* @var $this \yii\web\View */
/* @var $content string */

/* @var $user \app\common\models\UserModel */
$user = \Yii::$app->user->getIdentity();
$currentOrganization = ($user->specialist !== null && $user->specialist->organization !== null) ? $user->specialist->organization : null;
$organizations = \Yii::$app->session->get('__organizations');
if (!empty($organizations) && is_array($organizations) && $currentOrganization !== null) {
    unset($organizations[$currentOrganization->id]);
}
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
                    <a href="#" data-toggle="dropdown">
                        <span class="hidden-xs">Вы вошли как <?= Html::encode($user->login) ?></span>
                    </a>
                    <?php if ($currentOrganization !== null || !empty($organizations)): ?>
                        <ul class="dropdown-menu">
                            <?php if ($currentOrganization !== null): ?>
                                <li><div class="col-md-12 form-group" style="margin-top: 15px;"><small><?= Html::encode($currentOrganization->short_name); ?></small></div></li>
                            <?php endif; ?>
                            <?php if (!empty($organizations)): ?>
                                <li role="separator" class="divider"></li>
                                <li><div class="col-md-12 form-group">
                                        <?php
                                        $model = new LoginForm();
                                        $form = ActiveForm::begin(['action' => ['site/select-organization'], 'enableClientValidation' => false]); ?>
                                        <?= $form->field($model, 'id_organization')->dropDownList($organizations)->label('Сменить организацию:') ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <?= Html::submitButton('Сменить', ['class' => 'btn btn-primary btn-flat']) ?>
                                            </div>
                                        </div>
                                        <?php ActiveForm::end(); ?>
                                    </div></li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </li>
                <li>
                    <a href="<?php echo Url::to(['/adminv/site/logout']); ?>"
                       data-method="post"><i class="fa fa-sign-out"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
