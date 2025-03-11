<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.06.19
 * Time: 11:32
 */

use app\models\db\Organizations;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->blocks['content-header'] = 'Отчет по загрузке мощностей';
$this->registerCss('.nowrap {white-space: nowrap;}');
$organizations = Organizations::find()
    ->select('short_name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();
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
                        Url::toRoute(['statistics/capacity-report']),
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
                            'statistics/capacity-report/export',
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
                <th>Оборудование на балансе</th>
                <th>Использовано раз</th>
            </tr>
            </thead>
            <?php
            $currentOrgId = null;
            $totalPerOrg = 0;
            $total = 0;
            ?>
            <?foreach ($data as $datum): ?>
                <?php if($currentOrgId != $datum['id_organization'] && $currentOrgId != null) : ?>
                    <tr style="background: #ABABAB">
                        <th>Итого по организации</th>
                        <th><?=$totalPerOrg?> </th>
                    </tr>
                    <?php
                    $currentOrgId = null;
                    $totalPerOrg = 0 ?>
                <?php endif; ?>
                <?php
                if($currentOrgId != $datum['id_organization'] || $currentOrgId == null):
                    $currentOrgId = $datum['id_organization']; ?>
                    <tr style="background: #ABABAB">
                        <th colspan="2">Организация <?= $organizations[$currentOrgId] ?? 'не найдена' ?></th>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <?= $datum['name'] ?>
                    </td>
                    <td>
                        <?= $datum['sum'] ?>
                    </td>
                </tr>

                <?php
                $totalPerOrg += $datum['sum'];
                $total += $datum['sum'];
                ?>
            <?endforeach?>
            <?php if ($totalPerOrg > 0): ?>
                <tr style="background: #ABABAB">
                    <th>Итого по организации </th>
                    <th><?=$totalPerOrg?> </th>
                </tr>
            <?endif;?>
            <?php if ($total > 0): ?>
                <tr style="background: #777777">
                    <th>Всего</th>
                    <th> <?=$total?> </th>
                </tr>
            <?endif;?>
        </table>
    </div>
</div>