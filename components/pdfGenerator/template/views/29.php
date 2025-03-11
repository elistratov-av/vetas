<?php
/**
 * @var array $data
 */

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;

?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Результат клинического анализа белка в моче</div>
<div class="title2">экспертиза № <?= PdfGenerator::getValue('P0_Urinalysisnum', $data) ?></div>

<div class="clear"></div>

<div class="t1">
    <div class="tr">
        <div class="t2">Ветеринарное лечебное учреждение (подразделение): <span class="value"><?= PdfGenerator::getValue('P13_Orgshortname', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2">Ф. И. О. владельца: <span class="value"><?= PdfGenerator::getValue('P4_Ownername', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2">Адрес, телефон: <span class="value"><?= PdfGenerator::getValue('P5_Owneraddres', $data) ?>, <?= PdfGenerator::getValue('P5_Ownercontact', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2">Сведения о животном:</div>
    </div>
    <div class="tr">
        <div class="t2" style="width:100px;">Вид:</div>
        <div class="t2" style="width:220px;"><span class="value"><?= PdfGenerator::getValue('P6_Speciesname', $data) ?></span></div>
        <div class="t2" style="width:100px;">Пол:</div>
        <div class="t2"><b><?= PdfGenerator::petSex($data) ?></b></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:100px;">Порода:</div>
        <div class="t2" style="width:220px;"><span class="value"><?= PdfGenerator::getValue('P7_Breedname', $data) ?></span></div>
        <div class="t2" style="width:100px;border-bottom:0;">Кличка:</div>
        <div class="t2" style="border-bottom:0;"><span class="value"><?= PdfGenerator::getValue('P9_Petname', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:100px;">Возраст:</div>
        <div class="t2" style="width:220px;"><span class="value"><?= PdfGenerator::petAge($data) ?></span></div>
        <div class="t2" style="width:100px;">&nbsp;</div>
        <div class="t2">&nbsp;</div>
    </div>
</div>

<br>

<table class="t1" cellpadding="0" cellspacing="0">
    <tr class="tr">
        <td class="t2 t22 b" colspan="4">Физико-химические свойства:</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" style="width:180px;height: 50px;">Показатели</td>
        <td class="t2 t22 b" style="width:150px;">Ед. измерения</td>
        <td class="t2 t22 b" style="width:160px;">Данные исследования</td>
        <td class="t2 t22 b">Средние значения (для вида)</td>
    </tr>
    <tr class="tr">
        <td class="t2">Белок</td>
        <td class="t2">г/л</td>
        <td class="t2"><?= PdfGenerator::getValue('P20_Proteinvalue', $data) ?></td>
        <td class="t2">0,0 - 0,3</td>
    </tr>
</table>

<br>
<div>
    <?php if (isset($data['P13_Serviceresultdesc'])): ?>
        <div><b>Примечание:</b></div>
        <?= PdfGenerator::getValue('P13_Serviceresultdesc', $data, str_repeat('<div class="hr">&nbsp;</div>', 3)) ?>
    <?php elseif (isset($data['P16_Analysisdesc'])): ?>
        <div><b>Примечание (к лабораторным исследованиям):</b></div>
        <?= PdfGenerator::getValue('P16_Analysisdesc', $data, str_repeat('<div class="hr">&nbsp;</div>', 3)) ?>
    <?php endif; ?>
</div>
<br><br>
<div>
    <div class="t32" style="width: 120px;">Дата</div>
    <div class="t32" style="width: 200px;">
        <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = PdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
            ?>
            «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> года
        <?php } else { ?>
            «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________20__ года
        <?php } ?>
    </div>
</div>
<br>
<div>
    <div class="t32" style="width: 120px;">Ветврач</div>
    <div class="t5" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 12px;">&nbsp;</div>
    <div class="t5" style="width: 300px;">( <span class="value"><?=PdfGenerator::getValue('P33_SpecialistFIO', $data)?></span> )</div>
</div>
<div>
    <div class="t32" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 250px;">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>
