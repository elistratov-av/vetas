<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.03.19
 * Time: 14:36
 */

use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\ServiceTypes;
use app\models\db\ShiftType;
use app\modules\admin\models\Organization;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$this->blocks['content-header'] = 'Отчет по контролю спроса';
$this->registerCss('.nowrap {white-space: nowrap;}');
$organizations = Organization::find()
    ->select('short_name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();
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
$serviceTypes = ServiceTypes::find()
    ->select('name')
    ->orderBy(['name' => SORT_ASC])
    ->indexBy('id')
    ->asArray()
    ->column();

$js = <<<JS
$(function() {
  $("table").stickyTableHeaders();
});
$('.exp').hover(
    function(){
        $(this).css('cursor', 'pointer')});
$('tr').click(function(){
    if ((typeof $(this).data('level') !== "undefined") && (typeof $(this).data('expandable') !== "undefined")) {
        var trLevel = +$(this).data('level');
        var \$el = null;
        $(this).nextAll().each(function(i, e) {
            if(+$(e).data('level') <= trLevel) {
                \$el = $(e);
                return false;
            }
        });
        $(this).nextUntil(\$el).toggle();
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
                                            'За 5 лет' => [
                                                new JsExpression('new Date(new Date().setFullYear(new Date().getFullYear() - 5))'),
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
                                    'clientOptions'=> [
                                        'nonSelectedText' => 'Не выбрано',
                                    ]
                                ])?>
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
                            Url::toRoute(['statistics/services-report']),
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
                                'class' => 'btn btn-primary btn-sm'
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
                    $totalPerType = 0;
                    $totalPerOrg = 0;
                    $total = 0;
                    $totalCountServicesPerType = 0;
                    $totalCountServicesPerOrg = 0;
                    $totalCountServicesPerDist = 0;
                    $totalCountServicesPerArea = 0;
                    $totalSumAmountPerType = 0;
                    $totalSumAmountPerOrg = 0;
                    $totalSumAmountPerDist = 0;
                    $totalSumAmountPerArea = 0;
                    $totalCountServices = 0;
                    $totalSumAmount = 0;
                    $serviceNames = [];
                    $serviceNamesPerArea = [];
                    $serviceNamesPerDist = [];
                ?>
                <?foreach ($data as $datum): ?>
                    <?php if($currentTypeId != $datum['type_id']
                            && $currentTypeId != null
                            ||  ($currentOrgId != $datum['id_organization']
                                || $currentDistId != $datum['id_district']
                                ||  $currentAreaId != $datum['id_area'])
                                && $currentTypeId != null) : ?>
                        <tr style="background: #D6D6D6" data-level="1" data-expandable = "0">
                            <th><?= $serviceTypes[$currentTypeId]?>: итого <?= $totalPerType?></th>
                            <th><?=$totalCountServicesPerType?> </th> <td></td>
                            <th><?=$totalSumAmountPerType?> </th>
                        </tr>
                        <?php
                        $currentTypeId = null;
                        $totalPerType = 0;
                        $totalCountServicesPerType = 0;
                        $totalSumAmountPerType = 0?>
                    <?php endif; ?>
                    <?php if($currentOrgId != $datum['id_organization']
                            && $currentOrgId != null
                            || (($currentDistId != $datum['id_district']
                                ||  $currentAreaId != $datum['id_area']))
                                && $currentOrgId != null) :
                        ?>

                    <tr style="background: #ABABAB" data-level="0" data-expandable = "0">
                        <th>Итого по организации <?= $totalPerOrg?></th>
                        <th><?=$totalCountServicesPerOrg?> </th> <td></td>
                        <th><?=$totalSumAmountPerOrg?> </th>
                    </tr>
                        <?php
                        $currentTypeId = null;
                        $currentOrgId = null;
                        $totalPerOrg = 0;
                        $totalCountServicesPerOrg = 0;
                        $totalSumAmountPerOrg = 0?>
                    <?php endif; ?>
                    <?php if($currentDistId != $datum['id_district'] && $currentDistId != null):?>
                        <tr style="background: #808080" data-level="0" data-expandable = "0">
                            <th>Итого по району <?=count(array_unique($serviceNamesPerDist))?></th>
                            <th><?=$totalCountServicesPerDist?> </th><td></td>
                            <th><?=$totalSumAmountPerDist?> </th>
                        </tr>
                        <?php
                        $currentTypeId = null;
                        $currentOrgId = null;
                        $serviceNamesPerDist = [];
                        $totalCountServicesPerDist = 0;
                        $totalSumAmountPerDist = 0?>
                    <?php endif; ?>

                    <?php if($currentAreaId != $datum['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #656565" data-level="0" data-expandable = "0">
                        <th>Итого по округу <?=count(array_unique($serviceNamesPerArea))?></th>
                        <th><?=$totalCountServicesPerArea?> </th> <td></td>
                        <th><?=$totalSumAmountPerArea?> </th>
                    </tr>
                        <?php
                            $currentTypeId = null;
                            $currentOrgId = null;
                            $serviceNamesPerArea = [];
                            $totalCountServicesPerArea = 0;
                            $totalSumAmountPerArea = 0?>
                    <?php endif?>
                    <?php
                    if($currentAreaId != $datum['id_area']):
                        $currentAreaId = $datum['id_area'];
                    ?>
                    <tr data-level="0" data-expandable = "0">
                        <th colspan="4" style="background: #656565" ><?= $areas[$currentAreaId] ?? 'Округ не указан' ?></th>
                    </tr>
                    <?php endif; ?>
                    <?php
                    if($currentDistId != $datum['id_district']):
                        $currentDistId = $datum['id_district'];
                        ?>
                        <tr data-level="0" data-expandable = "0">
                            <th colspan="4" style="background: #808080" >Район <?=$districts[$currentDistId] ?? 'не указан' ?></th>
                        </tr>
                    <?php endif; ?>
                    <?php
                    if($currentOrgId != $datum['id_organization']):
                        $currentOrgId = $datum['id_organization'];
                    ?>
                    <tr style="background: #ABABAB" data-level="0">
                        <th colspan="4">Организация <?= $organizations[$currentOrgId] ?></th>
                    </tr>
                    <?php endif; ?>
                    <?php
                    if($currentTypeId != $datum['type_id']):
                        $currentTypeId = $datum['type_id'];
                        ?>
                        <tr style="background: #D6D6D6" data-level="1" data-expandable = "1"  class="exp">
                            <th colspan="4"><?= $serviceTypes[$currentTypeId] ?></th>
                        </tr>
                    <?php endif; ?>
                    <tr data-level="2" data-expandable = "1" style="display: none;"  class="exp">
                        <td>
                            <?= $datum['name'] ?>
                        </td>
                        <td>
                            <?= $datum['sum'] ?>
                        </td>
                        <td>
                            <?= $datum['price'] ?>
                        </td>
                        <td>
                            <?= $datum['total_amount'] ?>
                        </td>
                    </tr>

                    <?php
                    $totalCountServicesPerType += $datum['sum'];
                    $totalCountServicesPerOrg += $datum['sum'];
                    $totalCountServicesPerArea += $datum['sum'];
                    $totalCountServicesPerDist += $datum['sum'];
                    $totalCountServices += $datum['sum'];

                    $totalSumAmountPerType += $datum['total_amount'];
                    $totalSumAmountPerOrg += $datum['total_amount'];
                    $totalSumAmountPerDist += $datum['total_amount'];
                    $totalSumAmountPerArea += $datum['total_amount'];
                    $totalSumAmount += $datum['total_amount'];

                    $totalPerType++;
                    $totalPerOrg++;
                    $total++;
                    $serviceNames[] = $datum['name'];
                    $serviceNamesPerArea[] = $datum['name'];
                    $serviceNamesPerDist[] = $datum['name'];
                    ?>
                <?endforeach?>
                <?php if ($totalPerType > 0): ?>
                    <tr style="background: #D6D6D6" data-level="1" data-expandable = "0">
                        <th><?= $serviceTypes[$currentTypeId]?>: итого <?= $totalPerType?></th>
                        <th><?=$totalCountServicesPerType?> </th> <td></td>
                        <th><?=$totalSumAmountPerType?> </th>
                    </tr>
                <?endif;?>
                <?php if ($totalPerOrg > 0): ?>
                    <tr style="background: #ABABAB" data-level="0" data-expandable = "0">
                        <th>Итого по организации <?= $totalPerOrg?></th>
                        <th><?=$totalCountServicesPerOrg?> </th> <td></td>
                        <th><?=$totalSumAmountPerOrg?> </th>
                    </tr>
                <?endif;?>
                <?php if (count($serviceNamesPerDist) > 0): ?>
                    <tr style="background: #808080" data-level="0" data-expandable = "0">
                        <th>Итого по району <? $serviceNamesPerDist = array_unique($serviceNamesPerDist);
                            echo count($serviceNamesPerDist);?></th>
                        <th><?=$totalCountServicesPerDist?> </th> <td></td>
                        <th><?=$totalSumAmountPerDist?> </th>
                    </tr>
                <?endif;?>
                <?php if (count($serviceNamesPerArea) > 0): ?>
                    <tr style="background: #656565" data-level="0" data-expandable = "0">
                        <th>Итого по округу <? $serviceNamesPerArea = array_unique($serviceNamesPerArea);
                            echo count($serviceNamesPerArea);?></th>
                        <th><?=$totalCountServicesPerArea?> </th> <td></td>
                        <th><?=$totalSumAmountPerArea?> </th>
                    </tr>
                <?endif;?>
                <?php if (count($serviceNames) > 0): ?>
                    <tr style="background: #555555" data-level="0" data-expandable = "0">
                        <th>Всего <?=count(array_unique($serviceNames))?></th>
                        <th><?=$totalCountServices?> </th> <td></td>
                        <th><?=$totalSumAmount?> </th>
                    </tr>
                <?endif;?>
            </table>
        </div>
    </div>