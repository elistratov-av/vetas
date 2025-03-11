<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Клинический анализ кала</div>
<div class="title2">экспертиза № <?= $data['P2_SerialServiceNum'] ?? '' ?></div>

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
        <td class="t2 b" colspan="4">Физико-химические свойства:</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" style="width:180px;height: 50px;">Показатели</td>
        <td class="t2 t22 b" style="width:110px;">ед. измерения</td>
        <td class="t2 t22 b" style="width:180px;">Данные исследования</td>
        <td class="t2 t22 b" style="width:130px;">Средние значения (плотоядные)</td>
    </tr>
    <tr class="tr">
        <td class="t2">Цвет кала</td>
        <td class="t2 t22">визуально</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P16_Coprcolorvalue', $data) ?></td>
        <td class="t2 t22">коричневый</td>
    </tr>
    <tr class="tr">
        <td class="t2">Форма и консистенция</td>
        <td class="t2 t22">визуально</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P14_Coprformvalue', $data) ?></td>
        <td class="t2 t22">оформленный, плотный, цилиндрический</td>
    </tr>
    <tr class="tr">
        <td class="t2">Запах кала</td>
        <td class="t2 t22">органолептически</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P18_Coprodorvalue', $data) ?></td>
        <td class="t2 t22">специфический</td>
    </tr>
    <tr class="tr">
        <td class="t2">Кислотность pH</td>
        <td class="t2 t22">ед. рН</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P20_Acidityvalue', $data) ?></td>
        <td class="t2 t22">6,7 - 7,2</td>
    </tr>
    <tr class="tr">
        <td class="t2">Билирубин</td>
        <td class="t2 t22">кач.р-я</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P24_Bilirubinvalue', $data) ?></td>
        <td class="t2 t22">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Стеркобелин</td>
        <td class="t2 t22">кач.р-я</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P22_Stercobilinvalue', $data) ?></td>
        <td class="t2 t22">+/++</td>
    </tr>
    <tr class="tr">
        <td class="t2">Кровь в кале</td>
        <td class="t2 t22">кач.р-я</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P26_Bloodvalue', $data) ?></td>
        <td class="t2 t22">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Слизь</td>
        <td class="t2 t22">визуально</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P40_Slizvalue', $data) ?></td>
        <td class="t2 t22">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2 b" colspan="4">Микроскопия:</td>
    </tr>
    <tr class="tr">
        <td class="t2">Переваримость корма</td>
        <td class="t2 t22">микроскопически</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P41_Digestibilityvalue', $data) ?></td>
        <td class="t2 t22">удовлетворительная</td>
    </tr>
    <tr class="tr">
        <td class="t2">Соединительно-тканные волокна</td>
        <td class="t2 t22" rowspan="12">в поле зрения</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P30_Contissuefibersvalue', $data) ?></td>
        <td class="t2 t22">единичные непереваренные волокна</td>
    </tr>
    <tr class="tr">
        <td class="t2">Мышечные волокна непереваренные</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P28_Muscledfibersvalue', $data) ?></td>
        <td class="t2 t22">единичные</td>
    </tr>
    <tr class="tr">
        <td class="t2">Мышечные волокна полупереваренные</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P28_Muscledfiberssemivalue', $data) ?></td>
        <td class="t2 t22">единичные</td>
    </tr>
    <tr class="tr">
        <td class="t2">Растительная клетчатка непереваримая</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P42_Vegfiberindigestvalue', $data) ?></td>
        <td class="t2 t22">в соответствии с характером кормления</td>
    </tr>
    <tr class="tr">
        <td class="t2">Растительная клетчатка переваримая</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P43_Vegfiberdigestvalue', $data) ?></td>
        <td class="t2 t22">в незначит. кол-ве в соответствии с характером кормления</td>
    </tr>
    <tr class="tr">
        <td class="t2">Крахмал внеклеточный</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P38_Starchvalue', $data) ?></td>
        <td class="t2 t22">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Крахмал внутриклеточный</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P38_Starchinnervalue', $data) ?></td>
        <td class="t2 t22">единичный</td>
    </tr>
    <tr class="tr">
        <td class="t2">Нейтральный жир</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P32_Neutralfatvalue', $data) ?></td>
        <td class="t2 t22">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Жирные кислоты</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P34_Fattyacidsvalue', $data) ?></td>
        <td class="t2 t22">отсутствуют</td>
    </tr>
    <tr class="tr">
        <td class="t2">Мыла</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P36_Soapvalue', $data) ?></td>
        <td class="t2 t22">отсутствуют</td>
    </tr>
    <tr class="tr">
        <td class="t2">Клеточные элементы</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P44_Cellelementsvalue', $data) ?></td>
        <td class="t2 t22">единичные клетки эпителия ЖКТ</td>
    </tr>
    <tr class="tr">
        <td class="t2">Слизь</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P45_Slizvalue', $data) ?></td>
        <td class="t2 t22">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Прочее</td>
        <td class="t2 t22" colspan="3"><?= PdfGenerator::getValue('P46_Othervalue', $data) ?></td>
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
<br>
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
    <div class="t5" style="width: 300px;">( <span class="value"><?= isset($data['P33_SpecialistFIO']) ? $data['P33_SpecialistFIO'] : '&nbsp;' ?></span> )</div>
</div>
<div>
    <div class="t32" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 250px;">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>

