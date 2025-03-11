<?php

use app\models\db\admin\AdminUser;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $model \app\modules\adminfstek\models\forms\UserForm */
/* @var $user \app\common\models\UserModel */
/* @var $action string */

$readonly = \Yii::$app->user->can(AdminUser::ROLE_SECURITY) || (isset($user) && $user->is_deleted);
?>
<?php $form = ActiveForm::begin(); ?>
<?php echo $form->field($model, 'login')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'email')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'f_fio')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'i_fio')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'sudir_uid')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'o_fio')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'sex')
    ->radioList(['m' => 'Мужской', 'f' => 'Женский'], ['itemOptions' => ['disabled' => $readonly]]);
if ($readonly) {
    echo Html::activeHiddenInput($model, 'sex');
}
?>
<?php if ($readonly) {
    echo $form->field($model, 'birthday')
        ->textInput(['maxlength' => true, 'readonly' => $readonly]);
} else {
    echo $form->field($model, 'birthday')
        ->widget(\yii\jui\DatePicker::class, [
            'clientOptions' => [
                'yearRange' => date('Y') - 75 . ':' . date('Y'),
                'changeMonth' => 'true',
                'changeYear' => 'true',
                'firstDay' => '1',
            ],
        ]);
}
?>
<?php if ($action == 'edit' && !\Yii::$app->user->can(AdminUser::ROLE_ADMIN)) {
    echo $form->field($model, 'is_blocked')
        ->checkbox();
    if ($model->temp_block) {
        echo $form->field($model, 'temp_block')
            ->checkbox(['label' => 'Временная блокировка до ' . $user->block_until]);
    }
}
?>
<div class="form-group">
    <?php if (!isset($user) || (isset($user) && $user->is_deleted !== true)): ?>
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?php if ($action == 'edit' && !\Yii::$app->user->can(AdminUser::ROLE_SECURITY)) {
            echo Html::a('Сбросить пароль', ['password-change', 'id' => $user->id], ['class' => 'btn btn-danger btn-flat']);
        } ?>
    <?php endif; ?>
    <?php echo Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
</div>
<?php ActiveForm::end(); ?>
