<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\VarDumper;
use yii\web\JsExpression;
use yii\web\View;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel \app\modules\adminv\models\search\FoundPetAdSearch */
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
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => [
                        'L' => 'lost', 'F' => 'found'
                    ]
                ],
                [
                    'label' => 'Единый номер обращения',
                    'attribute' => 'service_number',
                    'headerOptions' => ['width' => '20%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'format' => 'raw',
                    'label' => 'Статус',
                    'attribute' => 'is_active',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        /* @var $model \app\models\db\found_pet\Ad */
                        return $model->is_active === true ? '<span style="color: green;">Активно</span>' : '<span style="color: red;">Неактивно</span>';
                    },
                ],
                [
                    'label' => 'Время создания',
                    'attribute' => 'created_at',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Время изменения',
                    'attribute' => 'updated_at',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Активно до',
                    'attribute' => 'active_till',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Время удаления',
                    'attribute' => 'closed_at',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                ],
                [
                    'format' => 'raw',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        /* @var $model \app\models\db\found_pet\Ad */
                        return '<div style="display: none;"><pre>'
                            . VarDumper::export($model->toArray(['*'],
                                [
                                    'author',
                                    'address',
                                    'species',
                                    'breed',
                                    'color',
                                ]))
                            . '</pre></div>'
                            . Html::a(
                                '<span class="glyphicon glyphicon-eye-open">',
                                '#',
                                [
                                    'class' => 'btn btn-xs btn-success show-message',
                                    'title' => 'Показать объявление',
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
