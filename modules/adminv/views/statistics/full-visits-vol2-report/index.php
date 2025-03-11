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
/** @var $visit_types array */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Детальный отчет по приемам';
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
                                'data' => $organizationsList,
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
                        <label class="control-label" style="text-align: left; ">Виды животных:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'spec_name',
                                'value' => \Yii::$app->request->get('spec_name'),
                                'data' => $speciesList,
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
                        Url::toRoute(['statistics/full-visits-vol2-report']),
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
                            'statistics/full-visits-vol2-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_organization' => \Yii::$app->request->get('id_organization', []),
                            'id_area' => \Yii::$app->request->get('id_area', []),
                            'id_district' => \Yii::$app->request->get('id_district', []),
                            'spec_name' => \Yii::$app->request->get('spec_name', []),
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
                <th></th>
                <th>Всего</th>
                <th>Завершено</th>
                <th>Отменено организацией</th>
                <th>Отменено владельцем</th>
                <th>Перенесено</th>
                <th>Перенесено организацией (только для mos.ru)</th>
                <th>Перенесено владельцем (только для mos.ru)</th>
            </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $currentDistId = null;
            $currentOrgId = null;

            $totalVisitsPerOrg = 0;
            $totalVisitsPerDist = 0;
            $totalVisitsPerArea = 0;
            $totalVisits = 0;

            $totalFinishedPerOrg = 0;
            $totalFinishedPerDist = 0;
            $totalFinishedPerArea = 0;
            $totalFinished = 0;

            $totalCancelledByOrgPerOrg = 0;
            $totalCancelledByOrgPerDist = 0;
            $totalCancelledByOrgPerArea = 0;
            $totalCancelledByOrg = 0;

            $totalCancelledByOwnerPerOrg = 0;
            $totalCancelledByOwnerPerDist = 0;
            $totalCancelledByOwnerPerArea = 0;
            $totalCancelledByOwner = 0;

            $totalTransferredPerOrg = 0;
            $totalTransferredPerDist = 0;
            $totalTransferredPerArea = 0;
            $totalTransferred = 0;

            $totalTransferredByOrgPerOrg = 0;
            $totalTransferredByOrgPerDist = 0;
            $totalTransferredByOrgPerArea = 0;
            $totalTransferredByOrg = 0;

            $totalTransferredByOwnerPerOrg = 0;
            $totalTransferredByOwnerPerDist = 0;
            $totalTransferredByOwnerPerArea = 0;
            $totalTransferredByOwner = 0;
            ?>
            <?php $data = $dataProvider->query ?>
            <?php foreach ($data as $datum): ?>
                <?php if($currentOrgId != $datum['id'] && $currentOrgId != null): ?>
                    <tr style="background: #c8c8c8">
                        <th>Итого по организации</th>
                        <th><?=$totalVisitsPerOrg?></th>
                        <th><?=$totalFinishedPerOrg?> </th>
                        <th><?=$totalCancelledByOrgPerOrg?> </th>
                        <th><?=$totalCancelledByOwnerPerOrg?> </th>
                        <th><?=$totalTransferredPerOrg?> </th>
                        <th><?=$totalTransferredByOrgPerOrg?> </th>
                        <th><?=$totalTransferredByOwnerPerOrg?> </th>
                    </tr>
                    <?php $currentOrgId = null;
                            $totalVisitsPerOrg = 0;
                            $totalFinishedPerOrg = 0;
                            $totalCancelledByOrgPerOrg = 0;
                            $totalCancelledByOwnerPerOrg = 0;
                            $totalTransferredPerOrg = 0;
                            $totalTransferredByOrgPerOrg = 0;
                            $totalTransferredByOwnerPerOrg = 0; ?>
                <?php endif; ?>
                <?php if($currentDistId != $datum['id_district'] && $currentDistId != null):?>
                    <tr style="background: #a0a0a0">
                        <th>Итого по району</th>
                        <th><?=$totalVisitsPerDist?></th>
                        <th><?=$totalFinishedPerDist?> </th>
                        <th><?=$totalCancelledByOrgPerDist?> </th>
                        <th><?=$totalCancelledByOwnerPerDist?> </th>
                        <th><?=$totalTransferredPerDist?> </th>
                        <th><?=$totalTransferredByOrgPerDist?> </th>
                        <th><?=$totalTransferredByOwnerPerDist?> </th>
                    </tr>
                    <?php
                    $totalVisitsPerDist = 0;
                    $totalFinishedPerDist = 0;
                    $totalCancelledByOrgPerDist = 0;
                    $totalCancelledByOwnerPerDist = 0;
                    $totalTransferredPerDist = 0;
                    $totalTransferredByOrgPerDist = 0;
                    $totalTransferredByOwnerPerDist = 0;
                    ?>
                <?php endif; ?>

                <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #808080">
                        <th>Итого по округу</th>
                        <th><?=$totalVisitsPerArea?></th>
                        <th><?=$totalFinishedPerArea?> </th>
                        <th><?=$totalCancelledByOrgPerArea?> </th>
                        <th><?=$totalCancelledByOwnerPerArea?> </th>
                        <th><?=$totalTransferredPerArea?> </th>
                        <th><?=$totalTransferredByOrgPerArea?> </th>
                        <th><?=$totalTransferredByOwnerPerArea?> </th>
                    </tr>
                    <?php
                    $totalVisitsPerArea = 0;
                    $totalFinishedPerArea = 0;
                    $totalCancelledByOrgPerArea = 0;
                    $totalCancelledByOwnerPerArea = 0;
                    $totalTransferredPerArea = 0;
                    $totalTransferredByOrgPerArea = 0;
                    $totalTransferredByOwnerPerArea = 0;
                    ?>
                <?php endif?>
                <?php
                if($currentAreaId != $datum['id_area']):
                    $currentAreaId = $datum['id_area'];
                    ?>
                    <tr>
                        <th colspan="8" style="background: #808080" ><?= $areasList[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <?php
                if($currentDistId != $datum['id_district']):
                    $currentDistId = $datum['id_district'];
                    ?>
                    <tr>
                        <th colspan="8" style="background: #a0a0a0" >Район <?=$districtsList[$currentDistId] ?? 'не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <?php
                if($currentOrgId != $datum['id']):
                    $currentOrgId = $datum['id']; ?>
                    <tr>
                        <th colspan="8" style="background: #c8c8c8">Организация <?=$organizationsList[$currentOrgId] ?? 'не указана' ?></th>
                    </tr>
                <?php endif ?>
                <tr>
                    <td>
                        <?= $datum['spec_name'] ?>
                    </td>
                    <td>
                        <?= $datum['total_visits'] ?>
                    </td>
                    <td>
                        <?= $datum['total_finished'] ?>
                    </td>
                    <td>
                        <?= $datum['cancelled_by_clinic'] ?>
                    </td>
                    <td>
                        <?= $datum['cancelled_by_owner'] ?>
                    </td>
                    <td>
                        <?= $datum['transferred_not_mosru'] ?>
                    </td>
                    <td>
                        <?= $datum['transferred_by_clinic_mosru'] ?>
                    </td>
                    <td>
                        <?= $datum['transferred_by_owner_mosru'] ?>
                    </td>
                </tr>
                <?php
                $totalVisitsPerOrg += $datum['total_visits'];
                $totalVisitsPerDist += $datum['total_visits'];
                $totalVisitsPerArea += $datum['total_visits'];
                $totalVisits += $datum['total_visits'];

                $totalFinishedPerOrg += $datum['total_finished'];
                $totalFinishedPerDist += $datum['total_finished'];
                $totalFinishedPerArea += $datum['total_finished'];
                $totalFinished += $datum['total_finished'];

                $totalCancelledByOrgPerOrg += $datum['cancelled_by_clinic'];
                $totalCancelledByOrgPerDist += $datum['cancelled_by_clinic'];
                $totalCancelledByOrgPerArea += $datum['cancelled_by_clinic'];
                $totalCancelledByOrg += $datum['cancelled_by_clinic'];

                $totalCancelledByOwnerPerOrg += $datum['cancelled_by_owner'];
                $totalCancelledByOwnerPerDist += $datum['cancelled_by_owner'];
                $totalCancelledByOwnerPerArea += $datum['cancelled_by_owner'];
                $totalCancelledByOwner += $datum['cancelled_by_owner'];

                $totalTransferredPerOrg += $datum['transferred_not_mosru'];
                $totalTransferredPerDist += $datum['transferred_not_mosru'];
                $totalTransferredPerArea += $datum['transferred_not_mosru'];
                $totalTransferred += $datum['transferred_not_mosru'];

                $totalTransferredByOrgPerOrg += $datum['transferred_by_clinic_mosru'];
                $totalTransferredByOrgPerDist += $datum['transferred_by_clinic_mosru'];
                $totalTransferredByOrgPerArea += $datum['transferred_by_clinic_mosru'];
                $totalTransferredByOrg += $datum['transferred_by_clinic_mosru'];

                $totalTransferredByOwnerPerOrg += $datum['transferred_by_owner_mosru'];
                $totalTransferredByOwnerPerDist += $datum['transferred_by_owner_mosru'];
                $totalTransferredByOwnerPerArea += $datum['transferred_by_owner_mosru'];
                $totalTransferredByOwner += $datum['transferred_by_owner_mosru'];
                ?>
            <?php endforeach;?>
            <tr style="background: #c8c8c8">
                <th>Итого по организации</th>
                <th><?=$totalVisitsPerOrg?></th>
                <th><?=$totalFinishedPerOrg?> </th>
                <th><?=$totalCancelledByOrgPerOrg?> </th>
                <th><?=$totalCancelledByOwnerPerOrg?> </th>
                <th><?=$totalTransferredPerOrg?> </th>
                <th><?=$totalTransferredByOrgPerOrg?> </th>
                <th><?=$totalTransferredByOwnerPerOrg?> </th>
            </tr>
            <tr style="background: #a0a0a0">
                <th>Итого по району</th>
                <th><?=$totalVisitsPerDist?></th>
                <th><?=$totalFinishedPerDist?> </th>
                <th><?=$totalCancelledByOrgPerDist?> </th>
                <th><?=$totalCancelledByOwnerPerDist?> </th>
                <th><?=$totalTransferredPerDist?> </th>
                <th><?=$totalTransferredByOrgPerDist?> </th>
                <th><?=$totalTransferredByOwnerPerDist?> </th>
            </tr>
            <tr style="background: #808080">
                <th>Итого по округу</th>
                <th><?=$totalVisitsPerArea?></th>
                <th><?=$totalFinishedPerArea?> </th>
                <th><?=$totalCancelledByOrgPerArea?> </th>
                <th><?=$totalCancelledByOwnerPerArea?> </th>
                <th><?=$totalTransferredPerArea?> </th>
                <th><?=$totalTransferredByOrgPerArea?> </th>
                <th><?=$totalTransferredByOwnerPerArea?> </th>
            </tr>
            <tr style="background: #666666">
                <th>Всего</th>
                <th><?=$totalVisits?></th>
                <th><?=$totalFinished?> </th>
                <th><?=$totalCancelledByOrg?> </th>
                <th><?=$totalCancelledByOwner?> </th>
                <th><?=$totalTransferred?> </th>
                <th><?=$totalTransferredByOrg?> </th>
                <th><?=$totalTransferredByOwner?> </th>
            </tr>
        </table>
    </div>
</div>
