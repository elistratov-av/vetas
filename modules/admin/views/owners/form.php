<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.12.18
 * Time: 12:37
 */

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$js = <<<JS
        $(document).ready(function ()
        { 
            $('#ownereditform-is_legal').change(function () {
                if (this.checked) {
                    $('#checkboxList').fadeIn();
                } else {
                    $('#checkboxList').fadeOut();
                }
            });
        });
JS;

$this->registerJs($js, View::POS_READY);
?>

<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'f_fio')->label('Фамилия') ?>
    <?= $form->field($model, 'i_fio')->label('Имя') ?>
    <?= $form->field($model, 'o_fio')->label('Отчество') ?>
    <?= $form->field($model, 'birthday')->widget(\yii\jui\DatePicker::className(),
        [
            'clientOptions' =>
                [
                    'yearRange' =>  date('Y')-75 . ':' . date('Y'),
                    'changeMonth' => 'true',
                    'changeYear' => 'true',
                    'firstDay' => '1',
                ],
        ])->label('Дата рождения') ?>
    <?= $form->field($model, 'snils')->label('СНИЛС') ?>
    <?= $form->field($model, 'is_legal')->checkbox()->label('Юридическое лицо') ?>
    <div id="checkboxList" style="display:none">
        <?= $form->field($model, 'jur_name')->label('Название') ?>
        <?= $form->field($model, 'inn')->label('ИНН') ?>
        <?= $form->field($model, 'ogrn')->label('ОГРН') ?>
    </div>

    </div>
    <div class="form-group">
        <?= Html::submitButton( 'Сохранить',  ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Отмена',  Url::to('index'), ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
