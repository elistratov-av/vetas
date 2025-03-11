<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $user \app\common\models\UserModel */

$this->blocks['content-header'] = "Изменение пароля для пользователя #{$user->id} \"{$user->login}\" " . $user->fullname;
?>
<div class="user-profile-password-change">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="user-form col-md-4">
        <?php $form = ActiveForm::begin(); ?>
        <?php echo $form->field($model, 'newPassword')
            ->passwordInput(['maxlength' => true]); ?>
        <?php echo $form->field($model, 'newPasswordRepeat')
            ->passwordInput(['maxlength' => true]); ?>
        <div class="form-group">
            <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
            <?php echo Html::a('Отмена', ['/adminv/users/index'], ['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
