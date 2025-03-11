<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.03.19
 * Time: 14:36
 */

use app\models\db\GovServices;
use app\models\db\ShiftType;
use app\modules\v2\modules\found\controllers\ModerationController;
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
/** @var $districts array */
/** @var $serviceTypes array */
/** @var $from string */
/** @var $to string */

$time_range = (!empty($fromDate) && !empty($toDate)) ? ($fromDate . ' - ' . $toDate) : Yii::$app->getRequest()->get('time_range', '');
$time_range_create = (!empty($createdFrom) && !empty($createdTo)) ? ($createdFrom . ' - ' . $createdTo) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет по поиску животных';
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" style="text-align: left; ">Дата события:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon" style="width: 35px;">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <?= DateRangePicker::widget([
                                            'name' => 'time_range',
                                            'value' => $time_range,
                                            'attribute' => 'time_range',
                                            'convertFormat' => true,
                                            'startAttribute' => 'fromDate',
                                            'endAttribute' => 'toDate',
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
                                    <label class="control-label" style="text-align: left; ">Дата создания:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon" style="width: 35px;">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <?= DateRangePicker::widget([
                                            'name' => 'time_range_create',
                                            'value' => $time_range_create,
                                            'attribute' => 'time_range_create',
                                            'convertFormat' => true,
                                            'startAttribute' => 'createdFrom',
                                            'endAttribute' => 'createdTo',
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
                                    <label class="control-label" style="text-align: left; ">Тип объявления:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'type',
                                            'value' => \Yii::$app->request->get('type'),
                                            'data' => $adType,
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
                                    <label class="control-label" style="text-align: left; ">Статус объявления:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'status',
                                            'value' => \Yii::$app->request->get('status'),
                                            'data' => $adStatus,
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
                                    <label class="control-label" style="text-align: left; ">Вид животного:</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'species',
                                            'value' => \Yii::$app->request->get('species'),
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
                                    Url::toRoute(['statistics/search-pets-report']),
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
                                        'statistics/search-pets-report/bi-export',
                                        'start' => Yii::$app->getRequest()->get('fromDate', ''),
                                        'end' => Yii::$app->getRequest()->get('to', ''),
                                        'created_start' => Yii::$app->getRequest()->get('createdFrom', ''),
                                        'created_end' => Yii::$app->getRequest()->get('createdTo', ''),
                                        'type' => \Yii::$app->request->get('type', []),
                                        'species' => \Yii::$app->request->get('species', []),
                                        'status' => \Yii::$app->request->get('status', []),
                                        'moderator' => \Yii::$app->request->get('moderator', []),
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
                    <p>Числа над таблицей выводятся по принципу <strong>показано в выборке/всего в системе.<br/></strong>Т.е. строка <strong>"Всего объявлений: 9/11"</strong> означает, что в системе зарегистрировано 11 объявлений, 9 из которых выведены в таблице ниже согласно заданным значениям фильтров. </p>
                    <li><strong>Всего объявлений</strong> - суммарное количество объявлений, зарегистрированных в системе на данный момент, независимо от типа, статуса, даты и др. параметров;</li>
                    <li><strong>Объявлений на модерации</strong> - количество активных на данный момент объявлений, не получивших подтверждение модератора;</li>
                    <li><strong>Одобрено модератором</strong> - количество активных на данный момент объявлений, получивших подтверждение модератора;</li>
                    <li><strong>Закрыто модератором</strong> - количество неактивных на данный момент объявлений, не получивших подтверждение модератора;</li>
                    <li><strong>Архивные</strong> - количество неактивных на данный момент объявлений: закрытые автоматически, пользователем и модератором;</li>
                    <li><strong>Закрыто автоматически</strong> - количество всех объявлений, отклоненных автомодерацией;</li>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box-body table-responsive">
        <div class="form-group col-md-2" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Всего объявлений: <?= $subtotal['total_ads'] . '/' . $countAds ?></label>
        </div>
        <div class="form-group col-md-2" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Объявлений на модерации: <?= $subtotal['total_moderating'] . '/' . $moderatedAds ?></label>
        </div>
        <div class="form-group col-md-2" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Одобрено модератором: <?= $subtotal['total_approved'] . '/' . $approvedAds ?></label>
        </div>
        <div class="form-group col-md-2" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Закрыто модератором: <?= $subtotal['total_rej_mod'] . '/' . $closedAds ?></label>
        </div>
        <div class="form-group col-md-2" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Архивные: <?= $subtotal['total_archived'] . '/' . $archivedAds ?></label>
        </div>
        <div class="form-group col-md-2" style="display: inline-block">
            <label class="control-label" style="text-align: left; ">Закрыто автоматически: <?= $subtotal['total_autocensored'] . '/' . $autoClosedAds ?></label>
        </div>
        <table class="table table-bordered table-hover">
            <thead style="background: white">
            <tr>
                <th style="vertical-align: middle; text-align: center">Номер объявления</th>
                <th style="vertical-align: middle; text-align: center">Тип объявления</th>
                <th style="vertical-align: middle; text-align: center">Фотография</th>
                <th style="vertical-align: middle; text-align: center">Кличка</th>
                <th style="vertical-align: middle; text-align: center">Вид животного</th>
                <th style="vertical-align: middle; text-align: center">Порода</th>
                <th style="vertical-align: middle; text-align: center">Возраст</th>
                <th style="vertical-align: middle; text-align: center">Пол</th>
                <th style="vertical-align: middle; text-align: center">Окрас</th>
                <th style="vertical-align: middle; text-align: center">Чип</th>
                <th style="vertical-align: middle; text-align: center">Дата события</th>

                <!--                <th style="vertical-align: middle; text-align: center">Дата размещения объявления</th>-->
                <!--                <th style="vertical-align: middle; text-align: center">Время размещения объявления</th>-->
                <th style="vertical-align: middle; text-align: center">Автор</th>
                <th style="vertical-align: middle; text-align: center">Телефон</th>
                <th style="vertical-align: middle; text-align: center">Email</th>
                <th style="vertical-align: middle; text-align: center">Адрес пропажи/находки</th>
                <th style="vertical-align: middle; text-align: center">Комментарий</th>
                <th style="vertical-align: middle; text-align: center">Модератор</th>
                <th style="vertical-align: middle; text-align: center">Клеймо/татуировка</th>
                <th style="vertical-align: middle; text-align: center">Идентификатор mos.ru</th>
                <th style="vertical-align: middle; text-align: center">ЕНО</th>
                <th style="vertical-align: middle; text-align: center">Статус объявления</th>
                <th style="vertical-align: middle; text-align: center">Дата модерации</th>
                <th style="vertical-align: middle; text-align: center">Дата создания</th>
                <th style="vertical-align: middle; text-align: center">Дата изменения</th>
                <th style="vertical-align: middle; text-align: center">Дата закрытия</th>
                <th style="vertical-align: middle; text-align: center">Причина закрытия</th>
            </tr>
            </thead>
            <style>
                .tooltip-wrap {
                    position: relative;
                }
                .tooltip-wrap .tooltip-content {
                    display: inline;
                    position: absolute;
                    bottom: 5%;
                    left: 5%;
                    right: 5%;
                    background-color: #fff;
                    padding: .5em;
                }
                .tooltip-wrap:hover .tooltip-content {
                    display: block;
                }
            </style>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <?= $row['id'] ?>
                    </td>
                    <td>
                        <?= $row['ad_type'] ?>
                    </td>
                    <td>
                        <?php
                        if ($row['photo'] && $row['photo'] != '[]') {
                            echo Html::a(Html::encode('Изображение'),
                                Url::to(['statistics/search-pets-report/show-photo', 'id' => $row['id']]));
                        }
                        ?>
                    </td>
                    <td>
                        <?= $row['animal_name'] ?>
                    </td>
                    <td>
                        <?= $row['species_name'] ?>
                    </td>
                    <td>
                        <?= $row['breed_name'] ?>
                    </td>
                    <td>
                        <?= $row['years_age'] ?>
                    </td>
                    <td>
                        <?= $row['gender'] ?>
                    </td>
                    <td>
                        <?= $row['colour'] ?>
                    </td>
                    <td>
                        <?= $row['chip'] ?>
                    </td>
                    <td>
                        <?= $row['date_event'] . ' ' . $row['time_event'] ?>
                    </td>
                    <td>
                        <?= $row['fullname'] ?>
                    </td>
                    <td>
                        <?= $row['phone'] ?>
                    </td>
                    <td>
                        <?= $row['email'] ?>
                    </td>
                    <td>
                        <?= $row['lost_found_address'] ?>
                    </td>
                    <td>
                        <?= $row['notice'] ?>
                    </td>
                    <td>
                        <?= $row['moderator'] ?>
                    </td>
                    <td>
                        <?= $row['stamp'] ?>
                    </td>
                    <td>
                        <?= $row['sso_id'] ?>
                    </td>
                    <td>
                        <?= $row['service_number'] ?>
                    </td>
                    <td>
                        <?= $adStatus[$row['status']] ?? '' ?>
                    </td>
                    <td>
                        <?= $row['verify_at'] ?>
                    </td>
                    <td>
                        <?= $row['created_at'] ?>
                    </td>
                    <td>
                        <?= $row['updated_at'] ?>
                    </td>
                    <td>
                        <?= $row['closed_at'] ?>
                    </td>
                    <td>
                        <?= $row['closed_reason'] ?>
                    </td>
                </tr>
            <?php endforeach ?>
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
