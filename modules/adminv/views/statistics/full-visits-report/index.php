<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.06.19
 * Time: 16:12
 */

use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\ShiftType;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/** @var $this \yii\web\View */
/** @var $data array */
/** @var $organizations array */
/** @var $visit_types array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Общий отчет по приемам';
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

$shift_types = [
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT) => 'mos.ru',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT) => 'Телефон',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE) => 'Живая очередь',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY) => 'Направление',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME) => 'Выезд на дом',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME) => 'Выезд на дом (mos.ru)',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE) => 'Выезд на дом (НВП)',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION) => 'Прививочный пункт',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_SHELTER) => 'Выезд в приют',
    strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_DETOUR) => 'Обход',
];


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
                        <label class="control-label" style="text-align: left; ">Административный(е) округ(а):</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'id_area',
                                'value' => \Yii::$app->request->get('id_area'),
                                'data' => $areas,
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
                        <label class="control-label" style="text-align: left; ">Район(ы):</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'id_district',
                                'value' => \Yii::$app->request->get('id_district'),
                                'data' => $districts,
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
                        <label class="control-label" style="text-align: left; ">В организации(ях):</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'id_organization',
                                'value' => \Yii::$app->request->get('id_organization'),
                                'data' => $organizations,
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
                        Url::toRoute(['statistics/full-visits-report']),
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
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead style="background: white">
            <tr>
                <th colspan="41" style="text-align: center">Обработано обращений (записей на прием)</th>
            </tr>
            <tr>
                <th></th>
                <?php foreach ($shift_types as $type => $name): ?>
                    <th colspan="4" style="text-align: center"><?= $name ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <th></th>
                <?php foreach ($shift_types as $type => $name): ?>
                    <th>Всего</th>
                    <th>Отменено</th>
                    <th>Перенесено</th>
                    <th>Завершено</th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $currentDistId = null;

            $total = [];
            $totalsPerDist = [];
            $totalsPerArea = [];

            foreach ($shift_types as $type => $name) {
                $total["{$type}_created"] = 0;
                $total["{$type}_cancelled"] = 0;
                $total["{$type}_transfered"] = 0;
                $total["{$type}_finished"] = 0;

                $totalsPerDist["{$type}_created"] = 0;
                $totalsPerDist["{$type}_cancelled"] = 0;
                $totalsPerDist["{$type}_transfered"] = 0;
                $totalsPerDist["{$type}_finished"] = 0;

                $totalsPerArea["{$type}_created"] = 0;
                $totalsPerArea["{$type}_cancelled"] = 0;
                $totalsPerArea["{$type}_transfered"] = 0;
                $totalsPerArea["{$type}_finished"] = 0;
            }


            ?>
            <?php foreach ($data as $datum): ?>
                <?php if ($currentDistId != $datum['id_district'] && $currentDistId != null): ?>
                    <tr style="background: #c8c8c8">
                        <th>Итого по району</th>
                        <?php foreach ($shift_types as $type => $name): ?>
                            <th><?= $totalsPerDist["{$type}_created"] ?> </th>
                            <th><?= $totalsPerDist["{$type}_cancelled"] ?> </th>
                            <th><?= $totalsPerDist["{$type}_transfered"] ?> </th>
                            <th><?= $totalsPerDist["{$type}_finished"] ?> </th>
                        <?php endforeach; ?>
                    </tr>
                    <?php
                    $serviceNamesPerDist = [];
                    foreach ($shift_types as $type => $name) {
                        $totalsPerDist["{$type}_created"] = 0;
                        $totalsPerDist["{$type}_cancelled"] = 0;
                        $totalsPerDist["{$type}_transfered"] = 0;
                        $totalsPerDist["{$type}_finished"] = 0;
                    }
                    ?>
                <?php endif; ?>

                <?php if ($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #a0a0a0">
                        <th>Итого по округу</th>
                        <?php foreach ($shift_types as $type => $name): ?>
                            <th><?= $totalsPerArea["{$type}_created"] ?> </th>
                            <th><?= $totalsPerArea["{$type}_cancelled"] ?> </th>
                            <th><?= $totalsPerArea["{$type}_transfered"] ?> </th>
                            <th><?= $totalsPerArea["{$type}_finished"] ?> </th>
                        <?php endforeach; ?>
                    </tr>
                    <?php
                    foreach ($shift_types as $type => $name) {
                        $totalsPerArea["{$type}_created"] = 0;
                        $totalsPerArea["{$type}_cancelled"] = 0;
                        $totalsPerArea["{$type}_transfered"] = 0;
                        $totalsPerArea["{$type}_finished"] = 0;
                    }
                    ?>
                <?php endif ?>
                <?php
                if ($currentAreaId != $datum['id_area']):
                    $currentAreaId = $datum['id_area'];
                    ?>
                    <tr>
                        <th colspan="41"
                            style="background: #a0a0a0"><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <?php
                if ($currentDistId != $datum['id_district']):
                    $currentDistId = $datum['id_district'];
                    ?>
                    <tr>
                        <th colspan="41" style="background: #c8c8c8">
                            Район <?= $districts[$currentDistId] ?? 'не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <?= $datum['short_name'] ?>
                    </td>
                    <?php foreach ($shift_types as $type => $name): ?>
                        <td>
                            <?= $datum["{$type}_created"] ?>
                        </td>
                        <td>
                            <?= $datum["{$type}_cancelled"] ?>
                        </td>
                        <td>
                            <?= $datum["{$type}_transfered"] ?>
                        </td>
                        <td>
                            <?= $datum["{$type}_finished"] ?>
                        </td>
                    <?php endforeach; ?>
                </tr>

                <?php

                foreach ($shift_types as$type => $name) {
                    $total["{$type}_created"] += $datum["{$type}_created"];
                    $total["{$type}_cancelled"] += $datum["{$type}_cancelled"];
                    $total["{$type}_transfered"] += $datum["{$type}_transfered"];
                    $total["{$type}_finished"] += $datum["{$type}_finished"];

                    $totalsPerDist["{$type}_created"] += $datum["{$type}_created"];
                    $totalsPerDist["{$type}_cancelled"] += $datum["{$type}_cancelled"];
                    $totalsPerDist["{$type}_transfered"] += $datum["{$type}_transfered"];
                    $totalsPerDist["{$type}_finished"] += $datum["{$type}_finished"];

                    $totalsPerArea["{$type}_created"] += $datum["{$type}_created"];
                    $totalsPerArea["{$type}_cancelled"] += $datum["{$type}_cancelled"];
                    $totalsPerArea["{$type}_transfered"] += $datum["{$type}_transfered"];
                    $totalsPerArea["{$type}_finished"] += $datum["{$type}_finished"];
                }
                ?>
            <?php endforeach; ?>
            <tr style="background: #c8c8c8">
                <th>Итого по району</th>
                <?php foreach ($shift_types as $type => $name): ?>
                    <th><?= $totalsPerDist["{$type}_created"] ?> </th>
                    <th><?= $totalsPerDist["{$type}_cancelled"] ?> </th>
                    <th><?= $totalsPerDist["{$type}_transfered"] ?> </th>
                    <th><?= $totalsPerDist["{$type}_finished"] ?> </th>
                <?php endforeach; ?>
            </tr>
            <tr style="background: #a0a0a0">
                <th>Итого по округу</th>
                <?php foreach ($shift_types as $type => $name): ?>
                    <th><?= $totalsPerArea["{$type}_created"] ?> </th>
                    <th><?= $totalsPerArea["{$type}_cancelled"] ?> </th>
                    <th><?= $totalsPerArea["{$type}_transfered"] ?> </th>
                    <th><?= $totalsPerArea["{$type}_finished"] ?> </th>
                <?php endforeach; ?>
            </tr>
            <tr style="background: #808080">
                <th>Всего</th>
                <?php foreach ($shift_types as $type => $name): ?>
                    <th><?= $total["{$type}_created"] ?> </th>
                    <th><?= $total["{$type}_cancelled"] ?> </th>
                    <th><?= $total["{$type}_transfered"] ?> </th>
                    <th><?= $total["{$type}_finished"] ?> </th>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>
</div>
