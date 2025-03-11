<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.04.19
 * Time: 15:23
 * @var $tree
 * @var $pathOrgs
 * @var $orderedData
 * @var array $organizations
 */

use app\modules\admin\models\OrganizationsTree;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\LinkPager;

/** @var $this \yii\web\View */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет по использованию расходных материалов';
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
    <table style="width: 97%">
        <tr>
            <td style="width: 40%;">
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
                                    <label class="control-label" style="text-align: left; ">В организации(ях):</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'id_organization',
                                            'value' => Yii::$app->request->get('id_organization'),
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
                                    Url::toRoute(['statistics/balance-tmc-report']),
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
                                        'statistics/balance-exp-report/export',
                                        'from' => Yii::$app->getRequest()->get('from', ''),
                                        'to' => Yii::$app->getRequest()->get('to', ''),
                                        'id_organization' => Yii::$app->request->get('id_organization', []),
                                    ]),
                                    [
                                        'class' => 'btn btn-primary btn-sm'
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </td>
            <td style="text-align: justify">
                <ul>
                    <li><strong>Остаток на начало периода</strong> - сумма всех подтвержденных приходов и расходов (включая расходы в рамках приемов) на начало выбранного периода;</li>
                    <li><strong>Поступило</strong> - сумма всех подтвержденных приходов за выбранный период, не считая передач тмц внутри одной организации;</li>
                    <li><strong>Списано с баланса</strong> - сумма всех подтвержденных списаний (без учета передачи в другие организации и использования тмц в приемах) за выбранный период;</li>
                    <li><strong>Передано в другие организации</strong> - сумма всех подтвержденных передач в другие организации за выбранный период;</li>
                    <li><strong>Использовано в рамках приемов</strong> - сумма всех использований тмц в рамках приемов за выбранный период;</li>
                    <li><strong>Остаток на конец периода</strong> - сумма всех подтвержденных приходов и расходов (включая расходы в рамках приемов) на конец выбранного периода;</li>
                </ul>
            </td>
        </tr>
    </table>
    <table class="table table-bordered table-hover">
        <thead style="background: white">
        <tr>
            <th>Материал (ТМЦ)</th>
            <th>Ед. изм.</th>
            <th>Остаток на начало периода</th>
            <th>Поступило</th>
            <th>Списано с баланса</th>
            <th>Передано в другие организации</th>
            <th>Использовано в рамках приемов</th>
            <th>Остаток на конец периода</th>
        </tr>
        </thead>
        <?php
        $currentOrgId = null;
        ?>
        <?php foreach ($rows as $row): ?>
            <?php
            if($currentOrgId != $row['id_organization']):
                $currentOrgId = $row['id_organization'];
                ?>
                <tr>
                    <th colspan="8" style="background: #6892d2" ><?= $organizations[$currentOrgId] ?? 'Организация не указана' ?></th>
                </tr>
            <?php endif; ?>
            <tr>
                <td><?= $row['exp_name'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['sum'] + $row['balance_before'] ?? 0 ?></td>
                <td><?= $row['income'] ?? 0 ?></td>
                <td><?= $row['outcome'] ?? 0 ?></td>
                <td><?= $row['transfer'] ?? 0 ?></td>
                <td><?= $row['used'] ?? 0 ?></td>
                <td><?= $row['sum'] + $row['balance_after'] ?? 0 ?></td>
            </tr>
        <?php endforeach;?>
    </table>
    <div class="row">
        <div class="col-md-12 text-center">
            <?php echo LinkPager::widget([
                'pagination' => $pagination,
                'firstPageLabel' => true,
                'lastPageLabel' => true,
            ]); ?>
        </div>
    </div>
</div>

