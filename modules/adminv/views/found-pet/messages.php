<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\VarDumper;
use yii\web\JsExpression;
use yii\web\View;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel \app\modules\adminv\models\search\FoundPetMessageSearch */
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
                    'filter' => [
                        'create-ad' => 'create-ad',
                        'update-ad' => 'update-ad',
                        'close-ad' => 'close-ad',
                        'extend-ad' => 'extend-ad',
                        'subscribe' => 'subscribe',
                        'update-subscribe' => 'update-subscribe',
                        'unsubscribe' => 'unsubscribe',
                    ],
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
                    'format' => 'raw',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        return '<div style="display: none;"><pre>' . VarDumper::export($model->body) . '</pre>'
                            . '<hr><pre>' . json_encode($model->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</pre>'
                            . '<hr><pre>' . VarDumper::export($model->headers) . '</pre></div>'
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
                [
                    'format' => 'raw',
                    'label' => 'Результат',
                    'attribute' => 'is_success',
                    'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        return $model->is_success === true ? '<span style="color: green;">Принято</span>' : '<span style="color: red;">Не принято</span>';
                    },
                ],
                [
                    'label' => 'Ошибки',
                    'attribute' => 'ad_errors',
                    'format' => 'raw',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        if (empty($model->ad_errors)) {
                            return '';
                        }
                        return '<div style="display: none;"><pre>' . VarDumper::export($model->ad_errors) . '</pre></div>'
                            . Html::a(
                                '<span class="glyphicon glyphicon-eye-open">',
                                '#',
                                [
                                    'class' => 'btn btn-xs btn-success show-errors',
                                    'title' => 'Показать ошибки',
                                ]
                            );
                    },
                ],
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
