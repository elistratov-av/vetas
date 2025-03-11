<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.02.19
 * Time: 15:10
 */

use app\models\db\Areas;
use app\modules\admin\models\Organization;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$this->blocks['content-header'] = 'Краткая статистика организаций по регистрации и приемам';
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
                                    'name' => 'id',
                                    'value' => \Yii::$app->request->get('id'),
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
                            Url::toRoute(['statistics/common-report']),
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
                                'statistics/common-report/export',
                                'from' => Yii::$app->getRequest()->get('from', ''),
                                'to' => Yii::$app->getRequest()->get('to', ''),
                                'id' => \Yii::$app->request->get('id', []),
                                'id_area' => \Yii::$app->request->get('id_area', [])
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
                        <th>Организация</th>
                        <th>Зарегистрировано животных всего</th>
                        <th>Зарегистрировано животных за период</th>
                        <th>Приемов всего</th>
                        <th>Приемов за период</th>
                    </tr>
                </thead>
                <?php
                $currentOrgId = null;
                $currentAreaId = null;
                $totalPetsPerArea = 0;
                $totalPetsAtAll = 0;
                $periodPetsPerArea = 0;
                $periodPetsAtAll = 0;
                $totalVisitsPerArea = 0;
                $totalVisitsAtAll = 0;
                $periodVisitsPerArea = 0;
                $periodVisitsAtAll = 0 ?>
                <?foreach ($data as $datum): ?>
                    <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                        <tr style="background: darkgray">
                            <th>Итого по округу</th>
                            <th><?=$totalPetsPerArea?> </th>
                            <th><?=$periodPetsPerArea?> </th>
                            <th><?=$totalVisitsPerArea?> </th>
                            <th><?=$periodVisitsPerArea?> </th>
                        </tr>
                        <?php $totalPetsPerArea = 0;
                        $periodPetsPerArea = 0;
                        $totalVisitsPerArea = 0;
                        $periodVisitsPerArea = 0 ?>
                    <?php endif?>
                    <?php
                    if($currentAreaId != $datum['id_area']):
                        $currentAreaId = $datum['id_area'];
                        $currentOrgId = null;
                        $totalPetsPerArea = 0;
                        $periodPetsPerArea = 0;
                        $totalVisitsPerArea = 0;
                        $periodVisitsPerArea = 0 ?>
                        <tr>
                            <th colspan="5" style="background: darkgray"><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                        </tr>
                    <?php endif ?>
                    <?php
                    if($currentOrgId != $datum['id']):
                        $currentOrgId = $datum['id'] ?>
                        <tr>
                            <td>Организация <?= $organizations[$currentOrgId] ?></td>
                            <td><?=$datum['totalPets']?></td>
                            <td><?=$datum['periodPets']?> </td>
                            <td><?=$datum['totalVisits']?> </td>
                            <td><?=$datum['periodVisits']?> </td>
                        </tr>
                    <?php endif ?>
                    <?php
                    $totalPetsPerArea += $datum['totalPets'];
                    $totalPetsAtAll += $datum['totalPets'];
                    $periodPetsPerArea += $datum['periodPets'];
                    $periodPetsAtAll += $datum['periodPets'];
                    $totalVisitsPerArea += $datum['totalVisits'];
                    $totalVisitsAtAll += $datum['totalVisits'];
                    $periodVisitsPerArea += $datum['periodVisits'];
                    $periodVisitsAtAll += $datum['periodVisits'] ?>
                <?endforeach?>
                <tr style="background: darkgray">
                    <th>Итого по округу</th>
                    <th><?=$totalPetsPerArea?> </th>
                    <th><?=$periodPetsPerArea?> </th>
                    <th><?=$totalVisitsPerArea?> </th>
                    <th><?=$periodVisitsPerArea?> </th>
                </tr>
                <tr style="background: darkgray">
                    <th>Всего </th>
                    <th><?=$totalPetsAtAll?> </th>
                    <th><?=$periodPetsAtAll?> </th>
                    <th><?=$totalVisitsAtAll?> </th>
                    <th><?=$periodVisitsAtAll?> </th>
                </tr>
            </table>
        </div>
    </div>