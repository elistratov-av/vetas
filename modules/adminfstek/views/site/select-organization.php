<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model \app\modules\adminv\models\forms\LoginForm */

$this->title = 'Вход';
$this->params['breadcrumbs'][] = $this->title;

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>

<div class="login-box">
    <div class="login-logo">
        Панель управления <b>ВетАС</b>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">Выберите организацию</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form->field($model, 'id_organization')->dropDownList($model->organizations)->label('Организация') ?>

        <div class="row">
            <div class="col-xs-4">
                <?= Html::submitButton('Войти', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
</div>
</div>


