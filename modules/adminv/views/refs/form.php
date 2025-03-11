<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $crud_id string */
/* @var $model \app\models\db\ActiveRecord */

?>
<div class="user-form col-md-4">
    <?php $form = ActiveForm::begin(); ?>
    <?php echo Html::activeHiddenInput($model, 'id'); ?>
    <?php echo $form->field($model, 'title')->label('Наименование') ?>
    <div class="form-group">
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?php echo Html::a('Отмена', ['/adminv/refs/index', 'crud_id' => $crud_id], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
