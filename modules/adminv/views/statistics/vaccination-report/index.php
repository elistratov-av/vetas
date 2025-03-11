<?php

use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/** @var $this \yii\web\View */
/** @var $organizationsList string[] */
/** @var $areasList string[] */
/** @var $data array */
/** @var $dateFilter string */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет по охвату вакцинацией против бешенства';
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
                                        }"),
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
                                            new JsExpression('moment()'),
                                        ],
                                        'С начала года' => [
                                            new JsExpression('moment().startOf(\'year\')'),
                                            new JsExpression('moment()'),
                                        ],
                                        'За 5 лет' => [
                                            new JsExpression('new Date(new Date().setFullYear(new Date().getFullYear() - 5))'),
                                            new JsExpression('moment()'),
                                        ],
                                    ],
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
                                'data' => $areasList,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions' => [
                                    'nonSelectedText' => 'Не выбрано',
                                    'enableCaseInsensitiveFiltering' => true,
                                    'includeResetOption' => true,
                                    'resetText' => 'Сбросить',
                                    'maxHeight' => 450,
                                ],
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
                                'data' => $organizationsList,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions' => [
                                    'nonSelectedText' => 'Не выбрано',
                                    'enableCaseInsensitiveFiltering' => true,
                                    'includeResetOption' => true,
                                    'resetText' => 'Сбросить',
                                    'maxHeight' => 450,
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6" style="padding-right: 0;">
                    <?= Html::a(
                        'Сбросить',
                        Url::toRoute(['statistics/vaccination-report']),
                        [
                            'class' => 'btn btn-default btn-sm',
                        ]
                    ) ?>
                    <?= Html::input('submit', 'submit', 'Показать', [
                        'class' => 'btn btn-primary btn-sm',
                    ]); ?>
                    <?= Html::a(
                        '<span class="glyphicon glyphicon-download"></span> Сохранить XLS',
                        Url::toRoute([
                            'statistics/vaccination-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_organization' => \Yii::$app->request->get('id_organization', []),
                            'id_area' => \Yii::$app->request->get('id_area', []),
                        ]),
                        [
                            'class' => 'btn btn-primary btn-sm',
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
                <th rowspan="2">Организация</th>
                <th rowspan="2">Кошек на учете</th>
                <th colspan="2" class="text-center">Вакцинировано</th>
                <th rowspan="2">Отказ от вакцинации</th>
                <th rowspan="2">Собак на учете</th>
                <th colspan="2" class="text-center">Вакцинировано</th>
                <th rowspan="2">Отказ от вакцинации</th>
                <th rowspan="2">Прочих животных на учете</th>
                <th colspan="2" class="text-center">Вакцинировано</th>
                <th rowspan="2">Отказ от вакцинации</th>
            </tr>
            <tr>
                <th>Вакциной Рабикан</th>
                <th>Комплексной вакциной</th>
                <th>Вакциной Рабикан</th>
                <th>Комплексной вакциной</th>
                <th>Вакциной Рабикан</th>
                <th>Комплексной вакциной</th>
            </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $totalCatsPerArea = 0;
            $totalCatsRabicanPerArea = 0;
            $totalCatsComplexPerArea = 0;
            $totalCatsRejectedPerArea = 0;
            $totalDogsPerArea = 0;
            $totalDogsRabicanPerArea = 0;
            $totalDogsComplexPerArea = 0;
            $totalDogsRejectedPerArea = 0;
            $totalOtherPerArea = 0;
            $totalOtherRabicanPerArea = 0;
            $totalOtherComplexPerArea = 0;
            $totalOtherRejectedPerArea = 0;

            $totalCats = 0;
            $totalCatsRabican = 0;
            $totalCatsComplex = 0;
            $totalCatsRejected = 0;
            $totalDogs = 0;
            $totalDogsRabican = 0;
            $totalDogsComplex = 0;
            $totalDogsRejected = 0;
            $totalOther = 0;
            $totalOtherRabican = 0;
            $totalOtherComplex = 0;
            $totalOtherRejected = 0;
            ?>
            <?php foreach ($data as $datum): ?>
                <?php if ($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #808080">
                        <th>Итого по округу</th>
                        <th><?= $totalCatsPerArea ?></th>
                        <th><?= $totalCatsRabicanPerArea ?> </th>
                        <th><?= $totalCatsComplexPerArea ?> </th>
                        <th><?= $totalCatsRejectedPerArea ?> </th>
                        <th><?= $totalDogsPerArea ?> </th>
                        <th><?= $totalDogsRabicanPerArea ?> </th>
                        <th><?= $totalDogsComplexPerArea ?> </th>
                        <th><?= $totalDogsRejectedPerArea ?> </th>
                        <th><?= $totalOtherPerArea ?> </th>
                        <th><?= $totalOtherRabicanPerArea ?> </th>
                        <th><?= $totalOtherComplexPerArea ?> </th>
                        <th><?= $totalOtherRejectedPerArea ?> </th>
                    </tr>
                    <?php
                    $totalCatsPerArea = 0;
                    $totalCatsRabicanPerArea = 0;
                    $totalCatsComplexPerArea = 0;
                    $totalCatsRejectedPerArea = 0;
                    $totalDogsPerArea = 0;
                    $totalDogsRabicanPerArea = 0;
                    $totalDogsComplexPerArea = 0;
                    $totalDogsRejectedPerArea = 0;
                    $totalOtherPerArea = 0;
                    $totalOtherRabicanPerArea = 0;
                    $totalOtherComplexPerArea = 0;
                    $totalOtherRejectedPerArea = 0;
                    ?>
                <?php endif ?>
                <?php
                if ($currentAreaId != $datum['id_area']):
                    $currentAreaId = $datum['id_area'];
                    ?>
                    <tr>
                        <th colspan="13" style="background: #808080"><?= $areasList[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <?= $datum['short_name'] ?? 'Организация не указана' ?>
                    </td>
                    <td>
                        <?= $datum['total_cats'] ?>
                    </td>
                    <td>
                        <?= $datum['total_rabies_cats'] ?>
                    </td>
                    <td>
                        <?= $datum['total_complex_cats'] ?>
                    </td>
                    <td>
                        <?= $datum['total_reject_cats'] ?>
                    </td>
                    <td>
                        <?= $datum['total_dogs'] ?>
                    </td>
                    <td>
                        <?= $datum['total_rabies_dogs'] ?>
                    </td>
                    <td>
                        <?= $datum['total_complex_dogs'] ?>
                    </td>
                    <td>
                        <?= $datum['total_reject_dogs'] ?>
                    </td>
                    <td>
                        <?= $datum['total_other'] ?>
                    </td>
                    <td>
                        <?= $datum['total_rabies_other'] ?>
                    </td>
                    <td>
                        <?= $datum['total_complex_other'] ?>
                    </td>
                    <td>
                        <?= $datum['total_reject_other'] ?>
                    </td>
                </tr>
                <?php
                $totalCatsPerArea += $datum['total_cats'];
                $totalCatsRabicanPerArea += $datum['total_rabies_cats'];
                $totalCatsComplexPerArea += $datum['total_complex_cats'];
                $totalCatsRejectedPerArea += $datum['total_reject_cats'];
                $totalDogsPerArea += $datum['total_dogs'];
                $totalDogsRabicanPerArea += $datum['total_rabies_dogs'];
                $totalDogsComplexPerArea += $datum['total_complex_dogs'];
                $totalDogsRejectedPerArea += $datum['total_reject_dogs'];
                $totalOtherPerArea += $datum['total_other'];
                $totalOtherRabicanPerArea += $datum['total_rabies_other'];
                $totalOtherComplexPerArea += $datum['total_complex_other'];
                $totalOtherRejectedPerArea += $datum['total_reject_other'];

                $totalCats += $datum['total_cats'];
                $totalCatsRabican += $datum['total_rabies_cats'];
                $totalCatsComplex += $datum['total_complex_cats'];
                $totalCatsRejected += $datum['total_reject_cats'];
                $totalDogs += $datum['total_dogs'];
                $totalDogsRabican += $datum['total_rabies_dogs'];
                $totalDogsComplex += $datum['total_complex_dogs'];
                $totalDogsRejected += $datum['total_reject_dogs'];
                $totalOther += $datum['total_other'];
                $totalOtherRabican += $datum['total_rabies_other'];
                $totalOtherComplex += $datum['total_complex_other'];
                $totalOtherRejected += $datum['total_reject_other'];
                ?>
            <?php endforeach ?>
            <tr style="background: #808080">
                <th>Итого по округу</th>
                <th><?= $totalCatsPerArea ?></th>
                <th><?= $totalCatsRabicanPerArea ?> </th>
                <th><?= $totalCatsComplexPerArea ?> </th>
                <th><?= $totalCatsRejectedPerArea ?> </th>
                <th><?= $totalDogsPerArea ?> </th>
                <th><?= $totalDogsRabicanPerArea ?> </th>
                <th><?= $totalDogsComplexPerArea ?> </th>
                <th><?= $totalDogsRejectedPerArea ?> </th>
                <th><?= $totalOtherPerArea ?> </th>
                <th><?= $totalOtherRabicanPerArea ?> </th>
                <th><?= $totalOtherComplexPerArea ?> </th>
                <th><?= $totalOtherRejectedPerArea ?> </th>
            </tr>
            <tr style="background: #666666">
                <th>Всего</th>
                <th><?= $totalCats ?></th>
                <th><?= $totalCatsRabican ?> </th>
                <th><?= $totalCatsComplex ?> </th>
                <th><?= $totalCatsRejected ?> </th>
                <th><?= $totalDogs ?> </th>
                <th><?= $totalDogsRabican ?> </th>
                <th><?= $totalDogsComplex ?> </th>
                <th><?= $totalDogsRejected ?> </th>
                <th><?= $totalOther ?> </th>
                <th><?= $totalOtherRabican ?> </th>
                <th><?= $totalOtherComplex ?> </th>
                <th><?= $totalOtherRejected ?> </th>
            </tr>
        </table>
    </div>
    </div>
</div>
