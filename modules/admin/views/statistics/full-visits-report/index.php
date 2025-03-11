<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.06.19
 * Time: 16:12
 */

use app\models\db\Areas;
use app\models\db\Districts;
use app\modules\admin\models\Organization;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$this->blocks['content-header'] = 'Общий отчет по приемам';
$this->registerCss('.nowrap {white-space: nowrap;}');
$organizations = Organization::find()
    ->select('short_name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();
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
                        Url::toRoute(['statistics/full-visits-report']),
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
                            'statistics/full-visits-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_organization' => \Yii::$app->request->get('id_organization', []),
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
        <table class="table table-bordered table-hover">
            <thead style="background: white">
                <tr>
                    <th colspan="16" style="text-align: center">Обработано обращений (записей на прием)</th>
                </tr>
                <tr>
                    <th></th>
                    <th colspan="4" style="text-align: center">mos.ru</th>
                    <th colspan="4" style="text-align: center">Телефон</th>
                    <th colspan="4" style="text-align: center">Живая очередь</th>
                    <th colspan="3" style="text-align: center">Направление</th>
                </tr>
                <tr>
                    <th></th>
                    <th>Создано</th>
                    <th>Отменено</th>
                    <th>Перенесено</th>
                    <th>Завершено</th>
                    <th>Создано</th>
                    <th>Отменено</th>
                    <th>Перенесено</th>
                    <th>Завершено</th>
                    <th>Создано</th>
                    <th>Отменено</th>
                    <th>Перенесено</th>
                    <th>Завершено</th>
                    <th>Создано</th>
                    <th>Отменено</th>
                    <th>Завершено</th>
                </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $currentDistId = null;
            $totalMosruCreatedPerDist = 0;
            $totalMosruCancelledPerDist = 0;
            $totalMosruTransferedPerDist = 0;
            $totalMosruFinishedPerDist = 0;
            $totalMosruCreatedPerArea = 0;
            $totalMosruCancelledPerArea = 0;
            $totalMosruTransferedPerArea = 0;
            $totalMosruFinishedPerArea = 0;
            $totalMosruCreated = 0;
            $totalMosruCancelled = 0;
            $totalMosruTransfered = 0;
            $totalMosruFinished = 0;

            $totalPhoneCreatedPerDist = 0;
            $totalPhoneCancelledPerDist = 0;
            $totalPhoneTransferedPerDist = 0;
            $totalPhoneFinishedPerDist = 0;
            $totalPhoneCreatedPerArea = 0;
            $totalPhoneCancelledPerArea = 0;
            $totalPhoneTransferedPerArea = 0;
            $totalPhoneFinishedPerArea = 0;
            $totalPhoneCreated = 0;
            $totalPhoneCancelled = 0;
            $totalPhoneTransfered = 0;
            $totalPhoneFinished = 0;

            $totalLqCreatedPerDist = 0;
            $totalLqCancelledPerDist = 0;
            $totalLqTransferedPerDist = 0;
            $totalLqFinishedPerDist = 0;
            $totalLqCreatedPerArea = 0;
            $totalLqCancelledPerArea = 0;
            $totalLqTransferedPerArea = 0;
            $totalLqFinishedPerArea = 0;
            $totalLqCreated = 0;
            $totalLqCancelled = 0;
            $totalLqTransfered = 0;
            $totalLqFinished = 0;

            $totalWdCreatedPerDist = 0;
            $totalWdCancelledPerDist = 0;
            $totalWdFinishedPerDist = 0;
            $totalWdCreatedPerArea = 0;
            $totalWdCancelledPerArea = 0;
            $totalWdFinishedPerArea = 0;
            $totalWdCreated = 0;
            $totalWdCancelled = 0;
            $totalWdFinished = 0;
            ?>
            <?foreach ($data as $datum): ?>
                <?php if($currentDistId != $datum['id_district'] && $currentDistId != null):?>
                    <tr style="background: #c8c8c8">
                        <th>Итого по району</th>
                        <th><?=$totalMosruCreatedPerDist?> </th>
                        <th><?=$totalMosruCancelledPerDist?> </th>
                        <th><?=$totalMosruTransferedPerDist?> </th>
                        <th><?=$totalMosruFinishedPerDist?> </th>
                        <th><?=$totalPhoneCreatedPerDist?> </th>
                        <th><?=$totalPhoneCancelledPerDist?> </th>
                        <th><?=$totalPhoneTransferedPerDist?> </th>
                        <th><?=$totalPhoneFinishedPerDist?> </th>
                        <th><?=$totalLqCreatedPerDist?> </th>
                        <th><?=$totalLqCancelledPerDist?> </th>
                        <th><?=$totalLqTransferedPerDist?> </th>
                        <th><?=$totalLqFinishedPerDist?> </th>
                        <th><?=$totalWdCreatedPerDist?> </th>
                        <th><?=$totalWdCancelledPerDist?> </th>
                        <th><?=$totalWdFinishedPerDist?> </th>
                    </tr>
                    <?php
                    $serviceNamesPerDist = [];
                    $totalMosruCreatedPerDist = 0;
                    $totalMosruCancelledPerDist = 0;
                    $totalMosruTransferedPerDist = 0;
                    $totalMosruFinishedPerDist = 0;
                    $totalPhoneCreatedPerDist = 0;
                    $totalPhoneCancelledPerDist = 0;
                    $totalPhoneTransferedPerDist = 0;
                    $totalPhoneFinishedPerDist = 0;
                    $totalLqCreatedPerDist = 0;
                    $totalLqCancelledPerDist = 0;
                    $totalLqTransferedPerDist = 0;
                    $totalLqFinishedPerDist = 0;
                    $totalWdCreatedPerDist = 0;
                    $totalWdCancelledPerDist = 0;
                    $totalWdFinishedPerDist = 0;
                    ?>
                <?php endif; ?>

                <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #a0a0a0">
                        <th>Итого по округу</th>
                        <th><?=$totalMosruCreatedPerArea?> </th>
                        <th><?=$totalMosruCancelledPerArea?> </th>
                        <th><?=$totalMosruTransferedPerArea?> </th>
                        <th><?=$totalMosruFinishedPerArea?> </th>
                        <th><?=$totalPhoneCreatedPerArea?> </th>
                        <th><?=$totalPhoneCancelledPerArea?> </th>
                        <th><?=$totalPhoneTransferedPerArea?> </th>
                        <th><?=$totalPhoneFinishedPerArea?> </th>
                        <th><?=$totalLqCreatedPerArea?> </th>
                        <th><?=$totalLqCancelledPerArea?> </th>
                        <th><?=$totalLqTransferedPerArea?> </th>
                        <th><?=$totalLqFinishedPerArea?> </th>
                        <th><?=$totalWdCreatedPerArea?> </th>
                        <th><?=$totalWdCancelledPerArea?> </th>
                        <th><?=$totalWdFinishedPerArea?> </th>
                    </tr>
                    <?php
                    $totalMosruCreatedPerArea = 0;
                    $totalMosruCancelledPerArea = 0;
                    $totalMosruTransferedPerArea = 0;
                    $totalMosruFinishedPerArea = 0;
                    $totalPhoneCreatedPerArea = 0;
                    $totalPhoneCancelledPerArea = 0;
                    $totalPhoneTransferedPerArea = 0;
                    $totalPhoneFinishedPerArea = 0;
                    $totalLqCreatedPerArea = 0;
                    $totalLqCancelledPerArea = 0;
                    $totalLqTransferedPerArea = 0;
                    $totalLqFinishedPerArea = 0;
                    $totalWdCreatedPerArea = 0;
                    $totalWdCancelledPerArea = 0;
                    $totalWdFinishedPerArea = 0;
                    ?>
                <?php endif?>
                <?php
                if($currentAreaId != $datum['id_area']):
                    $currentAreaId = $datum['id_area'];
                    ?>
                    <tr>
                        <th colspan="16" style="background: #a0a0a0" ><?= $areaNames[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <?php
                if($currentDistId != $datum['id_district']):
                    $currentDistId = $datum['id_district'];
                    ?>
                    <tr>
                        <th colspan="16" style="background: #c8c8c8" >Район <?=$distNames[$currentDistId] ?? 'не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <?= $datum['short_name'] ?>
                    </td>
                    <td>
                        <?= $datum['mos_ru_created'] ?>
                    </td>
                     <td>
                        <?= $datum['mos_ru_cancelled'] ?>
                    </td>
                     <td>
                        <?= $datum['mos_ru_transfered'] ?>
                    </td>
                     <td>
                        <?= $datum['mos_ru_finished'] ?>
                    </td>
                     <td>
                        <?= $datum['phone_created'] ?>
                    </td>
                     <td>
                        <?= $datum['phone_cancelled'] ?>
                    </td>
                     <td>
                        <?= $datum['phone_transfered'] ?>
                    </td>
                     <td>
                        <?= $datum['phone_finished'] ?>
                    </td>
                     <td>
                        <?= $datum['lq_created'] ?>
                    </td>
                     <td>
                        <?= $datum['lq_cancelled'] ?>
                    </td>
                     <td>
                        <?= $datum['lq_transfered'] ?>
                    </td>
                     <td>
                        <?= $datum['lq_finished'] ?>
                    </td>
                     <td>
                        <?= $datum['wd_created'] ?>
                    </td>
                     <td>
                        <?= $datum['wd_cancelled'] ?>
                    </td>
                     <td>
                        <?= $datum['wd_finished'] ?>
                    </td>
                </tr>

                <?php

                $totalMosruCreatedPerDist += $datum['mos_ru_created'];
                $totalMosruCancelledPerDist += $datum['mos_ru_cancelled'];
                $totalMosruTransferedPerDist += $datum['mos_ru_transfered'];
                $totalMosruFinishedPerDist += $datum['mos_ru_finished'];
                $totalMosruCreatedPerArea += $datum['mos_ru_created'];
                $totalMosruCancelledPerArea += $datum['mos_ru_cancelled'];
                $totalMosruTransferedPerArea += $datum['mos_ru_transfered'];
                $totalMosruFinishedPerArea += $datum['mos_ru_finished'];
                $totalMosruCreated += $datum['mos_ru_created'];
                $totalMosruCancelled += $datum['mos_ru_cancelled'];
                $totalMosruTransfered += $datum['mos_ru_transfered'];
                $totalMosruFinished += $datum['mos_ru_finished'];

                $totalPhoneCreatedPerDist += $datum['phone_created'];
                $totalPhoneCancelledPerDist += $datum['phone_cancelled'];
                $totalPhoneTransferedPerDist += $datum['phone_transfered'];
                $totalPhoneFinishedPerDist += $datum['phone_finished'];
                $totalPhoneCreatedPerArea += $datum['phone_created'];
                $totalPhoneCancelledPerArea += $datum['phone_cancelled'];
                $totalPhoneTransferedPerArea += $datum['phone_transfered'];
                $totalPhoneFinishedPerArea += $datum['phone_finished'];
                $totalPhoneCreated += $datum['phone_created'];
                $totalPhoneCancelled += $datum['phone_cancelled'];
                $totalPhoneTransfered += $datum['phone_transfered'];
                $totalPhoneFinished += $datum['phone_finished'];

                $totalLqCreatedPerDist += $datum['lq_created'];
                $totalLqCancelledPerDist += $datum['lq_cancelled'];
                $totalLqTransferedPerDist += $datum['lq_transfered'];
                $totalLqFinishedPerDist += $datum['lq_finished'];
                $totalLqCreatedPerArea += $datum['lq_created'];
                $totalLqCancelledPerArea += $datum['lq_cancelled'];
                $totalLqTransferedPerArea += $datum['lq_transfered'];
                $totalLqFinishedPerArea += $datum['lq_finished'];
                $totalLqCreated += $datum['lq_created'];
                $totalLqCancelled += $datum['lq_cancelled'];
                $totalLqTransfered += $datum['lq_transfered'];
                $totalLqFinished += $datum['lq_finished'];

                $totalWdCreatedPerDist += $datum['wd_created'];
                $totalWdCancelledPerDist += $datum['wd_cancelled'];
                $totalWdFinishedPerDist += $datum['wd_finished'];
                $totalWdCreatedPerArea += $datum['wd_created'];
                $totalWdCancelledPerArea += $datum['wd_cancelled'];
                $totalWdFinishedPerArea += $datum['wd_finished'];
                $totalWdCreated += $datum['wd_created'];
                $totalWdCancelled += $datum['wd_cancelled'];
                $totalWdFinished += $datum['wd_finished'];
                ?>
            <?endforeach?>
                <tr style="background: #c8c8c8">
                    <th>Итого по району</th>
                    <th><?=$totalMosruCreatedPerDist?> </th>
                    <th><?=$totalMosruCancelledPerDist?> </th>
                    <th><?=$totalMosruTransferedPerDist?> </th>
                    <th><?=$totalMosruFinishedPerDist?> </th>
                    <th><?=$totalPhoneCreatedPerDist?> </th>
                    <th><?=$totalPhoneCancelledPerDist?> </th>
                    <th><?=$totalPhoneTransferedPerDist?> </th>
                    <th><?=$totalPhoneFinishedPerDist?> </th>
                    <th><?=$totalLqCreatedPerDist?> </th>
                    <th><?=$totalLqCancelledPerDist?> </th>
                    <th><?=$totalLqTransferedPerDist?> </th>
                    <th><?=$totalLqFinishedPerDist?> </th>
                    <th><?=$totalWdCreatedPerDist?> </th>
                    <th><?=$totalWdCancelledPerDist?> </th>
                    <th><?=$totalWdFinishedPerDist?> </th>
                </tr>
                <tr style="background: #a0a0a0">
                    <th>Итого по округу</th>
                    <th><?=$totalMosruCreatedPerArea?> </th>
                    <th><?=$totalMosruCancelledPerArea?> </th>
                    <th><?=$totalMosruTransferedPerArea?> </th>
                    <th><?=$totalMosruFinishedPerArea?> </th>
                    <th><?=$totalPhoneCreatedPerArea?> </th>
                    <th><?=$totalPhoneCancelledPerArea?> </th>
                    <th><?=$totalPhoneTransferedPerArea?> </th>
                    <th><?=$totalPhoneFinishedPerArea?> </th>
                    <th><?=$totalLqCreatedPerArea?> </th>
                    <th><?=$totalLqCancelledPerArea?> </th>
                    <th><?=$totalLqTransferedPerArea?> </th>
                    <th><?=$totalLqFinishedPerArea?> </th>
                    <th><?=$totalWdCreatedPerArea?> </th>
                    <th><?=$totalWdCancelledPerArea?> </th>
                    <th><?=$totalWdFinishedPerArea?> </th>
                </tr>
                <tr style="background: #808080">
                    <th>Всего</th>
                    <th><?=$totalMosruCreated?> </th>
                    <th><?=$totalMosruCancelled?> </th>
                    <th><?=$totalMosruTransfered?> </th>
                    <th><?=$totalMosruFinished?> </th>
                    <th><?=$totalPhoneCreated?> </th>
                    <th><?=$totalPhoneCancelled?> </th>
                    <th><?=$totalPhoneTransfered?> </th>
                    <th><?=$totalPhoneFinished?> </th>
                    <th><?=$totalLqCreated?> </th>
                    <th><?=$totalLqCancelled?> </th>
                    <th><?=$totalLqTransfered?> </th>
                    <th><?=$totalLqFinished?> </th>
                    <th><?=$totalWdCreated?> </th>
                    <th><?=$totalWdCancelled?> </th>
                    <th><?=$totalWdFinished?> </th>
                </tr>
        </table>
    </div>
</div>