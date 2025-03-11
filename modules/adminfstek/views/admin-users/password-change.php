<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $user \app\models\db\admin\AdminUser */

$this->blocks['content-header'] = 'Изменение пароля';
?>
<div class="user-profile-password-change">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="user-form col-md-4">
        <?php $form = ActiveForm::begin(); ?>
        <?php echo $form->field($model, 'old_password')
            ->passwordInput(['maxlength' => true]); ?>
        <?php echo $form->field($model, 'new_password')
            ->passwordInput(['maxlength' => true]); ?>
        <?php echo $form->field($model, 'new_password_repeat')
            ->passwordInput(['maxlength' => true]); ?>
        <div class="form-group">
            <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
            <?php echo Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
