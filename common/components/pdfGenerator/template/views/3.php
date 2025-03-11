<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;

?>

<div class="logo">
    <img src="<?=Url::to('@web/img/logo.png');?>" width="71" height="74"/>
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба города Москвы</div>
<div class="head2">Приложение 3 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Результат ЦИТОЛОГИЧЕСКОГО ИССЛЕДОВАНИЯ МАЗКА – ОТПЕЧАТКА</div>
<div class="title2">
    экспертиза № <?=PdfGenerator::getValue('P12_Analysisnum', $data, '_______')?>
    <?php
    if (isset($data['P18_Analysisdate'])) {
        $P18_Analysisdate = PdfGenerator::dateFromFormat($data['P18_Analysisdate'], 'd.m.Y');
        ?>
        от «<?=(int)$P18_Analysisdate['d']?>» <?=$P18_Analysisdate['M']?> <?=$P18_Analysisdate['Y']?> года
    <?php } else { ?>
        от «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________20_____ года
    <?php } ?>
</div>

<div class="clear"></div>

<div class="t1">
    <div class="tr">
        <div class="t2" style="width:380px;">Ф. И. О. владельца: <span class="value"><?=$data['P4_Ownername'] ?? ''?></span></div>
        <div class="t2">контактный телефон: <span class="value"><?=$data['P5_Ownercontact'] ?? ''?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:120px;">Животное: вид:</div>
        <div class="t2" style="width:80px;"><?=isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'собаки' ? '<span class="strike">&nbsp;собака&nbsp;</span>' : 'собака') : 'собака'?></div>
        <div class="t2" style="width:80px;"><?=isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'кошки' ? '<span class="strike">&nbsp;кошка&nbsp;</span>' : 'кошка') : 'кошка'?></div>
        <div class="t2" style="width:80px;"><?=isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'лошадь' ? '<span class="strike">&nbsp;лошадь&nbsp;</span>' : 'лошадь') : 'лошадь'?></div>
        <div class="t2" style="width:80px;">&nbsp;</div>
        <div class="t2">ненужное зачеркнуть</div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;">Порода: <span class="value"><?=$data['P7_Breedname'] ?? ''?></span></div>
        <div class="t2" style="width:92px;">Пол:</div>
        <div class="t2" style="width:80px;"><b><?=isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'f' ? '<span class="strike">&nbsp;M&nbsp;</span>' : 'M') : 'M'?></b></div>
        <div class="t2" style="width:80px;"><b><?=isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'm' ? '<span class="strike">&nbsp;F&nbsp;</span>' : 'F') : 'F'?></b></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:130px;">Кличка: <span class="value"><?=$data['P9_Petname'] ?? ''?></span></div>
        <div class="t2" style="width:220px;color:#949494;">Идентификационный № <span class="value"><?=$data['P0_Petchpidentificationcode'] ?? ''?></span></div>
        <div class="t2" style="width:92px;">Возраст: <span class="value"><?=PdfGenerator::petAge($data);?></span></div>
        <div class="t2" style="width:180px;color:#949494;"><b>Рег. № &nbsp;&nbsp;&nbsp; Журнала № <?=$data['P11_Regnum'] ?? ''?></b></div>
    </div>
    <div class="tr">
        <div class="t2">Лечащий врач: <span class="value"><?=$data['P12_Attendingdoctor'] ?? ''?></span></div>
    </div>
    <div class="tr" style="border-bottom:0;">
        <div class="t2">Ветеринарное лечебное учреждение (подразделение): <span class="value"><?=$data['P13_Orgshortname'] ?? ''?></span></div>
    </div>
</div>

<br>


<table class="t1" cellpadding="0" cellspacing="0">
    <tr class="tr">
        <td class="t2 b" style="width: 200px;">Эпителиальные клетки</td>
        <td class="t2"><?=PdfGenerator::getValue('P3_Epithermal_cells', $data)?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Лейкоциты</td>
        <td class="t2"><?=PdfGenerator::getValue('P18_Leukocytesvalue', $data)?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Эритроциты</td>
        <td class="t2"><?=PdfGenerator::getValue('P19_Erythrocytesvalue', $data)?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Бактерии</td>
        <td class="t2"><?=PdfGenerator::getValue('P20_Bacteriavalue', $data)?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Грибы</td>
        <td class="t2"><?=PdfGenerator::getValue('P3_Mushrooms', $data)?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Примечание</td>
        <td class="t2"><?=PdfGenerator::getValue('P16_Analysisdesc', $data)?></td>
    </tr>
</table>

<br>

<div>
    <div><b><i>Количество поступивших проб:</i></b></div>
    <?=PdfGenerator::getValue('P14_Analysiscount', $data, '<div class="hr">&nbsp;</div>')?>
</div>
<br>

<div>
    <div><b><i>Ход и результаты исследования:</i></b></div>
    <?=PdfGenerator::getValue('P15_Analysisresult', $data, '<div class="hr">&nbsp;</div>')?>
</div>
<br>

<div>
    <div><b><i>Заключение:</i></b></div>
    <?=PdfGenerator::getValue('P3_Serviceresult', $data, '<div class="hr">&nbsp;</div>')?>
</div>
<br>

<br><br>

<div>
    <div class="t32" style="width: 120px;">Лаборант (ветврач)</div>
    <div class="t5" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 12px;">&nbsp;</div>
    <div class="t5" style="width: 300px; height: 18px;"><span class="value"><?= $data['P33_SpecialistFIO'] ?? '&nbsp;' ?></span></div>
</div>
<div>
    <div class="t32" style="width: 200px">&nbsp;</div>
    <div class="t32" style="width: 250px">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>


