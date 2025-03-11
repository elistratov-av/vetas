<?php
/** @var $this \yii\web\View */

use app\modules\adminv\models\forms\OrganizationDeleteReasonForm;
use yii\widgets\ActiveForm;

$model = new OrganizationDeleteReasonForm();
?>
<div class="modal fade" id="delete-reason-modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php $form = ActiveForm::begin([
                'id' => 'delete-reason-form',
            ]); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Укажите причину удаления организации</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo $form->field($model, 'reason')
                            ->dropDownList($model::options(), ['prompt' => '- выберите причину -']); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary btn-flat">Подтвердить</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

