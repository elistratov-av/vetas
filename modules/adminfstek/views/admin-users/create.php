<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $model \app\modules\adminfstek\models\forms\AdminUserForm */
/* @var $roleOptions array */

$this->blocks['content-header'] = 'Новый пользователь'
?>
<div class="user-profile-password-change clearfix">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="user-form col-md-4">
        <?php echo $this->render('_form', [
            'model' => $model,
            'roleOptions' => $roleOptions,
            'create' => true,
        ]); ?>
    </div>
</div>
