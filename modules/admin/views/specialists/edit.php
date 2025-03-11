<?php

/* @var $this \yii\web\View */
/* @var $model \app\modules\admin\models\forms\SpecialistForm */
/* @var $organizations array */
/* @var $roleOptions array */

$this->blocks['content-header'] = "Редактирование специалиста: {$model->user->fullname}";
?>
<div class="user-profile-password-change">
    <?php echo $this->render('form', [
        'model' => $model,
        'organizations' => $organizations,
        'roleOptions' => $roleOptions,
    ]); ?>
</div>
