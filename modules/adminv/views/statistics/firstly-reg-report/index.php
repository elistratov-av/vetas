<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.02.19
 * Time: 15:10
 */

use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\ArrayHelper;
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
/** @var $species array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет по первично зарегистрированным владельцам/животным';
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
                        <label class="control-label" style="text-align: left; ">Виды животных:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'idSpec',
                                'value' => \Yii::$app->request->get('idSpec'),
                                'data' => $species,
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
                        Url::toRoute(['statistics/firstly-reg-report']),
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
                            'statistics/firstly-reg-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_area' => \Yii::$app->request->get('id_area'),
                            'id_district' => \Yii::$app->request->get('id_district'),
                            'id_organization' => \Yii::$app->request->get('id_organization'),
                            'idSpec' => \Yii::$app->request->get('idSpec'),
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
                <th>Владелец<br><span style="font-size: smaller">(ФЛ, ЮЛ)</span></th>
                <th>Адрес владельца</th>
                <th>Телефон владельца</th>
                <th>Кличка животного</th>
                <th>Тип метки<br><span style="font-size: smaller">(основной идентификатор животного)</span></th>
                <th>Значение метки</th>
            </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $currentDistId = null;
            $currentOrgId = null;
            $currentSpecId = null;
            ?>
            <?php foreach ($rows as $row): ?>
                <?php if ($currentSpecId != $row['idSpec'] && $currentSpecId != null
                    || (($currentOrgId != $row['id_reg_organization']
                        || $currentDistId != $row['id_district']
                        || $currentAreaId != $row['id_area']))
                    && $currentSpecId != null): ?>
                    <tr style="background: #eeeeee">
                        <th colspan="7">Итого по виду <?= empty($subtotals['perOrg'][$currentOrgId]) ? '' : ArrayHelper::getValue($subtotals['perOrg'][$currentOrgId], $currentSpecId, ''); ?></th>
                    </tr>
                    <?php $currentSpecId = null ?>
                <?php endif; ?>

                <?php if ($currentOrgId != $row['id_reg_organization'] && $currentOrgId != null): ?>
                    <tr style="background: #cdcdcd">
                        <th colspan="7">Итого по организации <?= empty($subtotals['perOrg'][$currentOrgId]) ? '' : array_sum(array_values($subtotals['perOrg'][$currentOrgId])); ?></th>
                    </tr>
                    <?php $currentSpecId = null ?>
                <?php endif; ?>
                <?php if ($currentDistId != $row['id_district'] && $currentDistId != null): ?>
                    <tr style="background: #ababab">
                        <th colspan="7">Итого по району <?= empty($subtotals['perDist'][$currentDistId]) ? '' : $subtotals['perDist'][$currentDistId]; ?></th>
                    </tr>
                    <?php $currentSpecId = null ?>
                <?php endif; ?>
                <?php if ($currentAreaId != $row['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #8a8a8a">
                        <th colspan="7">Итого по округу <?= empty($subtotals['perArea'][$currentAreaId]) ? '' : $subtotals['perArea'][$currentAreaId]; ?></th>
                    </tr>
                    <?php $currentSpecId = null ?>
                <?php endif ?>
                <?php
                if ($currentAreaId != $row['id_area']):
                    $currentAreaId = $row['id_area'];
                    ?>
                    <tr>
                        <th colspan="7" style="background: #8a8a8a"><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if ($currentDistId != $row['id_district']):
                    $currentDistId = $row['id_district']; ?>
                    <tr>
                        <th colspan="7" style="background: #ababab">Район <?= $districts[$currentDistId] ?? 'не указан' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if ($currentOrgId != $row['id_reg_organization']):
                    $currentOrgId = $row['id_reg_organization']; ?>
                    <tr>
                        <th colspan="7" style="background: #cdcdcd">Организация <?= $organizations[$currentOrgId] ?? 'не указана' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if ($currentSpecId != $row['idSpec']):
                    $currentSpecId = $row['idSpec']; ?>
                    <tr>
                        <th colspan="7" style="background: #eeeeee"><?= $species[$currentSpecId] ?? 'Вид не указан' ?></th>
                    </tr>
                <?php endif ?>
                <tr>
                    <td>
                        <?= $row['ownName'] ?>
                    </td>
                    <td>
                        <?= $row['ownAddress'] ?>
                    </td>
                    <td>
                        <?= $row['ownPhone'] ?>
                    </td>
                    <td>
                        <?= $row['petName'] ?>
                    </td>
                    <td>
                        <?= $row['identType'] ?>
                    </td>
                    <td>
                        <?= $row['identification_code'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <!-- это последняя страница? -->
            <?php if ($pagination->getPage() == ($pagination->getPageCount() - 1)): ?>
                <tr style="background: #eeeeee">
                    <th colspan="7">Итого по виду <?= empty($subtotals['perOrg'][$currentOrgId]) ? '' : ArrayHelper::getValue($subtotals['perOrg'][$currentOrgId], $currentSpecId, ''); ?></th>
                </tr>
                <tr style="background: #cdcdcd">
                    <th colspan="7">Итого по организации <?= empty($subtotals['perOrg'][$currentOrgId]) ? '' : array_sum(array_values($subtotals['perOrg'][$currentOrgId])); ?></th>
                </tr>
                <tr style="background: #ababab">
                    <th colspan="7">Итого по району <?= empty($subtotals['perDist'][$currentDistId]) ? '' : $subtotals['perDist'][$currentDistId]; ?></th>
                </tr>
                <tr style="background: #8a8a8a">
                    <th colspan="7">Итого по округу <?= empty($subtotals['perArea'][$currentAreaId]) ? '' : $subtotals['perArea'][$currentAreaId]; ?></th>
                </tr>
                <tr style="background: #686868">
                    <th colspan="7">Всего <?= $pagination->totalCount ?></th>
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
