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

$this->blocks['content-header'] = 'Отчет по владельцам с электронной почтой';
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
                                        'За день' => [
                                            new JsExpression('moment().startOf(\'day\')'),
                                            new JsExpression('moment()')
                                        ],
                                        'За месяц' => [
                                            new JsExpression('moment().startOf(\'month\')'),
                                            new JsExpression('moment()')
                                        ],
                                        'За квартал' => [
                                            new JsExpression('moment().startOf(\'quarter\')'),
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
                                'data' => $areasList,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions'=> [
                                    'nonSelectedText' => 'Не выбрано',
                                    'enableCaseInsensitiveFiltering' => true,
                                    'includeResetOption' => true,
                                    'resetText' => 'Сбросить',
                                    'maxHeight' => 450,
                                    'numberDisplayed' => 3,
                                    'nSelectedText' => 'выбрано'
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
                                'data' => $districtsList,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions'=> [
                                    'nonSelectedText' => 'Не выбрано',
                                    'enableCaseInsensitiveFiltering' => true,
                                    'includeResetOption' => true,
                                    'resetText' => 'Сбросить',
                                    'maxHeight' => 450,
                                    'numberDisplayed' => 3,
                                    'nSelectedText' => 'выбрано'
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
                        Url::toRoute(['statistics/email-report']),
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
                            'statistics/email-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_area' => \Yii::$app->request->get('id_area', []),
                            'id_district' => \Yii::$app->request->get('id_district', []),
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
            <tr>
                <th>№ п\п</th>
                <th>Административный округ</th>
                <th>Район</th>
                <th>Общее количество владельцев животных</th>
                <th>Количество владельцев с электронной почтой</th>
                <th>Количество владельцев с подтверждёнными телефонными номерами</th>
                <th>Количество владельцев с неподтверждёнными телефонными номерами</th>
                <th>Количество владельцев, зарегистрированных на портале mos.ru</th>
                <th>Количество владельцев, подписанных на E-mail рассылку</th>
                <th>Количество владельцев, подписанных на push и ЛК</th>
            </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $count = 1;

            $totalOwnersPerArea = 0;
            $totalOwners = 0;

            $totalEmailPerArea = 0;
            $totalEmail = 0;

            $totalConfirmedPerArea = 0;
            $totalConfirmed = 0;

            $totalUnconfirmedPerArea = 0;
            $totalUnconfirmed = 0;

            $totalRegMosruPerArea = 0;
            $totalRegMosru = 0;

            $totalEmailSpkPerArea = 0;
            $totalEmailSpk = 0;

            $totalPushSpkPerArea = 0;
            $totalPushSpk = 0;
            ?>
            <?php $data = $dataProvider->query ?>
            <?php foreach ($data as $datum): ?>
                <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #b5dcf2">
                        <th>Итого по округу</th>
                        <th></th>
                        <th></th>
                        <th><?=$totalOwnersPerArea?></th>
                        <th><?=$totalEmailPerArea?> </th>
                        <th><?=$totalConfirmedPerArea?> </th>
                        <th><?=$totalUnconfirmedPerArea?> </th>
                        <th><?=$totalRegMosruPerArea?> </th>
                        <th><?=$totalEmailSpkPerArea?> </th>
                        <th><?=$totalPushSpkPerArea?> </th>
                    </tr>
                    <?php
                        $totalOwnersPerArea = 0;
                        $totalEmailPerArea = 0;
                        $totalConfirmedPerArea = 0;
                        $totalUnconfirmedPerArea = 0;
                        $totalRegMosruPerArea = 0;
                        $totalEmailSpkPerArea = 0;
                        $totalPushSpkPerArea = 0;
                    ?>
                <?php endif?>
                <?php
                    if($currentAreaId != $datum['id_area']):
                    $currentAreaId = $datum['id_area'];
                    ?>
                <?php endif?>
                <tr>
                    <td>
                        <?= $count ?>
                    </td>
                    <td>
                        <?= $datum['area_name'] ??  'Округ не указан'?>
                    </td>
                    <td>
                        <?= $datum['dist_name'] ?? 'Район не указан'?>
                    </td>
                    <td>
                        <?= $datum['total_owners'] ?>
                    </td>
                    <td>
                        <?= $datum['total_email'] ?>
                    </td>
                    <td>
                        <?= $datum['total_confirmed'] ?>
                    </td>
                    <td>
                        <?= $datum['total_unconfirmed'] ?>
                    </td>
                    <td>
                        <?= $datum['total_reg_mosru'] ?>
                    </td>
                    <td>
                        <?= $datum['total_email_spk'] ?>
                    </td>
                    <td>
                        <?= $datum['total_push_spk'] ?>
                    </td>
                </tr>
                <?php
                    $count++;
                    $totalOwnersPerArea += $datum['total_owners'];
                    $totalEmailPerArea += $datum['total_email'];
                    $totalConfirmedPerArea += $datum['total_confirmed'];
                    $totalUnconfirmedPerArea += $datum['total_unconfirmed'];
                    $totalRegMosruPerArea += $datum['total_reg_mosru'];
                    $totalEmailSpkPerArea += $datum['total_email_spk'];
                    $totalPushSpkPerArea += $datum['total_push_spk'];

                    $totalOwners += $datum['total_owners'];
                    $totalEmail += $datum['total_email'];
                    $totalConfirmed += $datum['total_confirmed'];
                    $totalUnconfirmed += $datum['total_unconfirmed'];
                    $totalRegMosru += $datum['total_reg_mosru'];
                    $totalEmailSpk += $datum['total_email_spk'];
                    $totalPushSpk += $datum['total_push_spk'];
                ?>
            <?php endforeach; ?>
            <tr style="background: #b5dcf2">
                <th>Итого по округу</th>
                <th></th>
                <th></th>
                <th><?=$totalOwnersPerArea?></th>
                <th><?=$totalEmailPerArea?> </th>
                <th><?=$totalConfirmedPerArea?> </th>
                <th><?=$totalUnconfirmedPerArea?> </th>
                <th><?=$totalRegMosruPerArea?> </th>
                <th><?=$totalEmailSpkPerArea ?></th>
                <th><?=$totalPushSpkPerArea ?> </th>
            </tr>
            <tr style="background: #9ac9e2">
                <th>Всего</th>
                <th></th>
                <th></th>
                <th><?=$totalOwners?></th>
                <th><?=$totalEmail?> </th>
                <th><?=$totalConfirmed?> </th>
                <th><?=$totalUnconfirmed?> </th>
                <th><?=$totalRegMosru?> </th>
                <th><?=$totalEmailSpk ?></th>
                <th><?=$totalPushSpk ?> </th>
            </tr>
        </table>
    </div>
</div>
