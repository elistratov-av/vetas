<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.07.19
 * Time: 11:03
 * @var $areasList
 * @var $districtsList
 * @var $organizationsList
 * @var $speciesList
 */

use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/** @var $this \yii\web\View */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Сводный отчет по уведомлениям';
$this->registerCss('.nowrap {white-space: nowrap;}');

$js = <<<JS
$(function() {
  $("table").stickyTableHeaders();
  // https://github.com/jmosbech/StickyTableHeaders/issues/134
  // https://github.com/jmosbech/StickyTableHeaders/pull/154/files
  // https://github.com/jmosbech/StickyTableHeaders#trigger-an-update-manually
  $('.table-responsive').on('scroll', function () {
      $(window).trigger('resize.stickyTableHeaders');
  });
});
JS;
$this->registerJs($js, View::POS_READY);

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
                                        'За 5 лет' => [
                                            new JsExpression('new Date(new Date().setFullYear(new Date().getFullYear() - 5))'),
                                            new JsExpression('moment()')
                                        ],
                                    ]
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" style="text-align: left; ">Виды животных:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'spec_name',
                                'value' => Yii::$app->request->get('spec_name'),
                                'data' => $speciesList,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions' => [
                                    'nonSelectedText' => 'Не выбрано',
                                    'enableCaseInsensitiveFiltering' => true,
                                    'includeResetOption' => true,
                                    'resetText' => 'Сбросить',
                                    'maxHeight' => 450,
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" style="text-align: left; ">Владельцы:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'id_owner',
                                'value' => Yii::$app->request->get('id_owner'),
                                'data' => $ownersList,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions' => [
                                    'nonSelectedText' => 'Не выбрано',
                                    'enableCaseInsensitiveFiltering' => true,
                                    'includeResetOption' => true,
                                    'resetText' => 'Сбросить',
                                    'maxHeight' => 450,
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" style="text-align: left; ">Отправитель:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'senders',
                                'value' => Yii::$app->request->get('senders'),
                                'data' => $sendersList,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions' => [
                                    'nonSelectedText' => 'Не выбрано',
                                    'enableCaseInsensitiveFiltering' => true,
                                    'includeResetOption' => true,
                                    'resetText' => 'Сбросить',
                                    'maxHeight' => 450,
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6" style="padding-right: 0;">
                    <?= Html::a(
                        'Сбросить',
                        Url::toRoute(['statistics/notifications-report']),
                        [
                            'class' => 'btn btn-default btn-sm'
                        ]
                    ) ?>
                    <?= Html::input('submit', 'submit', 'Показать', [
                        'class' => 'btn btn-primary btn-sm'
                    ]); ?>
                    <?= Html::a(
                        '<span class="glyphicon glyphicon-download"></span> Сохранить XLS',
                        Url::toRoute([
                            'statistics/notifications-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'spec_name' => \Yii::$app->request->get('spec_name', []),
                            'id_owner' => \Yii::$app->request->get('id_owner', []),
                            'senders' => \Yii::$app->request->get('senders', []),
                        ]),
                        [
                            'class' => 'btn btn-primary btn-sm'
                        ]
                    ) ?>
                </div>
            </div>
        </form>
    </div>
    <?php $data = $dataProvider->query[0] ?>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-hover">
            <tr>
                <th rowspan="3" style="width: 25%; vertical-align: middle">Первое уведомление</th>
                <th style="width: 30%">о вакцинации</th>
                <td>
                    <?= $data['init_vacc'] ?>
                </td>
            </tr>
            <tr>
                <th>об идентификации</th>
                <td>
                    <?= $data['init_ident'] ?>
                </td>
            </tr>
            <tr>
                <th>о вакцинации и идентификации</th>
                <td>
                    <?= $data['init_vacc_and_ident'] ?>
                </td>
            </tr>
            <tr>
                <th rowspan="2" style="width: 25%; vertical-align: middle">Напоминание о вакцинации</th>
                <th style="width: 30%">бешенство</th>
                <td>
                    <?= $data['rabies'] ?>
                </td>
            </tr>
            <tr>
                <th>лептоспироз</th>
                <td>
                    <?= $data['lepto'] ?>
                </td>
            </tr>
            <tr>
                <th colspan="2">Напоминание об идентификации</th>
                <td>
                    <?= $data['ident'] ?>
                </td>
            </tr>
            <tr>
                <th colspan="2">Уведомление о проведении противоэпизоотических мероприятий на местности</th>
                <td>
                    <?= $data['notif_qua'] ?>
                </td>
            </tr>
            <tr>
                <th colspan="2">Уведомление о найденном/отловленном владельческом животном</th>
                <td>
                    <?= $data['found'] ?>
                </td>
            </tr>
            <tr>
                <th colspan="2">Уведомления о готовности результатов исследований</th>
                <td>
                    <?= $data['research'] ?>
                </td>
            </tr>
            <tr>
                <th rowspan="6" style="vertical-align: middle">Нарушение</th>
                <th>идентификации</th>
                <td>
                    <?= $data['no_ident'] ?>
                </td>
            </tr>
            <tr>
                <th>вакцинации</th>
                <td>
                    <?= $data['no_vacc'] ?>
                </td>
            </tr>
        </table>
    </div>
</div>
