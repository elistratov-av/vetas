<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $model \app\modules\admin\models\forms\UserForm */

$this->blocks['content-header'] = 'Новый пользователь'
?>
<div class="user-profile-password-change">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="user-form col-md-4">
        <?php $form = ActiveForm::begin(); ?>
        <?php echo $form->field($model, 'login')
            ->textInput(['maxlength' => true]); ?>
        <?php echo $form->field($model, 'password')
            ->passwordInput(['maxlength' => true]); ?>
        <?php echo $form->field($model, 'passwordRepeat')
            ->passwordInput(['maxlength' => true]); ?>
        <?php echo $form->field($model, 'f_fio'); ?>
        <?php echo $form->field($model, 'i_fio'); ?>
        <?php echo $form->field($model, 'o_fio'); ?>
        <?php echo $form->field($model, 'sex')
            ->radioList(['m' => 'Мужской', 'f' => 'Женский']); ?>
        <?php echo $form->field($model, 'birthday')
            ->widget(\yii\jui\DatePicker::class, [
                'clientOptions' => [
                    'yearRange' => date('Y') - 75 . ':' . date('Y'),
                    'changeMonth' => 'true',
                    'changeYear' => 'true',
                    'firstDay' => '1',
                ],
            ]); ?>
        <div class="form-group">
            <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
            <?php echo Html::a('Отмена', ['/admin/users/index'], ['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
