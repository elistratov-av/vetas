<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.03.19
 * Time: 14:23
 */

use app\models\db\Areas;
use app\models\db\Users;
use app\modules\admin\models\Organization;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$this->blocks['content-header'] = 'Отчет по работе сотрудников';
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
$specialists = Users::find()
    ->select('fullname')
    ->orderBy(['fullname' => SORT_ASC])
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
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" style="text-align: left; ">Специалисты:</label>
                            <div class="input-group">
                                <?= MultiSelect::widget([
                                    'name' => 'id',
                                    'value' => \Yii::$app->request->get('id'),
                                    'data' => $specialists,
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
                            Url::toRoute(['statistics/employees-report']),
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
                                'statistics/employees-report/export',
                                'from' => \Yii::$app->request->get('from'),
                                'to' => \Yii::$app->request->get('to'),
                                'id_organization' => \Yii::$app->request->get('id_organization', []),
                                'id_area' => \Yii::$app->request->get('id_area', []),
                                'id' => \Yii::$app->request->get('id', []),
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
                        <th>ФИО врача</th>
                        <th>Приемы (количество)</th>
                        <th>Услуги (количество)</th>
                        <th>Стоимость приемов (Общая)</th>
                        <th>Безвозмездные услуги</th>
                    </tr>
                </thead>
                <?php
                $currentOrgId = null;
                $currentAreaId = null;
                $totalPerOrg = 0;
                $specNamesPerArea = [];
                $specNames = [];
                $totalVisitsPerOrg = 0;
                $totalVisitsPerArea = 0;
                $totalVisits = 0;
                $totalServicesPerOrg = 0;
                $totalServicesPerArea = 0;
                $totalServices = 0;
                $totalSumAmountPerOrg = 0;
                $totalSumAmountPerArea = 0;
                $totalSumAmount = 0;
                $totalFreeServicesPerOrg = 0;
                $totalFreeServicesPerArea = 0;
                $totalFreeServices = 0 ?>
            <?foreach ($data as $datum): ?>
                <?php if($currentOrgId != $datum['id_organization'] && $currentOrgId != null): ?>
                    <tr style="background: lightgrey">
                        <th>Итого по организации <?= $totalPerOrg?></th>
                        <th><?=$totalVisitsPerOrg?> </th>
                        <th><?=$totalServicesPerOrg?> </th>
                        <th><?=$totalSumAmountPerOrg?> </th>
                        <th><?=$totalFreeServicesPerOrg?> </th>
                    </tr>
                    <?php $totalPerOrg = 0;
                    $totalVisitsPerOrg = 0;
                    $totalServicesPerOrg = 0;
                    $totalSumAmountPerOrg = 0;
                    $totalFreeServicesPerOrg = 0?>
                <?php endif; ?>
                <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: darkgray">
                        <th>Итого по округу <?=count(array_unique($specNamesPerArea))?></th>
                        <th><?=$totalVisitsPerArea?> </th>
                        <th><?=$totalServicesPerArea?> </th>
                        <th><?=$totalSumAmountPerArea?> </th>
                        <th><?=$totalFreeServicesPerArea?> </th>
                    </tr>
                    <?php $specNamesPerArea = [];
                        $totalServicesPerArea = 0;
                        $totalSumAmountPerArea = 0;
                        $totalVisitsPerArea = 0;
                        $totalFreeServicesPerArea = 0 ?>
                <?php endif?>
                <?php
                if($currentAreaId != $datum['id_area']):
                    $currentAreaId = $datum['id_area'];
                    $currentOrgId = null;
                    $totalServicesPerArea = 0;
                    $totalSumAmountPerArea = 0;
                    $totalVisitsPerArea = 0;
                    $totalFreeServicesPerArea = 0 ?>
                    <tr>
                        <th colspan="5" style="background: darkgray"><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if($currentOrgId != $datum['id_organization']):
                    $currentOrgId = $datum['id_organization'] ?>
                    <tr style="background: lightgrey">
                        <td></td>
                        <th colspan="5">Организация <?= $organizations[$currentOrgId] ?></th>
                    </tr>
                <?php endif ?>
                    <tr>
                        <td>
                            <?= $datum['fullname'] ?>
                        </td>
                        <td>
                            <?= $datum['total_visits'] ?>
                        </td>
                        <td>
                            <?= $datum['total_services'] ?>
                        </td>
                        <td>
                            <?= $datum['total_amount'] ?>
                        </td>
                        <td>
                            <?= $datum['total_free_services'] ?>
                        </td>
                    </tr>
                <?php
                $totalPerOrg++;
                $specNamesPerArea[] = $datum['fullname'];
                $specNames[] = $datum['fullname'];
                $totalVisitsPerOrg += $datum['total_visits'];
                $totalVisitsPerArea += $datum['total_visits'];
                $totalVisits += $datum['total_visits'];
                $totalServicesPerOrg += $datum['total_services'];
                $totalServicesPerArea += $datum['total_services'];
                $totalServices += $datum['total_services'];
                $totalSumAmountPerOrg += $datum['total_amount'];
                $totalSumAmountPerArea += $datum['total_amount'];
                $totalSumAmount += $datum['total_amount'];
                $totalFreeServicesPerOrg += $datum['total_free_services'];
                $totalFreeServicesPerArea += $datum['total_free_services'];
                $totalFreeServices += $datum['total_free_services'] ?>
            <?endforeach?>
                    <tr style="background: lightgrey">
                        <th>Итого по организации <?= $totalPerOrg?></th>
                        <th><?=$totalVisitsPerOrg?> </th>
                        <th><?=$totalServicesPerOrg?> </th>
                        <th><?=$totalSumAmountPerOrg?> </th>
                        <th><?=$totalFreeServicesPerOrg?> </th>
                    </tr>
                    <tr style="background: darkgray">
                        <th>Итого по округу <?=count(array_unique($specNamesPerArea))?></th>
                        <th><?=$totalVisitsPerArea?> </th>
                        <th><?=$totalServicesPerArea?> </th>
                        <th><?=$totalSumAmountPerArea?> </th>
                        <th><?=$totalFreeServicesPerArea?> </th>
                    </tr>
                    <tr style="background: darkgray">
                        <th>Всего <?=count(array_unique($specNames))?></th>
                        <th><?=$totalVisits?> </th>
                        <th><?=$totalServices?> </th>
                        <th><?=$totalSumAmount?> </th>
                        <th><?=$totalFreeServices?> </th>
                    </tr>
            </table>
        </div>
    </div>
