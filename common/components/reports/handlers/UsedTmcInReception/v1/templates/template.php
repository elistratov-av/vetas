<?php
use app\common\helpers\DateHelper;
use yii\helpers\Url;

/* @var \app\common\components\reports\handlers\UsedTmcInReception\v1\dto\ReportDto $dto */
?>
<div class="logo">
    <img src="<?=Url::to('@web/img/logo.png')?>" width="71" height="74"/>
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба города Москвы</div>
<div class="clear"></div>
<br><br>


<div class="tr2">
    <div class="t7">Ветеринарное лечебное учреждение (подразделение): <span class="value"><?=$dto->organizationName?></span></div>
</div>
<div class="tr2">
    <div class="t7" style="width: 133px;">Ф.И.О. владельца:</div>
    <div class="t7"><span class="value"><?=$dto->specialistName?></span></div>
</div>
<div class="tr2">
    <div class="t8" style="width: 133px;">Адрес, телефон:</div>
    <div class="t8">
        <span class="value">
            <?=implode(',', array_filter(
                [$dto->ownerAddress, $dto->ownerPhone],
                function ($value) {
                    return $value !== null && $value !== '';
                }))
            ?>
        </span>
    </div>
</div>
<br>

<div class="title">Отчет по ТМЦ</div>

<div class="t6">
    <div class="t4">
        <div class="tr2">
            <div class="t3" style="width: 80px;">ID приема</div>
            <div class="t5" style="width: 100px;"><span class="value"><?=$dto->visitId?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 55px;">от «<?=$dto->date->format('j')?>»</div>
            <div class="t5" style="text-align: center; width: 100px;"><span class="value"><?=DateHelper::monthToStr($dto->date->format('m'))?></span></div>
            <div style="width: 75px;"><?=$dto->date->format('Y')?> года</div>
        </div>
    </div>

    <div class="clear"></div>
    <br>

    <table class="table1" border="1" bordercolor="#a9a9a9">
        <thead>
        <tr class="table__row1">
            <th class="table-key1">Наименование ТМЦ</th>
            <th class="table-key1">Серийный номер</th>
            <th class="table-key1">Срок годности</th>
            <th class="table-key1">Количество используемого ТМЦ</th>
            <th class="table-key1">Количество ТМЦ к утилизации</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($dto->tmc) { ?>
            <?php foreach ($dto->tmc as $tmc) { ?>
                <tr class="table__row1">
                    <td class="table-value-description1"><?=$tmc->name?></td>
                    <td class="table-value-description1"><?=$tmc->inventoryNumber?></td>
                    <td class="table-value-description1"><?=$tmc->expirationDate ? $tmc->expirationDate->format('d.m.Y') : '';?></td>
                    <td class="table-value-description1"><?=$tmc->count?></td>
                    <td class="table-value-description1"><?=$tmc->countUtilize?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr class="table__row1">
                <td colspan="4" class="table-value-description1">-</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <br><br>
    <div>
        <div class="t32" style="width: 150px;">Ветеринарный врач</div>
        <div class="t5" style="width: 300px;"><span class="value"><?=$dto->specialistName?></span></div>
    </div>
</div>
