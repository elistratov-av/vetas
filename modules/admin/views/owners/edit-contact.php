<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.12.18
 * Time: 16:45
 */

use app\models\db\ContactTypes;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->blocks['content-header'] = "Изменить контакт";
$types = ContactTypes::find()->select('name')->where(['entity_type' => 'pet_owner'])->indexBy('id')->asArray()->column();
?>

<div class="user-profile-password-change">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="user-form">
        <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>
        <?= $form->field($model, 'id_contact_type')
            ->dropDownList($types)
            ->label('Тип контакта') ?>
        <?= $form->field($model, 'name')->label('Контакт')?>
        <div class="form-group">
            <?= Html::submitButton( 'Сохранить',  ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Отмена',  Url::toRoute(['owners/index']), ['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
