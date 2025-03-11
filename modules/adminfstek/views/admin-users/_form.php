<?php

use app\models\db\admin\AdminUser;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $model \app\modules\adminfstek\models\forms\AdminUserForm */
/* @var $roleOptions array */
/* @var $fromProfile bool */

$create = $create ?? false;
$fromProfile = $fromProfile ?? false;
$readonly = (\Yii::$app->user->can(AdminUser::ROLE_SECURITY) && !$fromProfile) || ($model->user !== null && $model->user->is_deleted);
?>
<?php
$form = ActiveForm::begin(); ?>
<?php echo $form->field($model, 'login')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'email')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo (Yii::$app->user->can(AdminUser::ROLE_ADMIN) || Yii::$app->user->can(AdminUser::ROLE_ACCOUNTS_MANAGER))
    ? $form->field($model, 'role')->dropDownList($roleOptions)
    : Html::activeHiddenInput($model, 'role'); ?>
<?php echo $form->field($model, 'f_fio')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'i_fio')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php echo $form->field($model, 'o_fio')
    ->textInput(['maxlength' => true, 'readonly' => $readonly]); ?>
<?php if (!$create) {
    if (!$fromProfile && !\Yii::$app->user->can(AdminUser::ROLE_ADMIN)) {
        echo $form->field($model, 'is_blocked')
            ->checkbox();
        if ($model->temp_block) {
            echo $form->field($model, 'temp_block')
                ->checkbox(['label' => 'Временная блокировка до ' . $model->user->block_until]);
        }
    } else {
        Html::activeHiddenInput($model, 'is_blocked');
    }
}
?>
<div class="form-group">
    <?php if ($model->user === null || ($model->user !== null && $model->user->is_deleted !== true)): ?>
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?php if ((!$create && !\Yii::$app->user->can(AdminUser::ROLE_SECURITY)) || $fromProfile) {
            $link = $fromProfile ? ['profile/password-change'] : ['password-change', 'id' => $model->user->id];
            echo Html::a(($fromProfile ? 'Сменить пароль' : 'Сбросить пароль'), $link, ['class' => 'btn btn-danger btn-flat']);
        } ?>
    <?php endif; ?>
    <?php echo Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
</div>
<?php ActiveForm::end(); ?>
