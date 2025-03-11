<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.19
 * Time: 17:49
 */

use app\models\db\RegExpireReasons;
use app\models\db\Species;
use app\modules\admin\models\Organization;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$this->blocks['content-header'] = 'Отчет о снятии с учета';
$this->registerCss('.nowrap {white-space: nowrap;}');
$reasons = RegExpireReasons::find()
    ->select('name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();
$organizations = Organization::find()
    ->select('short_name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();
$species = Species::find()
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
                                'name' => 'id_species',
                                'value' => \Yii::$app->request->get('id_species'),
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" style="text-align: left; ">По причине:</label>
                        <div class="input-group">
                            <?= MultiSelect::widget([
                                'name' => 'id_reg_expire_reason',
                                'attribute' => 'id_reg_expire_reason',
                                'value' => \Yii::$app->request->get('id_reg_expire_reason'),
                                'data' => $reasons,
                                'options' => [
                                    'multiple' => true,
                                ],
                                'clientOptions'=> [
                                    'nonSelectedText' => 'Не выбрано',
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
                        Url::toRoute(['statistics/expire-pets-report']),
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
                            'statistics/expire-pets-report/export',
                            'from' => Yii::$app->getRequest()->get('from', ''),
                            'to' => Yii::$app->getRequest()->get('to', ''),
                            'id_reg_organization' => \Yii::$app->request->get('id_reg_organization', []),
                            'id_species' => \Yii::$app->request->get('id_species', []),
                            'id_reg_expire_reason' => \Yii::$app->request->get('id_reg_expire_reason', []),
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
                <th>Кличка</th>
                <th>Тип метки<br><span style="font-size: smaller">(основной идентификатор животного)</span></th>
                <th>Значение метки</th>
                <th>Имя владельца<br><span style="font-size: smaller">(ФЛ, ЮЛ)</span></th>
                <th>Адрес владельца<br><span style="font-size: smaller">(ФЛ, ЮЛ)</span></th>
                <th>Телефон владельца<br><span style="font-size: smaller">(ФЛ, ЮЛ)</span></th>
                <th>Дата снятия с учета</th>
                <th>Причина снятия с учета</th>
            </tr>
            </thead>
            <?php
            $currentOrgId = null;
            $currentSpecId = null;
            $totalPerSpec = 0;
            $totalPerOrg = 0;
            $total = 0;
            ?>
            <?foreach ($data as $datum): ?>
                <?php if ($currentSpecId != $datum['id_species']
                    && $currentSpecId != null
                    ||  (($currentOrgId != $datum['id_reg_organization']))
                    && $currentSpecId != null): ?>
                    <tr style="background: #eeeeee">
                        <th colspan="8">Итого по виду <?= $totalPerSpec?></th>
                    </tr>
                    <?php $totalPerSpec = 0;
                    $currentSpecId = null ?>
                <?php endif; ?>
                <?php if($currentOrgId != $datum['id_reg_organization'] && $currentOrgId != null): ?>
                    <tr style="background: #cdcdcd">
                        <th  colspan="8">Итого по организации <?= $totalPerOrg?></th>
                    </tr>
                    <?php $totalPerOrg = 0;
                    $currentSpecId = null?>
                <?php endif; ?>
                <?php
                if($currentOrgId != $datum['id_reg_organization']):
                    $currentOrgId = $datum['id_reg_organization']; ?>
                    <tr>
                        <th colspan="8" style="background: #cdcdcd">Организация <?=$organizations[$currentOrgId] ?? 'не указана' ?></th>
                    </tr>
                <?php endif ?>
                <?php
                if($currentSpecId != $datum['id_species']):
                    $currentSpecId = $datum['id_species']; ?>
                    <tr>
                        <th colspan="8" style="background: #eeeeee"><?= $species[$currentSpecId] ?? 'Вид не указан' ?></th>
                    </tr>
                <?php endif ?>
                <tr>
                    <td>
                        <?= $datum['pet_name'] ?>
                    </td>
                    <td>
                        <?= $datum['ident_name'] ?>
                    </td>
                    <td>
                        <?= $datum['identification_code'] ?>
                    </td>
                    <td>
                        <?= $datum['fullname'] ?>
                    </td>
                    <td>
                        <?= $datum['full_address'] ?>
                    </td>
                    <td>
                        <?= $datum['contact_name'] ?>
                    </td>
                    <td>
                        <?= $datum['reg_expire_date'] ?>
                    </td>
                    <td>
                        <?= $datum['reason_name'] ?>
                    </td>
                </tr>
                <?php
                $totalPerSpec++;
                $totalPerOrg++;
                $total++;
                ?>
            <?endforeach?>
            <tr style="background: #eeeeee">
                <th colspan="8">Итого по виду <?= $totalPerSpec?></th>
            </tr>
            <tr style="background: #cdcdcd">
                <th colspan="8">Итого по организации <?= $totalPerOrg?></th>
            </tr>
            <tr style="background: #686868">
                <th colspan="8">Всего <?=$total?></th>
            </tr>
        </table>
    </div>
</div>