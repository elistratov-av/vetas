<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 02.04.19
 * Time: 14:45
 * @var $from
 * @var $to
 * @var $avg
 */

use kartik\daterange\DateRangePicker;
use miloschuman\highcharts\Highcharts;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var $this \yii\web\View */
/** @var $data array */
/** @var $organizations array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'График пиковых часов загруженности в виде гистограммы';
$this->registerCss('.nowrap {white-space: nowrap;}');
?>
    <div class="box">
    <div class="box-body">
    <form method="get" style="margin-bottom: 20px;">
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
                            'value' => $time_range,
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
                                    'За месяц' => [
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
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" style="text-align: left; ">В организации(ях):</label>
                    <div class="input-group">
                        <?= MultiSelect::widget([
                            'name' => 'id_organization',
                            'value' => \Yii::$app->request->get('id_organization'),
                            'data' => $organizations,
                            'options' => [
                                'multiple' => true,
                            ],
                            'clientOptions'=> [
                                'nonSelectedText' => 'Не выбрано',
                                'enableCaseInsensitiveFiltering' => true,
                                'includeResetOption' => true,
                                'resetText' => 'Сбросить',
                                'maxHeight' => 450,
                            ]
                        ])?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6" style="padding-right: 0;">
                <?= Html::a(
                    'Сбросить',
                    Url::toRoute(['statistics/rush-hours-report']),
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
                        'statistics/rush-hours-report/export',
                        'from' => Yii::$app->getRequest()->get('from', ''),
                        'to' => Yii::$app->getRequest()->get('to', ''),
                        'id_organization' => \Yii::$app->request->get('id_organization', []),
                    ]),
                    [
                        'class' => 'btn btn-primary btn-sm'
                    ]
                ) ?>
            </div>
        </div>
    </form>
<?= Highcharts::widget([
    'scripts' => [
        'modules/exporting',
        'themes/grid-light',
    ],
    'options' => [
        'chart' => [
            'type' => 'column'
        ],
        'title' => [
            'text' => 'Среднее число обращений по времени суток за период с ' . $from . ' по ' . $to,
        ],
        'xAxis' => [
            'categories' => array_keys($avg),
            'title' => ['text' => 'Время'],
        ],
        'yAxis' => [
            'min' => 0,
            'title' => ['text' => 'Обращения'],
        ],
        'legend' => [
            'enabled' => false,
        ],

        'series' => [
            [
                'type' => 'column',
                'name' => 'Обращения',
                'data' => $avg,
            ],
        ],
    ]
]);
?>
