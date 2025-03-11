<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.07.19
 * Time: 11:03
 * @var $areasList
 * @var $districtsList
 * @var $organizationsList
 * @var $speciesList
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
/** @var $from string */
/** @var $to string */
/** @var $btiCityAreaCodeOptions array */

$time_range = (!empty($from) && !empty($to)) ? ($from . ' - ' . $to) : Yii::$app->getRequest()->get('time_range', '');

$this->blocks['content-header'] = 'Отчет по невакцинированным животным';
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
$area_change = <<<JS
$("body").delegate('#area_select', 'change', function () {
    $(this).next().val(11111);
});
JS;

$this->registerJs($area_change, View::POS_READY);
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
                                    <label class="control-label" style="text-align: left; ">Район(ы):</label>
                                    <div class="input-group">
                                        <?= MultiSelect::widget([
                                            'name' => 'bti_city_area_code',
                                            'value' => \Yii::$app->request->get('bti_city_area_code'),
                                            'data' => $btiCityAreaCodeOptions,
                                            'options' => [
                                                'multiple' => true,
                                            ],
                                            'clientOptions' => [
                                                'nonSelectedText' => 'Не выбрано',
                                                'enableCaseInsensitiveFiltering' => true,
                                                'enableClickableOptGroups' => true,
                                                'enableCollapsibleOptGroups' => true,
                                                'maxHeight' => 450,
                                                'numberDisplayed' => 3,
                                                'nSelectedText' => 'выбрано'
                                            ]
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="padding-right: 0;">
                                <?= Html::a(
                                    'Сбросить',
                                    Url::toRoute(['statistics/unvacc-pets-report', 'id_area' => 3]),
                                    [
                                        'class' => 'btn btn-default btn-sm'
                                    ]
                                ) ?>
                                <?= Html::input('submit', 'submit', 'Показать', [
                                    'class' => 'btn btn-primary btn-sm'
                                ]); ?>
                                <!--<?= Html::a(
                                    '<span class="glyphicon glyphicon-download"></span> Сохранить XLS BI',
                                    Url::toRoute([
                                        'statistics/unvacc-pets-report/bi-export',
                                        'id_area' => \Yii::$app->request->get('id_area', []),
                                        'id_district' => \Yii::$app->request->get('id_district', []),
                                    ]),
                                    [
                                        'class' => 'btn btn-primary btn-sm',
                                        'target' => '_blank',
                                    ]
                                ) ?> -->
                                <?= Html::a(
                                    '<span class="glyphicon glyphicon-download"></span> Сохранить XLS',
                                    Url::toRoute([
                                        'statistics/unvacc-pets-report/export',
                                        'bti_city_area_code' => \Yii::$app->request->get('bti_city_area_code'),
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
                    <p>В данном отчете выводится список кошек и собак без действующей вакцинации от бешенства и лептоспироза.
                        Округ и район определяются по полному адресу в соответствии со справочником БТИ.</p>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead style="background: white">
            <tr>
                <th rowspan="2">№ п\п</th>
                <th rowspan="2">Административный округ</th>
                <th rowspan="2">Район</th>
                <th rowspan="2">ФИО владельца</th>
                <th rowspan="2">Адрес регистрации</th>
                <th rowspan="2">Адрес фактического проживания</th>
                <th rowspan="2">Телефон</th>
                <th rowspan="2">E-mail</th>
                <th rowspan="2">Вид животного</th>
                <th rowspan="2">Порода</th>
                <th rowspan="2">Пол</th>
                <th rowspan="2">Идентификационный номер</th>
                <th rowspan="2">Регистрационный номер</th>
                <th rowspan="2">Кличка</th>
                <th colspan="2">Дата последней вакцинации</th>
            </tr>
            <tr>
                <th>Бешенство</th>
                <th>Лептоспироз</th>
            </tr>
            </thead>
            <?php
            $currentAreaId = null;
            $currentDistId = null;
            $count = 1;
            $totalPerDist = 0;
            $totalPerArea = 0;
            $total = 0;
            ?>
            <?php foreach ($rows as $row): ?>
                <?php if ($currentDistId != $row['id_district'] && $currentDistId != null): ?>
                    <tr style="background: #b5dcf2">
                        <th colspan="15">Итого по району</th>
                        <th><?php echo ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentAreaId, []), (int)$currentDistId, 0); ?></th>
                    </tr>
                    <?php
                    $totalPerDist = 0;
                    ?>
                <?php endif ?>
                <?php if ($currentAreaId != $row['id_area'] && $currentAreaId != null): ?>
                    <tr style="background: #b5dcf2">
                        <th colspan="15">Итого по округу</th>
                        <th><?php echo ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentAreaId, []), 'total', 0); ?></th>
                    </tr>
                    <?php
                    $totalPerArea = 0;
                    ?>
                <?php endif ?>
                <?php
                if ($currentAreaId != $row['id_area']):
                    $currentAreaId = $row['id_area'];
                    ?>
                <?php endif ?>
                <?php
                if ($currentDistId != $row['id_district']):
                    $currentDistId = $row['id_district'];
                    ?>
                <?php endif ?>
                <tr>
                    <td>
                        <?= $row['number'] ?>
                    </td>
                    <td>
                        <?= $row['area_name'] ?? 'Округ не указан' ?>
                    </td>
                    <td>
                        <?= $row['dist_name'] ?? 'Район не указан' ?>
                    </td>
                    <td>
                        <?= $row['owner_name'] ?>
                    </td>
                    <td>
                        <?= $row['reg_address'] ?>
                    </td>
                    <td>
                        <?= $row['fact_address'] ?>
                    </td>
                    <td>
                        <?= $row['owner_phone'] ?>
                    </td>
                    <td>
                        <?= $row['owner_mail'] ?>
                    </td>
                    <td>
                        <?= $row['species'] ?>
                    </td>
                    <td>
                        <?= $row['breed'] ?>
                    </td>
                    <td>
                        <?php if (!empty($row['pet_sex'])){
                            echo $row['pet_sex'] == 'm' ? 'Мужской' : 'Женский';
                        } else echo ''; ?>
                    </td>
                    <td>
                        <?= $row['pet_ident'] ?>
                    </td>
                    <td>
                        <?php if ($row['reg_num']) {
                            echo substr_replace($row['reg_num'], "-", 6, 0);
                        } ?>
                    </td>
                    <td>
                        <?= $row['pet_name'] ?>
                    </td>
                    <td>
                        <?= $row['rab_date'] ?>
                    </td>
                    <td>
                        <?= $row['lept_date'] ?>
                    </td>
                </tr>
                <?php $count++ ?>
            <?php endforeach ?>
            <!-- это последняя страница? -->
            <?php if ($pagination->getPage() == ($pagination->getPageCount() - 1)): ?>
                <?php if (!isset($subtotals[0])): ?>
                    <tr style="background: #cae7f7">
                        <th colspan="15">Итого по району</th>
                        <th><?php echo ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentAreaId, []), (int)$currentDistId, 0); ?></th>
                        </th>
                    </tr>
                    <tr style="background: #b5dcf2">
                        <th colspan="15">Итого по округу</th>
                        <th><?php echo ArrayHelper::getValue(ArrayHelper::getValue($subtotals, (int)$currentAreaId, []), 'total', 0); ?></th>
                    </tr>
                <?php else : ?>
                    <tr style="background: #b5dcf2">
                        <th colspan="15">Итого без округа и района</th>
                        <th><?php echo ArrayHelper::getValue($subtotals[0], 'total', 0); ?></th>
                    </tr>
                <?php endif; ?>
                <tr style="background: #9ac9e2">
                    <!--                     $pagination->totalCount дает здесь некорректный результат из-за LEFT JOIN -->
                    <!--                    <th colspan="8">Всего --><!--<? ///*= $pagination->totalCount; */?> -->
                    <!--</th>-->
                    <th colspan="15">Всего</th>
                    <th><?php echo ArrayHelper::getValue($subtotals, 'total', 0); ?></th>
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
