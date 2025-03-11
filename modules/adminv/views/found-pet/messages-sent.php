<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\VarDumper;
use yii\web\JsExpression;
use yii\web\View;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel \app\modules\adminv\models\search\FoundPetMessageSentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->blocks['content-header'] = 'Записи';
$js = <<<JS
    $('body').on('click', '.show-message', function(e) {
        e.preventDefault();
        var html = $(this).closest('td').find('div').clone();
        $('#messageModal').find('.modal-body').html(html);
        $('#messageModal').find('.modal-body > div').show();
        $('#messageModal').modal('show');
    });
    $('body').on('click', '.show-errors', function(e) {
        e.preventDefault();
        var html = $(this).closest('td').find('div').clone();
        $('#errorsModal').find('.modal-body').html(html);
        $('#errorsModal').find('.modal-body > div').show();
        $('#errorsModal').modal('show');
    });
JS;

$this->registerJs($js, View::POS_READY);
?>

<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Тип',
                    'attribute' => 'type',
                    'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'label' => 'Единый номер обращения',
                    'attribute' => 'service_number',
                    'headerOptions' => ['width' => '20%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'label' => 'Время создания',
                    'attribute' => 'created_at',
                    'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
                    'filter' => DateRangePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'created_at',
                        'convertFormat' => true,
                        'startAttribute' => 'from',
                        'endAttribute' => 'to',
                        'pluginEvents' => [
                            'cancel.daterangepicker' => new JsExpression("
                    function (ev, picker) {
                        picker.element.val('').change();
                    }"),
                        ],
                        'pluginOptions' => [
                            'opens' => 'right',
                            'locale' => [
                                'cancelLabel' => 'Очистить',
                                'format' => 'Y-m-d',
                            ],
                        ],
                    ]),
                ],
                [
                    'label' => 'HTTP код ответа',
                    'attribute' => 'response_code',
                    'headerOptions' => ['width' => '20%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'label' => 'Ошибки',
                    'format' => 'raw',
                    'headerOptions' => ['width' => '20%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        /** @var $model \app\models\db\found_pet\MessageSent  */
                        if (empty($model->user_error) && empty($model->curl_error)){
                            return '<b>-</b>';
                        }
                        return $model->user_error . '<br>' . $model->curl_error;
                    }
                ],
                [
                    'format' => 'raw',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],

                    'value' => function ($model) {
                        /** @var $model \app\models\db\found_pet\MessageSent  */
                        return '<div style="display: none;">'
                            . '<h3>Отправлено</h3>'
                            . '<pre>' . json_encode(json_decode($model->request), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</pre>'
                            . '<hr><h3>Хедеры ОТВЕТА</h3>'
                            . '<pre>' . VarDumper::export($model->response_headers) . '</pre>'
                            . '<hr><h3>ОТВЕТ</h3>'
                            . '<pre>' . VarDumper::export($model->response) . '</pre>'
                            .'</div>'
                            . Html::a(
                                '<span class="glyphicon glyphicon-eye-open">',
                                '#',
                                [
                                    'class' => 'btn btn-xs btn-success show-message',
                                    'title' => 'Показать сообщение',
                                ]
                            );
                    },
                ],
//                [
//                    'format' => 'raw',
//                    'label' => 'Результат',
//                    'attribute' => 'is_success',
//                    'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
//                    'contentOptions' => ['class' => 'text-center'],
//                    'value' => function ($model) {
//                        return $model->is_success === true ? '<span style="color: green;">Принято</span>' : '<span style="color: red;">Не принято</span>';
//                    },
//                ],
//                [
//                    'label' => 'Ошибки',
//                    'attribute' => 'ad_errors',
//                    'format' => 'raw',
//                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
//                    'contentOptions' => ['class' => 'text-center'],
//                    'value' => function ($model) {
//                        if (empty($model->ad_errors)) {
//                            return '';
//                        }
//                        return '<div style="display: none;"><pre>' . VarDumper::export($model->ad_errors) . '</pre></div>'
//                            . Html::a(
//                                '<span class="glyphicon glyphicon-eye-open">',
//                                '#',
//                                [
//                                    'class' => 'btn btn-xs btn-success show-errors',
//                                    'title' => 'Показать ошибки',
//                                ]
//                            );
//                    },
//                ],
            ],
        ]);
        ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="messageModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="errorsModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
