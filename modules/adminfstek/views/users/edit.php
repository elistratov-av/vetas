<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \app\modules\adminfstek\models\forms\UserForm */
/* @var $user \app\common\models\UserModel */

$this->blocks['content-header'] = "Редактирование информации для пользователя #{$user->id} \"{$user->login}\"";
?>
<div class="user-profile-password-change clearfix">
    <h1><?php echo Html::encode($this->title) ?></h1>
    <div class="user-form col-md-4">
        <?php echo $this->render('_form', [
            'model' => $model,
            'user' => $user,
            'action' => 'edit',
        ]); ?>
    </div>
</div>
