<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.05.19
 * Time: 16:07
 */

$this->blocks['content-header'] = 'Контроль выездной службы';
$this->registerCss('.nowrap {white-space: nowrap;}');

use kartik\daterange\DateRangePicker;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression; ?>

<div class="box">
    <div class="box-body">
        <form method="get" style="margin-bottom: 20px;" name="filter-form">
            <div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align: left; ">Период отчетности:</label>
                        <div class="input-group">
                            <div class="input-group-addon" style="width: 35px;">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <?= DateRangePicker::widget([
                                'name' => 'time_range',
                                'value' => Yii::$app->getRequest()->get('time_range', ''),
                                'attribute'=>'time_range',
                                'convertFormat'=>true,
                                'startAttribute'=>'from',
                                'endAttribute'=>'to',
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
                                    'startDate' => new JsExpression('moment().startOf(\'hour\')'),
                                    'endDate' => new JsExpression('moment().endOf(\'hour\')'),
                                    'ranges' => [
                                        'С начала месяца' => [
                                            new JsExpression('moment().startOf(\'month\')'),
                                            new JsExpression('moment()')
                                        ],
                                        'С начала года' => [
                                            new JsExpression('moment().startOf(\'year\')'),
                                            new JsExpression('moment()')
                                        ],
                                    ]
                                ],
                            ])?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6" style="padding-right: 0;">
                    <?= Html::a(
                        'Сбросить',
                        Url::toRoute(['statistics/ambulance-duty-report']),
                        [
                            'class' => 'btn btn-default btn-sm'
                        ]
                    ) ?>
                    <?= Html::input('submit','submit', 'Показать', [
                        'class' => 'btn btn-primary btn-sm'
                    ]); ?>
                    <?= Html::a(
                        '<span class="glyphicon glyphicon-download"></span> Сохранить XLS',
                        Url::toRoute([
                            'statistics/ambulance-duty-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                        ]),
                        [
                            'class' => 'btn btn-primary btn-sm'
                        ]
                    ) ?>
                </div>
            </div>
        </form>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'showFooter' => true,
    'columns' => [
        [
            'label' => 'Специалист',
            'attribute' => 'spec_name',
            'footer' => '<strong>Итого</strong>'
        ],
        [
            'label' => 'Количество принятых звонков',
            'attribute' => 'total_calls',
            'footer' => '<strong>' . $totalStatistic['total_calls'] . '</strong>'
        ],
        [
            'label' => 'Общее количество отказов',
            'attribute' => 'total_cancelled',
            'footer' => '<strong>' . $totalStatistic['total_cancelled'] . '</strong>'
        ],
        [
            'label' => 'Количество отказов владельцем',
            'attribute' => 'cancelled_by_owner',
            'footer' => '<strong>' . $totalStatistic['cancelled_by_owner'] . '</strong>'
        ],
        [
            'label' => 'Количество отказов специалистом',
            'attribute' => 'cancelled_by_org',
            'footer' => '<strong>' . $totalStatistic['cancelled_by_org'] . '</strong>'
        ],
        [
            'label' => 'Количество выездов',
            'attribute' => 'total_house_calls',
            'footer' => '<strong>' . $totalStatistic['total_house_calls'] . '</strong>'
        ],
        [
            'label' => 'Количество платных выездов',
            'attribute' => 'total_commercial',
            'footer' => '<strong>' . $totalStatistic['total_commercial'] . '</strong>'
        ],
        [
            'label' => 'Количество льготных выездов (незрячие)',
            'attribute' => 'total_free_for_blind',
            'footer' => '<strong>' . $totalStatistic['total_free_for_blind'] . '</strong>'
        ],
        [
            'label' => 'Количество льготных выездов (все остальные)',
            'attribute' => 'total_free_for_the_rest',
            'footer' => '<strong>' . $totalStatistic['total_free_for_the_rest'] . '</strong>'
        ]
    ]
])
?>
    </div>
</div>