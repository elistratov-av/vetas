<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.06.19
 * Time: 16:12
 */

use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\ShiftType;
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

$this->blocks['content-header'] = 'Детальный отчет по ветеринарным услугам';
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
                                    <label class="control-label" style="text-align: left; ">Канал записи:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'channel',
                                            'value' => \Yii::$app->request->get('channel'),
                                            'data' => ShiftType::find()
                                                ->select(new Expression('case when id = 1 then \'Направление\' else description end'))
                                                ->andWhere(new \yii\db\Expression('(overlap_category = \'TOP_LEVEL\' and idle = true) = false and overlap_category != \'IDLE\''))
                                                ->orderBy(['description' => SORT_ASC])
                                                ->indexBy('id')
                                                ->asArray()
                                                ->column(),
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
                                            'name' => 'spec_name',
                                            'value' => \Yii::$app->request->get('spec_name'),
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
                            <div class="col-md-6" style="padding-right: 0;">
                                <?= Html::a(
                                    'Сбросить',
                                    Url::toRoute(['statistics/full-services-vol2-report']),
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
                                        'statistics/full-services-vol2-report/export',
                                        'from' => Yii::$app->getRequest()->get('from', ''),
                                        'to' => Yii::$app->getRequest()->get('to', ''),
                                        'id_area' => \Yii::$app->request->get('id_area', []),
                                        'id_district' => \Yii::$app->request->get('id_district', []),
                                        'visit_types' => \Yii::$app->request->get('visit_types', []),
                                        'channel' => \Yii::$app->request->get('channel', []),
                                        'id_organization' => \Yii::$app->request->get('id_organization', []),
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
                    <li><strong>Всего услуг</strong> - суммарное количество услуг в приемах, зарегистрированных в системе на данный момент, независимо от статуса, даты и др. параметров;</li>
                    <li><strong>Новых услуг</strong> - суммарное количество услуг в приемах, зарегистрированных в системе на данный момент и находящихся в статусе <strong>"Новый"</strong>;</li>
                    <li><strong>Услуг в работе</strong> - суммарное количество услуг в приемах, зарегистрированных в системе на данный момент и находящихся в статусе <strong>"В работе"</strong>;</li>
                    <li><strong>Канал записи</strong> - способ, который был выбран для записи на прием;</li>
                    <li><strong>Всего</strong> - суммарное количество услуг, оказанных в рамках приемов, проведенных на заданном временном промежутке. В данную категорию включаются приемы независимо от их статуса;</li>
                    <li><strong>Завершено</strong> - суммарное количество услуг, оказанных в рамках завершенных приемов, проведенных на заданном временном промежутке;</li>
                    <li><strong>Отменено</strong> - суммарное количество услуг, оказанных в рамках отмененных приемов, проведенных на заданном временном промежутке;</li>
                    <li><strong>Новый</strong> - суммарное количество услуг, оказанных в рамках приемов в статусе "Новый", проведенных на заданном временном промежутке;</li>
                    <li><strong>В работе</strong> - суммарное количество услуг, оказанных в рамках приемов в статусе "В работе", проведенных на заданном временном промежутке;</li>
                    <li><strong>К переносу</strong> - суммарное количество услуг, оказанных в рамках приемов в статусе "К переносу", проведенных на заданном временном промежутке;</li>
                    <li><strong>Изменено</strong> - суммарное количество услуг, оказанных в рамках приемов в статусе "Изменено", проведенных на заданном временном промежутке;</li>
                    <li><strong>Закрыто по тайм-ауту</strong> - суммарное количество услуг, оказанных в рамках приемов в статусе "Пациент не явился", проведенных на заданном временном промежутке;</li>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box-body table-responsive">
        <div class="form-group col-md-3" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Всего услуг: <?= $subtotals['total_services'] ?></label>
        </div>
        <div class="form-group col-md-3" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Новых услуг: <?= $subtotals['total_n'] ?></label>
        </div>
        <div class="form-group col-md-3" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Услуг в работе: <?= $subtotals['total_w'] ?></label>
        </div>
        <table class="table table-bordered table-hover">
            <thead style="background: white">
                <tr>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Год</th>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Месяц</th>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Административный округ</th>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Организация</th>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Название услуги</th>
                    <th rowspan="2" style="text-align: center; vertical-align: middle">Канал записи</th>
                    <th colspan="8" style="text-align: center; vertical-align: middle">Статус записей</th>
                </tr>
                <tr>
                    <th style="text-align: center; vertical-align: middle">Всего</th>
                    <th style="text-align: center; vertical-align: middle">Завершено</th>
                    <th style="text-align: center; vertical-align: middle">Отменено</th>
                    <th style="text-align: center; vertical-align: middle">Новый</th>
                    <th style="text-align: center; vertical-align: middle">В работе</th>
                    <th style="text-align: center; vertical-align: middle">К переносу</th>
                    <th style="text-align: center; vertical-align: middle">Изменено</th>
                    <th style="text-align: center; vertical-align: middle">Закрыто по тайм-ауту</th>
                </tr>
            </thead>
            <?php
            $totalServices = 0;
            $totalF = 0;
            $totalA = 0;
            $totalN = 0;
            $totalW = 0;
            $totalT = 0;
            $totalC = 0;
            $totalD = 0;
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
            <?php foreach ($rows as $row):
//                var_dump($row); die();?>
                <tr>
                    <td>
                        <?= substr($row['date'], 0, 4) ?>
                    </td>
                    <td>
                        <?= $months[substr($row['date'], -2)] ?>
                    </td>
                    <td>
                        <?= $row['area_name'] ?>
                    </td>
                    <td>
                        <?= $row['short_name'] ?>
                    </td>
                    <td>
                        <?= $row['service_name'] ?>
                    </td>
                    <td>
                        <?= $row['channel'] ?>
                    </td>
                    <td>
                        <?= $row['total_services'] ?>
                    </td>
                    <td>
                        <?= $row['total_f'] ?>
                    </td>
                    <td>
                        <?= $row['total_a'] ?>
                    </td>
                    <td>
                        <?= $row['total_n'] ?>
                    </td>
                    <td>
                        <?= $row['total_w'] ?>
                    </td>
                    <td>
                        <?= $row['total_t'] ?>
                    </td>
                    <td>
                        <?= $row['total_c'] ?>
                    </td>
                    <td>
                        <?= $row['total_d'] ?>
                    </td>
                </tr>

                <?php
                    $totalServices += $row['total_services'];
                    $totalF += $row['total_f'];
                    $totalA += $row['total_a'];
                    $totalN += $row['total_n'];
                    $totalW += $row['total_w'];
                    $totalT += $row['total_t'];
                    $totalC += $row['total_c'];
                    $totalD += $row['total_d'];
                ?>
            <?php endforeach;?>
            <?php if ($pagination->getPage() == ($pagination->getPageCount() - 1)): ?>
                <tr style="background: #6892d2">
                    <th>Всего</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th><?=$subtotals['total_services']?> </th>
                    <th><?=$subtotals['total_f']?> </th>
                    <th><?=$subtotals['total_a']?> </th>
                    <th><?=$subtotals['total_n']?> </th>
                    <th><?=$subtotals['total_w']?> </th>
                    <th><?=$subtotals['total_t']?> </th>
                    <th><?=$subtotals['total_c']?> </th>
                    <th><?=$subtotals['total_d']?> </th>
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
