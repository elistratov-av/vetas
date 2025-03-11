<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.02.19
 * Time: 11:08
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm; ?>


<div class="user-profile-password-change">
    <h1><?= Html::encode($this->title) ?></h1>
<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name')->label('Название услуги') ?>
    <?= $form->field($model, 'sort_by')->textInput(['type' => 'number'])->label('Порядок сортировки') ?>
    <?= $form->field($model, 'duration')->textInput(['type' => 'number'])->label('Продолжительность') ?>
    <?= $form->field($model, 'cooldown')->textInput(['type' => 'number'])->label('Пауза') ?>
    <?= $form->field($model, 'id_service_type')
        ->dropDownList($service_type, [
            'prompt' => 'Выберите тип услуги'
        ])
        ->label('Тип услуги') ?>
    <?= $form->field($model, 'id_service_goal')
        ->dropDownList($service_goal, [
            'prompt' => 'Выберите цель оказания услуги'
        ])
        ->label('Цель оказания услуги') ?>
    <?= $form->field($model, 'at_home')->checkbox()->label('Услуга доступна на дому') ?>



    <div class="form-group">
        <?= Html::submitButton('Сохранить',  ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Отмена',  Url::toRoute(['mos-ru/services']), ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
</div>