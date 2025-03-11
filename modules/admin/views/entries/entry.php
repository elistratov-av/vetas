<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

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
        <p class="login-box-msg">Войдите, чтобы начать работать</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form->field($model, 'login')->textInput(['autofocus' => true])->label('Логин') ?>

        <?= $form->field($model, 'password')->passwordInput()->label('Пароль') ?>

        <div class="row">
            <div class="col-xs-8">
                <?= $form->field($model, 'rememberMe')->checkbox([
                        'label' => 'Запомнить меня',
                ]) ?>
            </div>
            <div class="col-xs-4">
                <?= Html::submitButton('Войти', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
</div>
</div>


