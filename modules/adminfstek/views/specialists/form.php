<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $model \app\modules\adminfstek\models\forms\SpecialistForm */
/* @var $organizations array */
/* @var $roleOptions array */

$css = <<<CSS
#specialistform-roles label {
    clear: both;
    display: block;
    margin-left: 20px;
}
CSS;

$this->registerCss($css);
?>
<div class="user-form col-md-4">
    <?php $form = ActiveForm::begin(); ?>
    <?php echo Html::activeHiddenInput($model, 'id_user'); ?>
    <?php echo $form->field($model, 'id_organization')
        ->dropDownList($organizations, [
            'prompt' => 'Выберите организацию',
        ]); ?>
    <?php echo $form->field($model, 'reg_date')->widget(\yii\jui\DatePicker::class, [
        'clientOptions' => [
            'yearRange' => date('Y') - 75 . ':' . date('Y'),
            'changeMonth' => 'true',
            'changeYear' => 'true',
            'firstDay' => '1',
            'clearOn' => 'button',
        ],
    ]); ?>
    <?php echo $form->field($model, 'expel_date')->widget(\yii\jui\DatePicker::class, [
        'clientOptions' => [
            'yearRange' => date('Y') - 75 . ':' . date('Y'),
            'changeMonth' => 'true',
            'changeYear' => 'true',
            'firstDay' => '1',
        ],
    ]); ?>
    <?php echo $form->field($model, 'roles')
        ->checkboxList($roleOptions); ?>
    <div class="row">
        <div class="form-group">
            <div class="col-md-4">
                <label class="control-label">Фото</label>
                <?php if (!empty($model->specialist) && !empty($model->specialist->photoFile)): ?>
                    <img src="<?php echo $model->specialist->photoFile->path; ?>"
                         class="img-responsive img-thumbnail" alt="">
                    <?php echo $form->field($model, 'delete_file')->checkbox(); ?>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <?php echo $form->field($model, 'file')->fileInput()->label('Загрузить изображение'); ?>
            </div>
        </div>
    </div>
    <div class="row"><hr></div>
    <div class="form-group">
        <?php if ($model->user === null || ($model->user !== null && $model->user->is_deleted !== true)): ?>
            <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?php echo Html::a('Отмена', ['users/index'], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
