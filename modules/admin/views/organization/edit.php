<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.12.18
 * Time: 17:43
 */

use app\models\db\OrgTypes;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


$this->blocks['content-header'] = 'Редактирование информации';
?>

<div class="user-profile-password-change">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="user-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name')->label('Название') ?>
    <?= $form->field($model, 'short_name')->label('Короткое название') ?>
    <?php echo $form->field($model, 'id_org_type')
        ->dropDownList(OrgTypes::options(), ['prompt' => '- выберите тип организации -'])
        ->label('Тип организации'); ?>
    <?= $form->field($model, 'kpp')->label('КПП (9 цифр)') ?>
    <?= $form->field($model, 'inn')->label('ИНН (10 цифр)') ?>
    <?= $form->field($model, 'ogrn')->label('ОГРН (13 цифр)') ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить',  ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Отмена',  Url::to('index'), ['class' => 'btn btn-default']) ?>
    </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

