<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $user \app\common\models\UserModel */

$this->blocks['content-header'] = "Изменение пароля для пользователя #{$user->id} \"{$user->login}\"";
?>
<div class="user-profile-password-change">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="user-form col-md-4">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'newPassword')->passwordInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'newPasswordRepeat')->passwordInput(['maxlength' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton( 'Сохранить', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Отмена',  Url::toRoute(['users/index']), ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
