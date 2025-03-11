<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;

$isCat = (isset($data['P6_Speciesname']) && mb_strtolower($data['P6_Speciesname']) == 'кошки');
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Клинический анализ мочи</div>
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
        <td class="t2 t22 b" colspan="4">Физико-химические свойства:</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" style="width:180px;height: 50px;">Показатели</td>
        <td class="t2 t22 b" style="width:150px;">Ед. измерения</td>
        <td class="t2 t22 b" style="width:160px;">Данные исследования</td>
        <td class="t2 t22 b">Средние значения (для вида)</td>
    </tr>
    <tr class="tr">
        <td class="t2">Цвет</td>
        <td class="t2">визуально</td>
        <td class="t2"><?= PdfGenerator::getValue('P14_Colorurinevalue', $data) ?></td>
        <td class="t2">соломенно-желтый - желтый</td>
    </tr>
    <tr class="tr">
        <td class="t2">Прозрачность</td>
        <td class="t2">визуально</td>
        <td class="t2"><?= PdfGenerator::getValue('P16_Transparencyvalue', $data) ?></td>
        <td class="t2">прозрачная</td>
    </tr>
    <tr class="tr">
        <td class="t2">Консистенция</td>
        <td class="t2">визуально</td>
        <td class="t2"><?= PdfGenerator::getValue('P67_Urinconsistency', $data) ?></td>
        <td class="t2">водянистая</td>
    </tr>
    <tr class="tr">
        <td class="t2">Кислотность рН</td>
        <td class="t2">ед. pH</td>
        <td class="t2"><?= PdfGenerator::getValue('P18_Acidityvalue', $data) ?></td>
        <td class="t2">5,0 – 6,5</td>
    </tr>
    <tr class="tr">
        <td class="t2">Относительная плотность</td>
        <td class="t2">г/см³</td>
        <td class="t2"><?= PdfGenerator::getValue('P26_Relativedensityvalue', $data) ?></td>
        <td class="t2"><?php echo $isCat ? '1,035 - 1,050' : '1,015 - 1,025' ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">Белок</td>
        <td class="t2">г/л</td>
        <td class="t2"><?= PdfGenerator::getValue('P20_Proteinvalue', $data) ?></td>
        <td class="t2">0,0 - 0,3</td>
    </tr>
    <tr class="tr">
        <td class="t2">Глюкоза</td>
        <td class="t2">ммоль/л</td>
        <td class="t2"><?= PdfGenerator::getValue('P22_Glukozavalue', $data) ?></td>
        <td class="t2"><?php echo $isCat ? '0,0 - 0,3' : '0,0 - 3,0' ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">Нитриты</td>
        <td class="t2">кач.р-я</td>
        <td class="t2"><?= PdfGenerator::getValue('P63_Nitritvalue', $data) ?></td>
        <td class="t2">отрицательная</td>
    </tr>
    <tr class="tr">
        <td class="t2">Кетоновые тела</td>
        <td class="t2">ммоль/л</td>
        <td class="t2"><?= PdfGenerator::getValue('P24_Ketonbodvalue', $data) ?></td>
        <td class="t2">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Уробилиноген</td>
        <td class="t2">ммоль/л</td>
        <td class="t2"><?= PdfGenerator::getValue('P24_Urobilinogenvalue', $data) ?></td>
        <td class="t2">0 - 17</td>
    </tr>
    <tr class="tr">
        <td class="t2">Билирубин</td>
        <td class="t2">кач.р-я</td>
        <td class="t2"><?= PdfGenerator::getValue('P28_Bilirubinvalue', $data) ?></td>
        <td class="t2">отрицательная</td>
    </tr>
    <tr class="tr">
        <td class="t2">Гемоглобин</td>
        <td class="t2">кач.р-я</td>
        <td class="t2"><?= PdfGenerator::getValue('P30_Hemeglvalue', $data) ?></td>
        <td class="t2">отрицательная</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" colspan="4">Микроскопия осадка:</td>
    </tr>
    <tr class="tr">
        <td class="t2">Эритроциты</td>
        <td class="t2" rowspan="11">в поле зрения</td>
        <td class="t2"><?= PdfGenerator::getValue('P32_Erythrocytvalue', $data) ?></td>
        <td class="t2">0 - 1</td>
    </tr>
    <tr class="tr">
        <td class="t2">Лейкоциты</td>
        <td class="t2"><?= PdfGenerator::getValue('P34_Leucocytvalue', $data) ?></td>
        <td class="t2">0 - 5</td>
    </tr>
    <tr class="tr">
        <td class="t2">Неорганизованный осадок</td>
        <td class="t2"><?= PdfGenerator::getValue('P58_Saltvalue', $data) ?></td>
        <td class="t2">может присутствовать (единичные кристаллы)</td>
    </tr>
    <tr class="tr">
        <td class="t2">Эпителий:</td>
        <td class="t2"></td>
        <td class="t2"></td>
    </tr>
    <tr class="tr">
        <td class="t2">плоский</td>
        <td class="t2"><?= PdfGenerator::getValue('P36_Ploskiyvalue', $data) ?></td>
        <td class="t2">единичный</td>
    </tr>
    <tr class="tr">
        <td class="t2">переходный</td>
        <td class="t2"><?= PdfGenerator::getValue('P38_Perehodvalue', $data) ?></td>
        <td class="t2">единичный</td>
    </tr>
    <tr class="tr">
        <td class="t2">почечный</td>
        <td class="t2"><?= PdfGenerator::getValue('P40_Pochechnvalue', $data) ?></td>
        <td class="t2">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Цилиндры:</td>
        <td class="t2"><?= PdfGenerator::getValue('P54_Cilindervalue', $data) ?></td>
        <td class="t2">единич.зернистые и гиалиновые</td>
    </tr>
    <tr class="tr">
        <td class="t2">Слизь</td>
        <td class="t2"><?= PdfGenerator::getValue('P56_Slizvalue', $data) ?></td>
        <td class="t2">отсутствует</td>
    </tr>
    <tr class="tr">
        <td class="t2">Бактерии</td>
        <td class="t2"><?= PdfGenerator::getValue('P56_Bacteriavalue', $data) ?></td>
        <td class="t2">единичные</td>
    </tr>
    <tr class="tr">
        <td class="t2">Прочее</td>
        <td class="t2"><?= PdfGenerator::getValue('P56_Othervalue', $data) ?></td>
        <td class="t2"></td>
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
    <div class="t5" style="width: 300px;">( <span class="value"><?= isset($data['P33_SpecialistFIO']) ? $data['P33_SpecialistFIO'] : '&nbsp;' ?></span> )</div>
</div>
<div>
    <div class="t32" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 250px;">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>
