<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.03.19
 * Time: 14:36
 */

use app\models\db\ShiftType;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\LinkPager;

/** @var $this \yii\web\View */
/** @var $rows array */
/** @var $pagination \yii\data\Pagination */
/** @var $subtotals array */
/** @var $organizations array */
/** @var $areas array */
/** @var $districts array */
/** @var $serviceTypes array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет по контролю спроса';
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
$('.exp').hover(
    function(){
        $(this).css('cursor', 'pointer')});
$('tr').click(function(){
    if ((typeof $(this).data('level') !== "undefined") && (typeof $(this).data('expandable') !== "undefined")) {
        var trLevel = +$(this).data('level');
        var el = null;
        $(this).nextAll().each(function(i, e) {
            if(+$(e).data('level') <= trLevel) {
                el = $(e);
                return false;
            }
        });
        $(this).nextUntil(el).toggle();
    }
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
                                ],
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
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" style="text-align: left; ">Канал(ы) записи:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'channel',
                                'value' => \Yii::$app->request->get('channel'),
                                'data' => ShiftType::find()
                                    ->select(new \yii\db\Expression('case when id = 1 then \'Направление\' else description end'))
                                    ->andWhere(['or', ['between', 'id', 1, 4], ['id' => 10]])
                                    ->orderBy(['description' => SORT_ASC])
                                    ->indexBy('id')
                                    ->asArray()
                                    ->column(),
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions' => [
                                    'nonSelectedText' => 'Не выбрано',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" style="text-align: left; ">Тип(ы) услуг:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'type_id',
                                'value' => \Yii::$app->request->get('type_id'),
                                'data' => $serviceTypes,
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
                        Url::toRoute(['statistics/services-report']),
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
                            'statistics/services-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_organization' => \Yii::$app->request->get('id_organization', []),
                            'id_area' => \Yii::$app->request->get('id_area', []),
                            'id_district' => \Yii::$app->request->get('id_district', []),
                            'type_id' => \Yii::$app->request->get('type_id', []),
                            'channel' => \Yii::$app->request->get('channel', []),
                        ]),
                        [
                            'class' => 'btn btn-primary btn-sm',
                        ]
                    ) ?>
                </div>
            </div>
        </form>
        <table class="table table-bordered table-hover">
            <thead style="background: white">
            <tr>
                <th>Услуга</th>
                <th>Оказано количество</th>
                <th>Стоимость за единицу</th>
                <th>Стоимость услуг (Общая)</th>
            </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $currentDistId = null;
            $currentOrgId = null;
            $currentTypeId = null;
            ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $id_area = (int)$currentAreaId;
                $id_district = (int)$currentDistId;
                $totalPerType = 0;
                $totalCountServicesPerType = 0;
                $totalSumAmountPerType = 0;
                if (isset($subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId])) {
                    $totalPerType = $subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId]['service_count'];
                    $totalCountServicesPerType = $subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId]['sum'];
                    $totalSumAmountPerType = $subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId]['total_amount'];
                }
                $totalPerOrg = 0;
                $totalCountServicesPerOrg = 0;
                $totalSumAmountPerOrg = 0;
                if (isset($subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total'])) {
                    $totalPerOrg = $subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total']['service_count'];
                    $totalCountServicesPerOrg = $subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total']['sum'];
                    $totalSumAmountPerOrg = $subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total']['total_amount'];
                }
                $totalPerDist = 0;
                $totalCountServicesPerDist = 0;
                $totalSumAmountPerDist = 0;
                if (isset($subtotals['perArea'][$id_area][$id_district]['total'])) {
                    $totalPerDist = $subtotals['perArea'][$id_area][$id_district]['total']['service_count'];
                    $totalCountServicesPerDist = $subtotals['perArea'][$id_area][$id_district]['total']['sum'];
                    $totalSumAmountPerDist = $subtotals['perArea'][$id_area][$id_district]['total']['total_amount'];
                }
                $totalPerArea = 0;
                $totalCountServicesPerArea = 0;
                $totalSumAmountPerArea = 0;
                if (isset($subtotals['perArea'][$id_area]['total'])) {
                    $totalPerArea = $subtotals['perArea'][$id_area]['total']['service_count'];
                    $totalCountServicesPerArea = $subtotals['perArea'][$id_area]['total']['sum'];
                    $totalSumAmountPerArea = $subtotals['perArea'][$id_area]['total']['total_amount'];
                }
                ?>
                <?php if ($currentTypeId != $row['type_id']
                    && $currentTypeId != null
                    || (($currentOrgId != $row['id_organization']
                        || $currentDistId != $row['id_district']
                        || $currentAreaId != $row['id_area']))
                    && $currentTypeId != null) : ?>
                    <tr style="background: #D6D6D6" data-level="1" data-expandable="0">
                        <th><?= $serviceTypes[$currentTypeId] ?>: итого <?= $totalPerType ?></th>
                        <th><?= $totalCountServicesPerType ?> </th>
                        <td></td>
                        <th><?= $totalSumAmountPerType ?> </th>
                    </tr>
                    <?php
                    $currentTypeId = null;
                    ?>
                <?php endif; ?>
                <?php if ($currentOrgId != $row['id_organization']
                    && $currentOrgId != null
                    || (($currentDistId != $row['id_district']
                        || $currentAreaId != $row['id_area']))
                    && $currentOrgId != null) : ?>
                    <tr style="background: #ABABAB" data-level="0" data-expandable="0">
                        <th>Итого по организации <?= $totalPerOrg ?></th>
                        <th><?= $totalCountServicesPerOrg ?> </th>
                        <td></td>
                        <th><?= $totalSumAmountPerOrg ?> </th>
                    </tr>
                    <?php
                    $currentTypeId = null;
                    $currentOrgId = null;
                    ?>
                <?php endif; ?>
                <?php if ($currentDistId != $row['id_district'] && $currentDistId != null): ?>
                    <tr style="background: #808080" data-level="0" data-expandable="0">
                        <th>Итого по району <?= $totalPerDist ?></th>
                        <th><?= $totalCountServicesPerDist ?> </th>
                        <td></td>
                        <th><?= $totalSumAmountPerDist ?> </th>
                    </tr>
                    <?php
                    $currentTypeId = null;
                    $currentOrgId = null;
                    ?>
                <?php endif; ?>
                <?php if ($currentAreaId != $row['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #656565" data-level="0" data-expandable="0">
                        <th>Итого по округу <?= $totalPerArea ?></th>
                        <th><?= $totalCountServicesPerArea ?> </th>
                        <td></td>
                        <th><?= $totalSumAmountPerArea ?> </th>
                    </tr>
                    <?php
                    $currentTypeId = null;
                    $currentOrgId = null;
                    ?>
                <?php endif ?>
                <?php
                if ($currentAreaId != $row['id_area']):
                    $currentAreaId = $row['id_area'];
                    ?>
                    <tr data-level="0" data-expandable="0">
                        <th colspan="4" style="background: #656565"><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <?php
                if ($currentDistId != $row['id_district']):
                    $currentDistId = $row['id_district'];
                    ?>
                    <tr data-level="0" data-expandable="0">
                        <th colspan="4" style="background: #808080">Район <?= $districts[$currentDistId] ?? 'не указан' ?></th>
                    </tr>
                <?php endif; ?>
                <?php
                if ($currentOrgId != $row['id_organization']):
                    $currentOrgId = $row['id_organization'];
                    ?>
                    <tr style="background: #ABABAB" data-level="0">
                        <th colspan="4">Организация <?= $organizations[$currentOrgId] ?></th>
                    </tr>
                <?php endif; ?>
                <?php
                if ($currentTypeId != $row['type_id']):
                    $currentTypeId = $row['type_id'];
                    ?>
                    <tr style="background: #D6D6D6" data-level="1" data-expandable="1" class="exp">
                        <th colspan="4"><?= $serviceTypes[$currentTypeId] ?></th>
                    </tr>
                <?php endif; ?>
                <tr data-level="2" data-expandable="1" style="display: none;" class="exp">
                    <td>
                        <?= $row['name'] ?>
                    </td>
                    <td>
                        <?= $row['sum'] ?>
                    </td>
                    <td>
                        <?= $row['price'] ?>
                    </td>
                    <td>
                        <?= $row['total_amount'] ?>
                    </td>
                </tr>
            <?php endforeach ?>
            <!-- это последняя страница? -->
            <?php if ($pagination->getPage() == ($pagination->getPageCount() - 1)): ?>
                <?php
                $id_area = (int)$currentAreaId;
                $id_district = (int)$currentDistId;
                if (isset($subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId])) {
                    $totalPerType = $subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId]['service_count'];
                    $totalCountServicesPerType = $subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId]['sum'];
                    $totalSumAmountPerType = $subtotals['perArea'][$id_area][$id_district][$currentOrgId][$currentTypeId]['total_amount'];
                }
                if (isset($subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total'])) {
                    $totalPerOrg = $subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total']['service_count'];
                    $totalCountServicesPerOrg = $subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total']['sum'];
                    $totalSumAmountPerOrg = $subtotals['perArea'][$id_area][$id_district][$currentOrgId]['total']['total_amount'];
                }
                if (isset($subtotals['perArea'][$id_area][$id_district]['total'])) {
                    $totalPerDist = $subtotals['perArea'][$id_area][$id_district]['total']['service_count'];
                    $totalCountServicesPerDist = $subtotals['perArea'][$id_area][$id_district]['total']['sum'];
                    $totalSumAmountPerDist = $subtotals['perArea'][$id_area][$id_district]['total']['total_amount'];
                }
                if (isset($subtotals['perArea'][$id_area]['total'])) {
                    $totalPerArea = $subtotals['perArea'][$id_area]['total']['service_count'];
                    $totalCountServicesPerArea = $subtotals['perArea'][$id_area]['total']['sum'];
                    $totalSumAmountPerArea = $subtotals['perArea'][$id_area]['total']['total_amount'];
                }
                ?>
                <tr style="background: #D6D6D6" data-level="1" data-expandable="0">
                    <th><?= $serviceTypes[$currentTypeId] ?? '(тип не указан)' ?>: итого <?= $totalPerType ?></th>
                    <th><?= $totalCountServicesPerType ?> </th>
                    <td></td>
                    <th><?= $totalSumAmountPerType ?> </th>
                </tr>
                <tr style="background: #ABABAB" data-level="0" data-expandable="0">
                    <th>Итого по организации <?= $totalPerOrg ?></th>
                    <th><?= $totalCountServicesPerOrg ?> </th>
                    <td></td>
                    <th><?= $totalSumAmountPerOrg ?> </th>
                </tr>
                <tr style="background: #808080" data-level="0" data-expandable="0">
                    <th>Итого по району <?php echo $totalPerDist; ?></th>
                    <th><?= $totalCountServicesPerDist ?> </th>
                    <td></td>
                    <th><?= $totalSumAmountPerDist ?> </th>
                </tr>
                <tr style="background: #656565" data-level="0" data-expandable="0">
                    <th>Итого по округу <?php echo $totalPerArea; ?></th>
                    <th><?= $totalCountServicesPerArea ?> </th>
                    <td></td>
                    <th><?= $totalSumAmountPerArea ?> </th>
                </tr>
                <tr style="background: #555555" data-level="0" data-expandable="0">
                    <th>Всего <?= $subtotals['total']['service_count'] ?></th>
                    <th><?= $subtotals['total']['sum'] ?> </th>
                    <td></td>
                    <th><?= $subtotals['total']['total_amount'] ?> </th>
                </tr>
            <?php endif; ?>
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
</div>
