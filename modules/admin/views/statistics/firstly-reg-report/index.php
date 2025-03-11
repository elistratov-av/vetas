<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.02.19
 * Time: 15:10
 */

use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$this->blocks['content-header'] = 'Отчет по первично зарегистрированным владельцам/животным';
$this->registerCss('.nowrap {white-space: nowrap;}');

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
                                        'format' => 'Y-m-d H:i:s',
                                    ],
                                    'timePicker' => true,
                                    'timePicker24Hour' => true,
                                    'timePickerIncrement' => 1,
                                    'startDate' => new JsExpression('moment().startOf(\'hour\')'),
                                    'endDate' => new JsExpression('moment().endOf(\'hour\')'),
                                    'ranges' => [
                                        'Сегодня' => [
                                            new JsExpression("moment().startOf('day')"),
                                            new JsExpression("moment()")
                                        ],
                                        'С начала недели' => [
                                            new JsExpression('moment().startOf(\'week\')'),
                                            new JsExpression('moment()')
                                        ],
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
                                'name' => 'id_reg_organization',
                                'value' => \Yii::$app->request->get('id_reg_organization'),
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
                        <label class="control-label" style="text-align: left; ">Виды животных:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'idSpec',
                                'value' => \Yii::$app->request->get('idSpec'),
                                'data' => $species,
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
                        Url::toRoute(['statistics/firstly-reg-report']),
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
                            'statistics/firstly-reg-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_area' => \Yii::$app->request->get('id_area'),
                            'id_district' => \Yii::$app->request->get('id_district'),
                            'id_reg_organization' => \Yii::$app->request->get('id_reg_organization'),
                            'idSpec' => \Yii::$app->request->get('idSpec'),
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
            $totals = [
                'totalPerSpec' => 0,
                'totalPerOrg' => 0,
                'totalPerDist' => 0,
                'totalPerArea' => 0,
                'total' => 0
            ];
            ?>
            <?foreach ($data as $datum): ?>
                <?php if ($currentSpecId != $datum['idSpec']
                            && $currentSpecId != null
                            ||  (($currentOrgId != $datum['id_reg_organization']
                                || $currentDistId != $datum['id_district']
                                ||  $currentAreaId != $datum['id_area']))
                                && $currentSpecId != null): ?>
                    <tr style="background: #eeeeee">
                        <th  colspan="7">Итого по виду <?= $totals['totalPerSpec']?></th>
                    </tr>
                    <?php $totals['totalPerSpec'] = 0;
                        $currentSpecId = null ?>
                <?php endif; ?>

                <?php if($currentOrgId != $datum['id_reg_organization'] && $currentOrgId != null): ?>
                    <tr style="background: #cdcdcd">
                        <th  colspan="7">Итого по организации <?= $totals['totalPerOrg']?></th>
                    </tr>
                    <?php $totals['totalPerOrg'] = 0;
                    $currentSpecId = null?>
                <?php endif; ?>
                <?php if($currentDistId != $datum['id_district'] && $currentDistId != null): ?>
                    <tr style="background: #ababab">
                        <th  colspan="7">Итого по району <?= $totals['totalPerDist']?></th>
                    </tr>
                    <?php $totals['totalPerDist'] = 0;
                    $currentSpecId = null?>
                <?php endif; ?>
                <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #8a8a8a">
                        <th  colspan="7">Итого по округу <?=$totals['totalPerArea']?></th>
                    </tr>
                    <?php $totals['totalPerArea'] = 0;
                    $currentSpecId = null ?>
                <?php endif?>
                <?php
                if($currentAreaId != $datum['id_area']):
                    $currentAreaId = $datum['id_area'];
                    ?>
                    <tr>
                        <th colspan="7" style="background: #8a8a8a"><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if($currentDistId != $datum['id_district']):
                    $currentDistId = $datum['id_district']; ?>
                    <tr>
                        <th colspan="7" style="background: #ababab">Район <?=$districts[$currentDistId] ?? 'не указан' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if($currentOrgId != $datum['id_reg_organization']):
                    $currentOrgId = $datum['id_reg_organization']; ?>
                    <tr>
                        <th colspan="7" style="background: #cdcdcd">Организация <?=$organizations[$currentOrgId] ?? 'не указана' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if($currentSpecId != $datum['idSpec']):
                    $currentSpecId = $datum['idSpec']; ?>
                    <tr>
                        <th colspan="7" style="background: #eeeeee"><?= $species[$currentSpecId] ?? 'Вид не указан' ?></th>
                    </tr>
                <?php endif ?>
                <tr>
                    <td>
                        <?= $datum['ownName'] ?>
                    </td>
                    <td>
                        <?= $datum['ownAddress'] ?>
                    </td>
                    <td>
                        <?= $datum['ownPhone'] ?>
                    </td>
                    <td>
                        <?= $datum['petName'] ?>
                    </td>
                    <td>
                        <?= $datum['identType'] ?>
                    </td>
                    <td>
                        <?= $datum['identification_code'] ?>
                    </td>
                </tr>
                <?php
                foreach ($totals as &$total) {
                    $total++;
                }

                ?>
            <?endforeach?>
            <tr style="background: #eeeeee">
                <th colspan="7">Итого по виду <?= $totals['totalPerSpec']?></th>
            </tr>
            <tr style="background: #cdcdcd">
                <th colspan="7">Итого по организации <?= $totals['totalPerOrg']?></th>
            </tr>
            <tr style="background: #ababab">
                <th colspan="7">Итого по району <?= $totals['totalPerDist']?></th>
            </tr>
            <tr style="background: #8a8a8a">
                <th colspan="7">Итого по округу <?=$totals['totalPerArea']?></th>
            </tr>
            <tr style="background: #686868">
                <th colspan="7">Всего <?=$totals['total']?></th>
            </tr>
        </table>
    </div>
</div>
