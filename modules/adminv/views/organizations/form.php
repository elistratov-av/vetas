<?php

use app\models\db\OrgTypes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use roboapp\multiselect\MultiSelect;

/* @var $this \yii\web\View */
/* @var $model \app\models\db\Organizations */
/* @var $organizationsOptions array */
/* @var $districts array */
?>
<div class="user-form col-md-4">
    <?php $form = ActiveForm::begin(); ?>
    <?php echo Html::activeHiddenInput($model, 'id'); ?>
    <?php echo $form->field($model, 'parent_id')
        ->dropDownList($organizationsOptions)
        ->label('Родительская организация'); ?>
    <?php echo $form->field($model, 'name')->label('Полное наименование') ?>
    <?php echo $form->field($model, 'short_name')->label('Сокращенное наименование') ?>
    <?php echo $form->field($model, 'id_org_type')
        ->dropDownList(OrgTypes::options(), ['prompt' => '- выберите тип организации -'])
        ->label('Тип организации'); ?>
    <?php echo $form->field($model, 'inn')->label('ИНН (10 цифр)') ?>
    <?php echo $form->field($model, 'kpp')->label('КПП (9 цифр)') ?>
    <?php echo $form->field($model, 'ogrn')->label('ОГРН (13 цифр)') ?>
    <?php echo $form->field($model, 'reg_number')->label('Регистрационный номер') ?>
    <div class="form-group field-organizations-bti_adm_district_codes required <?php echo ($model->hasErrors('bti_adm_district_codes')) ? 'has-error' : '' ?>">
    <?php
    if (!empty($districts)) {
        $selected_districts = empty($model->bti_adm_district_codes) ? [] : $model->bti_adm_district_codes->getValue();
        echo Html::hiddenInput('Organizations[bti_adm_district_codes]', null);
        echo Html::label('Обслуживаемые округа', 'Organizations[bti_adm_district_codes]');
        echo MultiSelect::widget([
            'name' => 'Organizations[bti_adm_district_codes]',
            'value' => $selected_districts,
            'data' => $districts,
            'options' => [
                'multiple' => true,
            ],
            'clientOptions' => [
                'numberDisplayed' => 1,
                'nSelectedText' => 'выбрано',
                'nonSelectedText' => 'Не выбрано'
            ]
        ]);
        echo Html::error($model, 'bti_adm_district_codes', ['class' => 'help-block']);
    } else {
        echo '<br>Не удалось подключиться к ЕФСП для получения списка округов. Сервис не доступен<br>';
    }
    ?>
    </div>
    <div class="form-group">
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?php echo Html::a('Отмена', ['/adminv/organizations/index'], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
