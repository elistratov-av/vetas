<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.02.19
 * Time: 15:10
 */

use app\models\db\Areas;
use app\models\db\Districts;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/** @var $this \yii\web\View */
/** @var $data array */
/** @var $organizations array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет о нагрузке на ветеринарные учреждения и службы';
$this->registerCss('.nowrap {white-space: nowrap;}');

$areas = Areas::find()
    ->select('name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();
$districts = Districts::find()
    ->select('name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();

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
                                            'За 5 лет' => [
                                                new JsExpression('new Date(new Date().setFullYear(new Date().getFullYear() - 5))'),
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
                            <label class="control-label" style="text-align: left; ">Административный(е) округ(а):</label>
                            <div class="input-group">
                                <?= MultiSelect::widget([
                                    'name' => 'id_area',
                                    'value' => \Yii::$app->request->get('id_area'),
                                    'data' => $areas,
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
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" style="text-align: left; ">Район(ы):</label>
                            <div class="input-group">
                                <?= MultiSelect::widget([
                                    'name' => 'id_district',
                                    'value' => \Yii::$app->request->get('id_district'),
                                    'data' => $districts,
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
                            Url::toRoute(['statistics/clinics-duty-report']),
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
                                'statistics/clinics-duty-report/export',
                                'from' => Yii::$app->getRequest()->get('from', ''),
                                'to' => Yii::$app->getRequest()->get('to', ''),
                                'id_organization' => \Yii::$app->request->get('id_organization', []),
                                'id_area' => \Yii::$app->request->get('id_area', []),
                                'id_district' => \Yii::$app->request->get('id_district', [])
                            ]),
                            [
                                'class' => 'btn btn-primary btn-sm'
                            ]
                        ) ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background: white">
                <tr><th></th>
                    <th colspan="5">Принято обращений (записей на прием)</th>
                    <th>Проведено приемов</th>
                    <th>Оказано услуг</th>
                </tr>
                <tr>
                    <th></th>
                    <th>Записей всего</th>
                    <th>Mos.ru</th>
                    <th>Телефон</th>
                    <th>Живая очередь</th>
                    <th>Направление</th>
                </tr>
                </thead>
                <?php
                $currentOrgId = null;
                $currentDistId = null;
                $currentAreaId = null;
                $totalMosruPerDist = 0;
                $totalMosruPerArea = 0;
                $totalMosru = 0;
                $totalPhonePerDist = 0;
                $totalPhonePerArea = 0;
                $totalPhone = 0;
                $totalLiveQueuePerDist = 0;
                $totalLiveQueuePerArea = 0;
                $totalLiveQueue = 0;
                $totalAppointmentPerDist = 0;
                $totalAppointmentPerArea = 0;
                $totalAppointment = 0;
                $totalVisitsPerDist = 0;
                $totalVisitsPerArea = 0;
                $totalVisits = 0;
                $totalServicesPerDist = 0;
                $totalServicesPerArea = 0;
                $totalServices = 0 ?>
                <?php foreach ($data as $datum): ?>
                    <?php if($currentDistId != $datum['id_district'] && $currentDistId != null): ?>
                        <tr style="background: lightgray">
                            <th>Итого по району</th>
                            <th><?=$totalMosruPerDist+$totalPhonePerDist+$totalLiveQueuePerDist+$totalAppointmentPerDist?></th>
                            <th><?=$totalMosruPerDist?> </th>
                            <th><?=$totalPhonePerDist?> </th>
                            <th><?=$totalLiveQueuePerDist?> </th>
                            <th><?=$totalAppointmentPerDist?> </th>
                            <th><?=$totalVisitsPerDist?> </th>
                            <th><?=$totalServicesPerDist?> </th>
                        </tr>
                        <?php $totalMosruPerDist = 0;
                        $totalPhonePerDist = 0;
                        $totalLiveQueuePerDist = 0;
                        $totalAppointmentPerDist = 0;
                        $totalVisitsPerDist = 0;
                        $totalServicesPerDist = 0 ?>
                    <?php endif?>
                    <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                        <tr style="background: darkgray">
                            <th>Итого по округу</th>
                            <th><?=$totalMosruPerArea+$totalPhonePerArea+$totalLiveQueuePerArea+$totalAppointmentPerArea?> </th>
                            <th><?=$totalMosruPerArea?> </th>
                            <th><?=$totalPhonePerArea?> </th>
                            <th><?=$totalLiveQueuePerArea?> </th>
                            <th><?=$totalAppointmentPerArea?> </th>
                            <th><?=$totalVisitsPerArea?> </th>
                            <th><?=$totalServicesPerArea?> </th>
                        </tr>
                        <?php $totalMosruPerArea = 0;
                        $totalPhonePerArea = 0;
                        $totalLiveQueuePerArea = 0;
                        $totalAppointmentPerArea = 0;
                        $totalVisitsPerArea = 0;
                        $totalServicesPerArea = 0 ?>
                    <?php endif?>
                    <?php
                    if($currentAreaId != $datum['id_area']):
                        $currentAreaId = $datum['id_area'];
                        $currentOrgId = null;
                        $totalMosruPerArea = 0;
                        $totalPhonePerArea = 0;
                        $totalLiveQueuePerArea = 0;
                        $totalAppointmentPerArea = 0;
                        $totalVisitsPerArea = 0;
                        $totalServicesPerArea = 0 ?>
                        <tr>
                            <th colspan="8" style="background: darkgray"><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                        </tr>
                    <?php endif ?>
                    <?php
                    if($currentDistId != $datum['id_district']):
                        $currentDistId = $datum['id_district'] ?>
                        <tr>
                            <th colspan="8" style="background: lightgray">Район <?=$districts[$currentDistId] ?? 'не указан' ?></th>
                        </tr>
                    <?php endif ?>
                    <?php
                    if($currentOrgId != $datum['id']):
                        $currentOrgId = $datum['id'] ?>
                        <tr>
                            <th>Организация <?= $organizations[$currentOrgId] ?></th>
                            <td><?=$datum['mosruVisitsQuery']+$datum['phoneVisitsQuery']+$datum['liveQueueVisitsQuery']+$datum['appointmentVisitsQuery']?></td>
                            <td><?=$datum['mosruVisitsQuery']?></td>
                            <td><?=$datum['phoneVisitsQuery']?> </td>
                            <td><?=$datum['liveQueueVisitsQuery']?> </td>
                            <td><?=$datum['appointmentVisitsQuery']?> </td>
                            <td><?=$datum['totalVisitsQuery']?> </td>
                            <td><?=$datum['servicesCounter']?>
                        </tr>

                    <?php endif ?>
                    <?php
                    $totalMosruPerDist += $datum['mosruVisitsQuery'];
                    $totalMosruPerArea += $datum['mosruVisitsQuery'];
                    $totalMosru += $datum['mosruVisitsQuery'];
                    $totalPhonePerDist += $datum['phoneVisitsQuery'];
                    $totalPhonePerArea += $datum['phoneVisitsQuery'];
                    $totalPhone += $datum['phoneVisitsQuery'];
                    $totalLiveQueuePerDist += $datum['liveQueueVisitsQuery'];
                    $totalLiveQueuePerArea += $datum['liveQueueVisitsQuery'];
                    $totalLiveQueue += $datum['liveQueueVisitsQuery'];
                    $totalAppointmentPerDist += $datum['appointmentVisitsQuery'];
                    $totalAppointmentPerArea += $datum['appointmentVisitsQuery'];
                    $totalAppointment += $datum['appointmentVisitsQuery'];
                    $totalVisitsPerDist += $datum['totalVisitsQuery'];
                    $totalVisitsPerArea += $datum['totalVisitsQuery'];
                    $totalVisits += $datum['totalVisitsQuery'];
                    $totalServicesPerDist += $datum['servicesCounter'];
                    $totalServicesPerArea += $datum['servicesCounter'];
                    $totalServices += $datum['servicesCounter'] ?>
                <?php endforeach;?>
                <tr style="background: lightgray">
                    <th>Итого по району</th>
                    <th><?=$totalMosruPerDist+$totalPhonePerDist+$totalLiveQueuePerDist+$totalAppointmentPerDist?></th>
                    <th><?=$totalMosruPerDist?> </th>
                    <th><?=$totalPhonePerDist?> </th>
                    <th><?=$totalLiveQueuePerDist?> </th>
                    <th><?=$totalAppointmentPerDist?> </th>
                    <th><?=$totalVisitsPerDist?> </th>
                    <th><?=$totalServicesPerDist?> </th>
                </tr>
                <tr style="background: darkgray">
                    <th>Итого по округу</th>
                    <th><?=$totalMosruPerArea+$totalPhonePerArea+$totalLiveQueuePerArea+$totalAppointmentPerArea?> </th>
                    <th><?=$totalMosruPerArea?> </th>
                    <th><?=$totalPhonePerArea?> </th>
                    <th><?=$totalLiveQueuePerArea?> </th>
                    <th><?=$totalAppointmentPerArea?> </th>
                    <th><?=$totalVisitsPerArea?> </th>
                    <th><?=$totalServicesPerArea?> </th>
                </tr>
                <tr style="background: darkgray">
                    <th>Всего </th>
                    <th><?=$totalMosru+$totalPhone+$totalLiveQueue+$totalAppointment?> </th>
                    <th><?=$totalMosru?> </th>
                    <th><?=$totalPhone?> </th>
                    <th><?=$totalLiveQueue?> </th>
                    <th><?=$totalAppointment?> </th>
                    <th><?=$totalVisits?> </th>
                    <th><?=$totalServices?> </th>
                </tr>
            </table>
        </div>
    </div>
