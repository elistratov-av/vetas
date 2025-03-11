<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.12.18
 * Time: 13:46
 * @var string[] $dataProvider
 * @var string[] $searchModel
 */

use app\modules\admin\helpers\VisitStatusHelper;
use app\modules\admin\models\Visits;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->blocks['content-header'] = 'Приемы' ?>

<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '100px', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Время приема',
                    'attribute' => 'time_range',
                    'value' => function ($visit) {
                        if ($visit->channel == 4) {
                            return $visit->fact_start_dttm ?? $visit->created_at;
                        } else {
                            $time = str_replace("[\"", '', $visit->time_range);
                            $time = str_replace('","', ' — ', $time);
                            $time = str_replace('")', ' ', $time);
                            return $time;
                        }
                    },
                    'filter' => DateRangePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'time_range',
                        'convertFormat' => true,
                        'startAttribute' => 'from',
                        'endAttribute' => 'to',
                        'pluginEvents' => [
                            'cancel.daterangepicker' => new JsExpression("
                    function (ev, picker) {
                        picker.element.val('').change();
                    }")
                        ],
                        'pluginOptions' => [
                            'opens' => 'right',
                            'locale' => [
                                'cancelLabel' => 'Очистить',
                                'format' => 'Y-m-d',
                            ],
                        ],
                    ])
                ],
                [
                    'label' => 'Номер талона',
                    'attribute' => 'ticket',
                    'headerOptions' => ['width' => '100px', 'class' => 'text-center'],
                    'format' => 'raw',
                    'value' => function (Visits $model) {
                        return Html::a(Html::encode($model->ticket_number), Url::to(['visit/info', 'id' => $model->id]));
                    },
                ],
                [
                    'label' => 'Организация',
                    'attribute' => 'short_name',
                    'value' => 'organization.short_name',
                ],

                [
                    'label' => 'Владелец',
                    'attribute' => 'fullname',
                    'value' => function (Visits $model) {
                        return Html::a(Html::encode($model->owner->fullname), Url::to(['owners/profile', 'id' => $model->owner->id]), [
                            'target' => '_blank',
                        ]);
                    },
                    'format' => 'raw',
                ],
                [
                    'label' => 'Животное',
                    'attribute' => 'petName',
                    'value' => 'pets.name',
                ],
                [
                    'label' => 'Статус',
                    'attribute' => 'status',
                    'format' => 'raw',
                    'filter' => MultiSelect::widget([
                        'model' => $searchModel,
                        'attribute' => 'status',
                        'data' => VisitStatusHelper::statusList(),
                        'options' => [
                            'multiple' => true,
                        ],
                    ]),
                    'value' => function ($visit) {
                        return \app\modules\admin\helpers\VisitStatusHelper::statusLabel($visit->status);
                    }
                ],
                [
                    'label' => 'Запись через mos.ru',
                    'attribute' => 'is_mos_ru',
                    'value' => function ($visit) {
                        if ($visit->message) {
                            return "Да";
                        } else {
                            return '';
                        }
                    },
                ],
                [
                    'label' => 'Создан',
                    'attribute' => 'created_at',
                    'value' => 'created_at',
                    'filter' => \yii\jui\DatePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'created_at',
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
                    }')
                        ]
                    ]),
                ],

            ]
        ]) ?>
    </div>
</div>
