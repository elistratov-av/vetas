<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.03.19
 * Time: 13:29
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
/** @var $species array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет о выданных регистрационных удостоверениях';
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
                                        'С начала месяца' => [
                                            new JsExpression('moment().startOf(\'month\')'),
                                            new JsExpression('moment()'),
                                        ],
                                        'С начала года' => [
                                            new JsExpression('moment().startOf(\'year\')'),
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
                                'name' => 'id_species',
                                'value' => \Yii::$app->request->get('id_species'),
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
                        Url::toRoute(['statistics/reg-pets-report']),
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
                            'statistics/reg-pets-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_organization' => \Yii::$app->request->get('id_organization', []),
                            'id_species' => \Yii::$app->request->get('id_species', []),
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
            <thead>
            <tr style="background: white">
                <th>Кличка</th>
                <th>Тип метки<br><span style="font-size: smaller">(основной идентификатор животного)</span></th>
                <th>Значение метки</th>
                <th>Имя владельца<br><span style="font-size: smaller">(ФЛ, ЮЛ)</span></th>
                <th>Адрес владельца<br><span style="font-size: smaller">(ФЛ, ЮЛ)</span></th>
                <th>Телефон владельца<br><span style="font-size: smaller">(ФЛ, ЮЛ)</span></th>
                <th>Дата регистрации</th>
                <th>Номер регистрационного удостоверения</th>
            </tr>
            </thead>
            <?php
            $currentOrgId = null;
            $currentSpecId = null;
            ?>
            <?php foreach ($rows as $row): ?>
                <?php if ($currentSpecId != $row['id_species']
                    && $currentSpecId != null
                    || (($currentOrgId != $row['id_reg_organization']))
                    && $currentSpecId != null): ?>
                    <tr style="background: #eeeeee">
                        <th colspan="8">Итого по виду <?= ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentOrgId, []), (int)$currentSpecId, 0); ?></th>
                    </tr>
                    <?php $currentSpecId = null ?>
                <?php endif; ?>
                <?php if ($currentOrgId != $row['id_reg_organization'] && $currentOrgId != null): ?>
                    <tr style="background: #cdcdcd">
                        <th colspan="8">Итого по организации <?= ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentOrgId, []), 'total', 0); ?></th>
                    </tr>
                    <?php $currentSpecId = null ?>
                <?php endif; ?>
                <?php
                if ($currentOrgId != $row['id_reg_organization']):
                    $currentOrgId = $row['id_reg_organization']; ?>
                    <tr>
                        <th colspan="8" style="background: #cdcdcd">Организация <?= $organizations[$currentOrgId] ?? 'не указана' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if ($currentSpecId != $row['id_species']):
                    $currentSpecId = $row['id_species']; ?>
                    <tr>
                        <th colspan="8" style="background: #eeeeee"><?= $species[$currentSpecId] ?? 'Вид не указан' ?></th>
                    </tr>
                <?php endif ?>
                <tr>
                    <td>
                        <?= $row['pet_name'] ?>
                    </td>
                    <td>
                        <?= $row['ident_name'] ?>
                    </td>
                    <td>
                        <?= $row['identification_code'] ?>
                    </td>
                    <td>
                        <?= $row['fullname'] ?>
                    </td>
                    <td>
                        <?= $row['full_address'] ?>
                    </td>
                    <td>
                        <?= $row['contact_name'] ?>
                    </td>
                    <td>
                        <?= $row['reg_date'] ?>
                    </td>
                    <td>
                        <?= $row['number'] ?>
                    </td>
                </tr>
            <?php endforeach ?>
            <!-- это последняя страница? -->
            <?php if ($pagination->getPage() == ($pagination->getPageCount() - 1)): ?>
                <tr style="background: #eeeeee">
                    <th colspan="8">Итого по виду <?= ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentOrgId, []), (int)$currentSpecId, 0); ?></th>
                </tr>
                <tr style="background: #cdcdcd">
                    <th colspan="8">Итого по организации <?= ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentOrgId, []), 'total', 0); ?></th>
                </tr>
                <tr style="background: #686868">
                    <!-- $pagination->totalCount дает здесь некорректный результат из-за LEFT JOIN -->
                    <!--<th colspan="8">Всего <?/*= $pagination->totalCount; */?></th>-->
                    <th colspan="8">Всего <?= ArrayHelper::getValue($subtotals, 'total', 0); ?></th>
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
