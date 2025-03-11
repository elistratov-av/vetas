<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.04.19
 * Time: 15:23
 * @var $tree
 * @var $pathOrgs
 * @var $orderedData
 */

use app\modules\admin\models\Organization;
use app\modules\admin\models\OrganizationsTree;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->blocks['content-header'] = 'Отчет по использованию препаратов';
$this->registerCss('.nowrap {white-space: nowrap;}');
$organizations = Organization::find()
    ->select('short_name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();
$level = OrganizationsTree::find()
    ->select('level')
    ->asArray()
    ->indexBy('id')
    ->column();
$levelMax = max($level);
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
                            'statistics/balance-tmc-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_organization' => \Yii::$app->request->get('id_organization', []),
                        ]),
                        [
                            'class' => 'btn btn-primary btn-sm'
                        ]
                    ) ?>
                </div>
            </div>
        </form>
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th></th>
                <th>Препарат (ТМЦ)</th>
                <th>Ед. изм.</th>
                <th>Остаток на начало периода</th>
                <th>Поступило</th>
                <th>Расход</th>
                <th>Остаток на конец периода</th>
            </tr>
            </thead>
            <?php foreach ($tree as $leaf): ?>
                <?php if (in_array($leaf, $pathOrgs)) : ?>
                    <?php $level = OrganizationsTree::find()->select('level')->where(['id' => $leaf])->asArray()->indexBy('id')->column() ?>
                    <?php $orgLevel = $level[$leaf]?>
                    <tr data-level="<?=$orgLevel?>" style="background: lightgray" class="org">
                        <th>
                            <?php for($i = 1; $i <= $level[$leaf]; $i++) : ?>
                                <?php echo "&nbsp"?>
                            <?php endfor?>
                            <?=$organizations[$leaf] ?? "Название не найдено"?>
                        </th>
                        <td></td>
                        <td></td>
                        <th><?=$orderedRows[$leaf][0]['totalstart'] ?? 0?></th>
                        <th><?=$orderedRows[$leaf][0]['totalgot'] ?? 0?></th>
                        <th><?=$orderedRows[$leaf][0]['totalspent'] ?? 0?></th>
                        <th><?=$orderedRows[$leaf][0]['totalend'] ?? 0?></th>
                    </tr>
                <?php endif; ?>
                <?php if (array_key_exists($leaf, $orderedData)) : ?>
                    <?php foreach ($orderedData[$leaf] as $orderedDatum) : ?>
                        <tr data-level="<?=$level[$leaf]+1?>" class="level_<?=$level[$leaf]+1?>">
                            <td></td>
                            <td><?= $orderedDatum['drug_name'] ?></td>
                            <td><?= $orderedDatum['name'] ?></td>
                            <td><?= $orderedDatum['balanceperiodstart'] ?? 0 ?></td>
                            <td><?= $orderedDatum['got'] ?? 0 ?></td>
                            <td><?= $orderedDatum['spent'] ?? 0 ?></td>
                            <td><?= $orderedDatum['balanceperiodend'] ?? 0 ?></td>
                        </tr>
                    <?php endforeach?>
                <?php endif; ?>
            <?php endforeach?>
        </table>
    </div>
</div>