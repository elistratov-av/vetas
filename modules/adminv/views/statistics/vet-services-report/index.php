<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.03.19
 * Time: 14:36
 */

use app\models\db\GovServices;
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
/** @var $visit_types array */
/** @var $districts array */
/** @var $serviceTypes array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет об оказании ветеринарных услуг';
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
                                    <label class="col-lg control-label" style="text-align: left; margin-left: 15px;">Внимание! Выбор дат из разных годов повлечет за собой вывод некорректных данных с разными ценами.</label>
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
                                            'data' => \app\models\db\Users::find()
                                                ->select('fullname')
                                                ->orderBy(['fullname' => SORT_ASC])
                                                ->indexBy('id')
                                                ->asArray()
                                                ->column(),
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" style="text-align: left; ">Услуги:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'id_service',
                                            'value' => \Yii::$app->request->get('id_service'),
                                            'data' => GovServices::find()
                                                ->select('name')
                                                ->orderBy(['name' => SORT_ASC])
                                                ->indexBy('id')
                                                ->asArray()
                                                ->column(),
                                            'options' => [
                                                'multiple' => true,
                                            ],
                                            'clientOptions' => [
                                                'nonSelectedText' => 'Не выбрано',
                                                'enableCaseInsensitiveFiltering' => true,
                                                'includeResetOption' => true,
                                                'resetText' => 'Сбросить',
                                                'maxHeight' => 450,
                                                'buttonWidth' => 145
                                            ],
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" style="text-align: left; ">Каналы записи:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'channel',
                                            'value' => \Yii::$app->request->get('channel'),
                                            'data' => ShiftType::find()
                                                ->select("(case when id = 1 then 'Направление' else description end) as channel_name")
                                                ->andWhere(new \yii\db\Expression('(overlap_category = \'TOP_LEVEL\' and idle = true) = false and overlap_category != \'IDLE\''))
                                                ->orderBy(['channel_name' => SORT_ASC])
                                                ->indexBy('id')
                                                ->asArray()
                                                ->column(),
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
                            <div class="col-md-6" style="padding-right: 0;">
                                <?= Html::a(
                                    'Сбросить',
                                    Url::toRoute(['statistics/vet-services-report']),
                                    [
                                        'class' => 'btn btn-default btn-sm',
                                    ]
                                ) ?>
                                <?= Html::input('submit', 'submit', 'Показать', [
                                    'class' => 'btn btn-primary btn-sm',
                                ]); ?>
                                <?= Html::a(
                                    '<span class="glyphicon glyphicon-download"></span> Сохранить XLS BI',
                                    Url::toRoute([
                                        'statistics/vet-services-report/bi-export',
                                        'from' => Yii::$app->getRequest()->get('from', ''),
                                        'to' => Yii::$app->getRequest()->get('to', ''),
                                        'channel' => \Yii::$app->request->get('channel', []),
                                        'type_id' => \Yii::$app->request->get('type_id', []),
                                        'id_service' => \Yii::$app->request->get('id_service', []),
                                        'id_organization' => \Yii::$app->request->get('id_organization', []),
                                        'specialists' => \Yii::$app->request->get('specialists', []),
                                    ]),
                                    [
                                        'class' => 'btn btn-primary btn-sm',
                                        'target' => '_blank',
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </td>
            <td style="text-align: justify">
                <ul>
                    <p>В данном отчете считаются услуги, оказанные только в рамках завершенных приемов. Отбор услуг в выборку осуществляется по значению следующих полей в порядке убывания приоритета:
                        <ul>
                        <li>Фактическая дата начала приема;</li>
                        <li>Дата, на которую была подана заявка на прием;</li>
                        <li>Дата создания заявки на прием.</li>
                    </ul>
                    </p>
                    <li><strong>Тарифы с НДС</strong> - цена услуги в прайслисте;</li>
                    <li><strong>Количество платных ветеринарных услуг</strong> - суммарное количество услуг, у которых цена после применения всех скидок и надбавок не равно нулю;</li>
                    <li><strong>Общая стоимость платных услуг</strong> - суммарная стоимость платных услуг. Значение данной колонки рассчитывается как сумма произведений цен с учетом скидок и надбавок на их количество;</li>
                    <li><strong>Вакцинации Рабиканом</strong> -  количество вакцинаций вакциной Рабикан в заданный период;</li>
                    <li><strong>Количество услуг со 100% скидкой</strong> - суммарное количество оказанных услуг, цена которых либо в прайслисте нулевая, либо стала таковой после применения скидок и надбавок (без учета льготных категорий населения);</li>
                    <li><strong>Инвалиды по зрению, Ветераны ВОВ, Инвалиды 1 группы, Сироты и дети без попечителей в возрасте до 23 лет, Многодетные семьи, Ветераны труда</strong> - суммарное количество оказанных услуг в рамках приемов, у которых указана соответствующая льгота;</li>
                    <li><strong>Форма 1</strong> - суммарное количество использованных расходников в рамках оказанных услуг. Учитываются все бланки, свидетельства, сертификаты, в названии которых встречается регистронезависимая комбинация символов "ф1";</li>
                    <li><strong>Форма 4</strong> - суммарное количество использованных расходников в рамках оказанных услуг. Учитываются все бланки, свидетельства, сертификаты, в названии которых встречается регистронезависимая комбинация символов "ф4";</li>
                    <li><strong>Форма ТС</strong> - суммарное количество использованных расходников в рамках оказанных услуг. Учитываются все бланки, свидетельства, сертификаты, в названии которых встречается регистронезависимая комбинация символов "ТСф1";</li>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box-body table-responsive">
    <table class="table table-bordered table-hover">
        <thead style="background: white">
        <tr>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Адм. округ</th>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Организация</th>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Наименование ветеринарных услуг</th>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Тарифы с НДС</th>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Количество платных ветеринарных услуг</th>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Общая стоимость платных услуг</th>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Вакцинации Рабиканом</th>
            <th rowspan="3" style="vertical-align: middle; text-align: center">Количество услуг со 100% скидкой</th>
            <th colspan="9" style="vertical-align: middle; text-align: center">Количество ветеринарных услуг, оказанных в рамках госзадания</th>
        </tr>
        <tr>
            <th rowspan="2" style="vertical-align: middle; text-align: center">Инвалиды по зрению</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center">Ветераны ВОВ</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center">Инвалиды 1 группы</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center">Сироты и дети без попечителей в возрасте до 23 лет</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center">Многодетные семьи</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center">Ветераны труда</th>
            <th colspan="3" style="vertical-align: middle; text-align: center">Оформленные ВСД</th>
        </tr>
        <tr>
            <th>Форма 1</th>
            <th>Форма 4</th>
            <th>Форма ТС</th>
        </tr>
        </thead>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td>
                    <?= $row['area_name'] ?>
                </td>
                <td>
                    <?= $row['short_name'] ?>
                </td>
                <td>
                    <?= $row['name'] ?>
                </td>
                <td>
                    <?= $row['price'] ?>
                </td>
                <td>
                    <?= $row['total_paid'] ?>
                </td>
                <td>
                    <?= $row['total_amount'] ?>
                </td>
                <td>
                    <?= $row['total_rabies'] ?>
                </td>
                <td>
                    <?= $row['total_free'] ?>
                </td>
                <td>
                    <?= $row['total_blind'] ?>
                </td>
                <td>
                    <?= $row['total_veteran'] ?>
                </td>
                <td>
                    <?= $row['total_disabled'] ?>
                </td>
                <td>
                    <?= $row['total_orphan'] ?>
                </td>
                <td>
                    <?= $row['total_large_family'] ?>
                </td>
                <td>
                    <?= $row['total_veteran_of_labour'] ?>
                </td>
                <td>
                    <?= $row['total_f1'] ?? 0 ?>
                </td>
                <td>
                    <?= $row['total_f4'] ?? 0 ?>
                </td>
                <td>
                    <?= $row['total_ts'] ?? 0 ?>
                </td>
            </tr>
        <?php endforeach ?>
        <!-- это последняя страница? -->
        <?php if ($pagination->getPage() == ($pagination->getPageCount() - 1)): ?>
            <tr style="background: #3268bc">
                <th>Всего </th>
                <th></th>
                <th></th>
                <th></th>
                <th><?= $subtotals['total']['total_paid']?></th>
                <th><?= $subtotals['total']['total_amount']?></th>
                <th><?= $subtotals['total']['total_rabies']?></th>
                <th><?= $subtotals['total']['total_free']?></th>
                <th><?= $subtotals['total']['total_blind']?></th>
                <th><?= $subtotals['total']['total_veteran']?></th>
                <th><?= $subtotals['total']['total_disabled']?></th>
                <th><?= $subtotals['total']['total_orphan']?></th>
                <th><?= $subtotals['total']['total_large_family']?></th>
                <th><?= $subtotals['total']['total_veteran_of_labour']?></th>
                <th><?= $subtotals['total']['total_f1']?></th>
                <th><?= $subtotals['total']['total_f4']?></th>
                <th><?= $subtotals['total']['total_ts']?></th>
            </tr>
        <?php endif; ?>

    </table>
    </div>
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
