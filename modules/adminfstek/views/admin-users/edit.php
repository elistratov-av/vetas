<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \app\modules\adminfstek\models\forms\AdminUserForm */
/* @var $roleOptions array */
/* @var $fromProfile bool */

$fromProfile = $fromProfile ?? false;

$this->blocks['content-header'] = 'Редактирование пользователя';
?>
<div class="user-profile-password-change clearfix">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="user-form col-md-4">
        <?php echo $this->render('_form', [
            'model' => $model,
            'roleOptions' => $roleOptions,
            'fromProfile' => $fromProfile,
        ]); ?>
    </div>
</div>
