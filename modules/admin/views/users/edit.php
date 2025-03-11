<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \app\modules\admin\models\forms\UserForm */
/* @var $user \app\common\models\UserModel */

$this->blocks['content-header'] = "Редактирование информации для пользователя #{$user->id} \"{$user->login}\"";
?>
<div class="user-profile-password-change">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="user-form col-md-4">
        <?php $form = ActiveForm::begin(); ?>
        <?php echo $form->field($model, 'login'); ?>
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
        <?php echo $form->field($model, 'is_blocked')
            ->checkbox()
            ->label('Признак блокировки'); ?>
        <?php if ($model->temp_block) {
            echo $form->field($model, 'temp_block')
                ->checkbox()
                ->label('Временная блокировка до ' . $user->block_until);
        } ?>
        <div class="form-group">
            <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']); ?>
            <?php echo Html::a('Сменить пароль', ['/admin/users/password-change', 'id' => $user->id], ['class' => 'btn btn-primary btn-flat']); ?>
            <?php echo Html::a('Отмена', ['/admin/users/index'], ['class' => 'btn btn-default']); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
