<?php

use app\models\db\audit\TimesheetLog;
use app\modules\adminv\models\search\TimesheetLogSearch;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var View               $this
 * @var TimesheetLogSearch $searchModel
 */
$this->blocks['content-header'] = 'Удаления расписаний';
$this->render('_part_JSCSS');
?>

<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'attribute' => 'id_timesheet',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'ФИО Инициатора',
                    'attribute' => 'fio_initiator',
                ],
                [
                    'label' => 'ID Специалиста',
                    'attribute' => 'id_specialist',
                ],
                [
                    'label' => 'Дата удаления',
                    'attribute' => 'date',
                    'value' => 'date',
                    'filter' => DatePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'date',
                        'language' => 'ru',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options' => [
                            'readonly' => true,
                        ],
                        'clientOptions' => [
                            'showButtonPanel' => true,
                            'closeText' => 'Очистить',
                            'onClose' => new JsExpression('
                                function (dateText, inst) {
                                    if ($(window.event.srcElement).hasClass(\'ui-datepicker-close\')) {
                                          $.datepicker._clearDate(this);
                                    }
                                }'
                            )
                        ]
                    ]),
                ],
                [
                    'format' => 'raw',
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'value' => static function ($model) {
                        /** @var TimesheetLog $model */
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open">',
                            ['timesheet-detail', 'id' => $model->id],
                            [
                                'class' => 'btn btn-xs btn-success show-visit',
                                'title' => 'Подробности'
                            ]);
                    },
                ]
            ]
        ])
        ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="visitModal" role="dialog">

</div>
