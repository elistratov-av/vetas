<?php

use app\common\widgets\DZ;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

/* @var $this \yii\web\View */
/* @var $model \app\models\db\AndroidBuild */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$js = <<<JS
window.DZF = (function ($) {
    var pub = {
        alert: function (message, type) {
            type = type || 'info';
            if (type == 'danger') {
                type = 'error';
            }
            if (typeof yii.noty === 'function') {
                yii.noty.alert(message, type);                
            } else {
                alert(message);
            }
        },
        parseJsonMessage: function (json) {
            return (json.message !== undefined && json.message !== null && $.trim(json.message) != '')
                ? $.trim(json.message)
                : ((json.name !== undefined && json.name !== null && $.trim(json.name != ''))
                    ? $.trim(json.name)
                    : null);
        },
        parseTextMessage: function (str) {
            str = $.trim(str);
            var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/;
            if (rbrace.test(str)) {
                return pub.parseJsonMessage($.parseJSON(str));
            } else {
                return str;
            }
        },
        alertAjaxError: function (jqXHR, defaultMsg) {
            if (jqXHR === undefined) {
                return;
            }
            var message;
            if (jqXHR.responseJSON !== undefined) {
                message = pub.parseJsonMessage(jqXHR.responseJSON);
                if (message !== null) {
                    return pub.alert(message, 'error');
                }
            }
            if (jqXHR.responseText !== undefined && jqXHR.responseText !== null && $.trim(jqXHR.responseText) != '') {
                // Dropzone returns not jqXHR but xhr object (json response as plain string)!
                message = pub.parseTextMessage(jqXHR.responseText);
                if (message !== null) {
                    return pub.alert(message, 'error');
                }
            }
            pub.alert(defaultMsg || 'Error', 'error');
        },
        clearDz: function (file, dz) {
            dz.removeAllFiles(true);
        },
        clearDzTm: function (file, dz) {
            setTimeout(function () {
                pub.clearDz(file, dz);
            }, 1000);
        },
        updateInput: function (file, dz) {
            $('input[name$="[filename]"]').val(file.upload.filename);
            pub.clearDzTm(file, dz);
        }
    };
    return pub;
}(jQuery));
JS;

$this->registerJs($js);

$dir = \Yii::getAlias('@webroot/android');

$this->blocks['content-header'] = 'Сборки Android';
?>
<div class="box">
    <div class="box-body">
        <div class="col-md-4">
            <?php $form = ActiveForm::begin(); ?>
            <?php echo $form->field($model, 'filename')
                ->textInput(['maxlength' => true, 'readonly' => true]); ?>
            <?php echo DZ::widget([
                'options' => [
                    'id' => 'file-upload',
                    'autoDiscover' => false,
                ],
                'clientOptions' => [
                    'url' => Url::to(['android/upload']),
                    'uploadMultiple' => false,
                    'paramName' => 'upload',
                    'acceptedFiles' => '.apk',
                    'dictDefaultMessage' => 'Кликните или перетащите сюда файлы для загрузки',
                    'addRemoveLinks' => false,
                    'autoProcessQueue' => true,
                    'createImageThumbnails' => false,
                    'chunking' => true,
                    'forceChunking' => true,
                    'chunkSize' => 2000000,
                    'chunksUploaded' => (new JsExpression('function(file, done) {DZF.updateInput(file, $("#file-upload").get(0).dropzone);}')),
                ],
                'clientEvents' => [
                    'error' => (new JsExpression('function(file, response, xhr) {console.log("error"); DZF.clearDzTm(file, this); return DZF.alertAjaxError(xhr, response.message);}')),
                ],
            ]); ?>
            <?php echo $form->field($model, 'version')
                ->textInput(['maxlength' => true]); ?>
            <?php echo $form->field($model, 'update_required')
                ->checkbox(); ?>
            <div class="form-group">
                <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
                <?php echo Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>
<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                'id',
                'version',
                [
                    'attribute' => 'filename',
                    'format' => 'raw',
                    'value' => function ($model) use ($dir) {
                        /* @var $model \app\models\db\AndroidBuild */
                        if (empty($model->filename)) {
                            $html = '-';
                        } else {
                            $html = $model->filename;
                            if (!is_file($dir . '/' . $model->filename)) {
                                $html .= ' <span class="glyphicon glyphicon-exclamation-sign text-danger" title="Файл отсутствует"></span>';
                            }
                        }
                        return $html;
                    },
                ],
                [
                    'attribute' => 'update_required',
                    'value' => function ($model) {
                        return ($model->update_required === true) ? "Да" : "Нет";
                    },
                ],
                'created_at',
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{delete}',
                    'buttons' =>
                        [
                            'delete' => function ($url, $model, $key) {
                                /* @var $model \app\models\db\AndroidBuild */
                                return Html::a(
                                        '<span class="glyphicon glyphicon-trash"></span>',
                                        ['delete', 'id' => $model->id],
                                        [
                                            'class'        => 'btn btn-xs btn-danger',
                                            'title'        => 'Удалить',
                                            'data-method'  => 'post',
                                            'data-confirm' => 'Вы уверены, что хотите удалить билд?',
                                        ]
                                    );
                            },
                        ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
