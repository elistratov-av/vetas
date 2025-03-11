<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.12.18
 * Time: 16:43
 */

/** @var \yii\web\View $this */

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\admin\widgets\ExtendedGridView;
use yii\web\JsExpression;
use yii\web\View;

$this->blocks['content-header'] = 'Статистика mos.ru';
$this->registerCss('.nowrap {white-space: nowrap;}');
$js = <<<JS
$(function() {
  $("table").stickyTableHeaders();
});
JS;

$this->registerJs($js, View::POS_READY);
?>

<div class="box">
    <div class="box-body">
        <form method="get" style="margin-bottom: 20px;">
            <div class="row form-horizontal">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align: left; ">Период отчетности:</label>
                        <div class="input-group">
                            <div class="input-group-addon" style="width: 35px;">
                                <i class="fa fa-calendar"> </i>
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
                <div class="col-md-6" style="padding-right: 0;">
                    <?= Html::a(
                        'Сбросить',
                        Url::toRoute(['statistics/mos-ru']),
                        [
                            'class' => 'btn btn-default btn-sm'
                        ]
                    ) ?>

                    <?= Html::input('submit','submit', 'Показать', [
                        'class' => 'btn btn-primary btn-sm'
                    ]); ?>

                    <?= Html::a(
                        '<span class="glyphicon glyphicon-download"> </span> Сохранить XLS',
                        Url::toRoute([
                            'statistics/mos-ru/export',
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

<?=ExtendedGridView::widget([
    'dataProvider' => $dataProvider,
    'emptyText' => 'Не найдена статистика для указанной даты',
    'tableHeader' => '
            <tr style="background: white">
                <th colspan="3"> </th>
                <th colspan="2">Отменено</th>
                <th colspan="3">Записи для</th>
                <th colspan="3">Проведено приемов для</th>
            </tr>
            <tr style="background: white">
                <th>Организация</th>
                <th>Всего</th>
                <th>Перенесено</th>
                <td>владельцем</td>
                <td>организацией</td>
                <td>кошек</td>
                <td>собак</td>
                <td>иные</td>
                <td>кошек</td>
                <td>собак</td>
                <td>иные</td>
            </tr>',
    'columns' => [
        [
            'attribute' => 'organization.short_name',
            'contentOptions' => [
                'width' => '20%',
                'class' => 'nowrap'
            ],
        ],
        [
            'attribute' => 'total',
        ],
        [
            'attribute' => 'moved',
        ],
        [
            'attribute' => 'canceled_by_owner',
        ],
        [
            'attribute' => 'canceled_by_org',
        ],
        [
            'attribute' => 'cats_visits',
        ],
        [
            'attribute' => 'dogs_visits',
        ],
        [
            'attribute' => 'other_visits',
        ],
        [
            'attribute' => 'cats_finished_visits',
        ],
        [
            'attribute' => 'dogs_finished_visits',
        ],
        [
            'attribute' => 'other_finished_visits',
        ]
    ]
]);
?>
    </div>
</div>
