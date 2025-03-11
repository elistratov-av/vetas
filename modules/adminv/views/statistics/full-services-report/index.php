<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.06.19
 * Time: 16:12
 */

use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\Visits;
use app\modules\admin\helpers\VisitStatusHelper;
use kartik\daterange\DateRangePicker;
use roboapp\multiselect\MultiSelect;
use yii\db\Expression;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\LinkPager;

/** @var $this \yii\web\View */
/** @var $data array */
/** @var $organizations array */
/** @var $from string */
/** @var $to string */
/** @var $visit_types array */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Общий отчет по ветеринарным услугам';
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
            <td style="width: 46.4%;">
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
                                    <label class="control-label" style="text-align: left; ">Специалист(ы):</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'specialists',
                                            'value' => \Yii::$app->request->get('specialists'),
                                            'data' => $specialists,
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
                                    <label class="control-label" style="text-align: left; ">Тип приёма:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'visit_types',
                                            'value' => \Yii::$app->request->get('visit_types'),
                                            'data' => $visit_types,
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
                                    <label class="control-label" style="text-align: left; ">Услуга(и):</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'services',
                                            'value' => \Yii::$app->request->get('services'),
                                            'data' => $services,
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
                                    <label class="control-label" style="text-align: left; ">Статус заявки:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'status',
                                            'value' => \Yii::$app->request->get('status'),
                                            'data' => VisitStatusHelper::statusList(),
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
                                    Url::toRoute(['statistics/full-services-report']),
                                    [
                                        'class' => 'btn btn-default btn-sm'
                                    ]
                                );
                                ?>
                                <?= Html::input('submit','submit', 'Показать', [
                                    'class' => 'btn btn-primary btn-sm'
                                ]); ?>
                                <?= Html::a(
                                    '<span class="glyphicon glyphicon-download"></span> Сохранить XLS',
                                    Url::toRoute([
                                        'statistics/full-services-report/export',
                                        'from' => Yii::$app->getRequest()->get('from', ''),
                                        'to' => Yii::$app->getRequest()->get('to', ''),
                                        'id_area' => Yii::$app->request->get('id_area', []),
                                        'id_district' => Yii::$app->request->get('id_district', []),
                                        'visit_types' => \Yii::$app->request->get('visit_types', []),
                                        'status' => Yii::$app->request->get('status', []),
                                        'id_organization' => Yii::$app->request->get('id_organization', []),
                                        'specialists' => Yii::$app->request->get('specialists', []),
                                        'services' => Yii::$app->request->get('services', []),
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
                    <p>Отбор услуг в выборку осуществляется по значению следующих полей в порядке убывания приоритета:
                    <ul>
                        <li>Фактическая дата начала приема;</li>
                        <li>Дата, на которую была подана заявка на прием;</li>
                        <li>Дата создания заявки на прием.</li>
                    </ul>
                    </p>
                    <li><strong>Всего заявок</strong> - суммарное количество заявок и приемов, зарегистрированных в системе на данный момент, независимо от статуса, даты и др. параметров;</li>
                    <li><strong>Новых заявок</strong> - суммарное количество заявок на приемы, зарегистрированных в системе на данный момент и находящихся в статусе <strong>"Новый"</strong>;</li>
                    <li><strong>Заявок в работе</strong> - суммарное количество заявок на приемы, зарегистрированных в системе на данный момент и находящихся в статусе <strong>"В работе"</strong>;</li>
                    <li><strong>Запись по ЖО</strong> - суммарное количество услуг, оказанных в рамках приемов по живой очереди и проведенных на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса;</li>
                    <li><strong>Запись по телефону</strong> - суммарное количество услуг, оказанных в рамках приемов, на которые записались по телефону, и проведенных на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса;</li>
                    <li><strong>Запись по направлению</strong> - суммарное количество услуг, оказанных в рамках приемов по направлению и проведенных на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса;</li>
                    <li><strong>Запись на портале mos.ru</strong> - суммарное количество услуг, оказанных в рамках приемов, на которые записались на портале mos.ru, и проведенных на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса;</li>
                    <li><strong>Запись в мобильном приложении</strong> - суммарное количество услуг, оказанных в рамках приемов, на которые записались через мобильное приложение, и проведенных на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса;</li>
                    <li><strong>Ветеринарная помощь на дому</strong> - суммарное количество услуг, оказанных в рамках приемов, оказанных как неотложные, и проведенных на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса;</li>
                    <li><strong>Вызов на дом (mos.ru)</strong> - суммарное количество услуг, оказанных в рамках приемов, на которые записались на портале mos.ru, и проведенных дома у пациента на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса.</li>
                    <li><strong>Прививочные пункты</strong> - суммарное количество услуг, оказанных в рамках приемов, оказанные на прививочных пунктах на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса.</li>
                    <li><strong>Обходы</strong> - суммарное количество услуг, оказанных в рамках обходов на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса.</li>
                    <li><strong>Приюты</strong> - суммарное количество услуг, оказанных в рамках приемов, проведенных в приюте на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса.</li>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box-body table-responsive">
        <div class="form-group col-md-3" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Всего заявок: <?= $countVisits ?></label>
        </div>
        <div class="form-group col-md-3" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Новых заявок: <?= $countNew ?></label>
        </div>
        <div class="form-group col-md-3" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Заявок в работе: <?= $countWork ?></label>
        </div>
        <table class="table table-bordered table-hover">
            <thead style="background: white">
                <tr>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Год</th>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Месяц</th>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Название услуги</th>
                    <th colspan="10" style="text-align: center; vertical-align: middle">Количество записей</th>
                </tr>
                <tr>
                    <th style="text-align: center; vertical-align: middle">Запись по ЖО</th>
                    <th style="text-align: center; vertical-align: middle">Запись по телефону</th>
                    <th style="text-align: center; vertical-align: middle">Запись по направлению</th>
                    <th style="text-align: center; vertical-align: middle">Запись на портале mos.ru</th>
                    <th style="text-align: center; vertical-align: middle">Запись в мобильном приложении</th>
                    <th style="text-align: center; vertical-align: middle">Ветеринарная помощь на дому</th>
                    <th style="text-align: center; vertical-align: middle">Вызов на дом (mos.ru)</th>
                    <th style="text-align: center; vertical-align: middle">Прививочные пункты</th>
                    <th style="text-align: center; vertical-align: middle">Обходы</th>
                    <th style="text-align: center; vertical-align: middle">Приюты</th>
                </tr>
            </thead>
            <?php
            $months = [
                    '01' => 'Январь',
                    '02' => 'Февраль',
                    '03' => 'Март',
                    '04' => 'Апрель',
                    '05' => 'Май',
                    '06' => 'Июнь',
                    '07' => 'Июль',
                    '08' => 'Август',
                    '09' => 'Сентябрь',
                    '10' => 'Октябрь',
                    '11' => 'Ноябрь',
                    '12' => 'Декабрь',
            ];
            ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <?= substr($row['date'], 0, 4) ?>
                    </td>
                    <td>
                        <?= $months[substr($row['date'], -2)] ?>
                    </td>
                     <td>
                        <?= $row['service_name'] ?>
                    </td>
                     <td>
                        <?= $row['total_lq'] ?>
                    </td>
                     <td>
                        <?= $row['total_phone'] ?>
                    </td>
                     <td>
                        <?= $row['total_workday'] ?>
                    </td>
                     <td>
                        <?= $row['total_mosru'] ?>
                    </td>
                    <td>
                        <?= $row['total_mpgu'] ?>
                    </td>
                    <td>
                        <?= $row['total_ambulance'] ?>
                    </td>
                    <td>
                        <?= $row['total_home_mosru'] ?>
                    </td>
                    <td>
                        <?= $row['total_vacc_station'] ?>
                    </td>
                    <td>
                        <?= $row['total_detour'] ?>
                    </td>
                    <td>
                        <?= $row['total_shelter'] ?>
                    </td>
                </tr>
            <?php endforeach;?>
            <?php if ($pagination->getPage() == ($pagination->getPageCount() - 1)): ?>
                <tr style="background: #6892d2">
                    <th>Всего</th>
                    <th></th>
                    <th></th>
                    <th><?=$subtotals['total_lq']?> </th>
                    <th><?=$subtotals['total_phone']?> </th>
                    <th><?=$subtotals['total_workday']?> </th>
                    <th><?=$subtotals['total_mosru']?> </th>
                    <th><?=$subtotals['total_mpgu']?> </th>
                    <th><?=$subtotals['total_ambulance']?> </th>
                    <th><?=$subtotals['total_home_mosru']?> </th>
                    <th><?=$subtotals['total_vacc_station']?> </th>
                    <th><?=$subtotals['total_detour']?> </th>
                    <th><?=$subtotals['total_shelter']?> </th>
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
