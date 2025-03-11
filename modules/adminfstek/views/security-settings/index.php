<?php

/* @var $this \yii\web\View */

/* @var $model \app\models\db\admin\SecuritySettings */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Настройки безопасности';

$this->blocks['content-header'] = $this->title;
?>
<div class="box">
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <?php $form = ActiveForm::begin(); ?>
                <?php foreach ([
                                   'jwt_token_ttl',
                                   'allowed_login_attempts',
                                   'password_min_duration',
                                   'password_duration',
                                   'password_compare_previous',
                               ] as $attribute): ?>
                    <?php echo $form->field($model, $attribute); ?>
                <?php endforeach; ?>
                <div class="form-group">
                    <hr>
                    <h4>Настройки сложности пароля:</h4>
                </div>
                <?php echo $form->field($model, 'password_min_length'); ?>
                <?php foreach ([
                                   'password_contains_letters',
                                   'password_both_case',
                                   'password_contains_digits',
                                   'password_contains_symbols',
                               ] as $attribute) {
                    echo $form->field($model, $attribute)->checkbox();
                } ?>
                <?php echo $form->field($model, 'password_min_changed_symbols'); ?>
                <div class="form-group">
                    <hr>
                    <h4>Авторизация внешних систем:</h4>
                </div>
                <?php echo $form->field($model, 'external_services_auth_enabled')->checkbox(); ?>
                <div class="form-group">
                    <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
                    <?php echo Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
