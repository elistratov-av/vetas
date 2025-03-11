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

<div class="title">Биохимический анализ крови</div>
<div class="title2">экспертиза № <?= PdfGenerator::getValue('P2_SerialServiceNum', $data) ?></div>

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
        <td class="t2 t22 b" style="width:50px;height: 50px;" rowspan="2">№</td>
        <td class="t2 t22 b" style="width:180px;" rowspan="2">биохимические показатели крови</td>
        <td class="t2 t22 b" style="width:70px;" rowspan="2">Единицы измерения</td>
        <td class="t2 t22 b" style="width:110px;" rowspan="2">Результаты исследования</td>
        <td class="t2 t22 b" style="width:140px;" colspan="2">Нормальные величины</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" style="width:70px;">Собаки</td>
        <td class="t2 t22 b" style="width:70px;">Кошки</td>
    </tr>

    <tr class="tr">
        <td class="t2">1</td>
        <td class="t2">Общий белок</td>
        <td class="t2">г/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P94_Totalproteinglvalue', $data) ?></td>
        <td class="t2 t22">60-72</td>
        <td class="t2 t22">58-76</td>
    </tr>

    <tr class="tr">
        <td class="t2">2</td>
        <td class="t2">Альбумины</td>
        <td class="t2">г/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P98_Albuminglvalue', $data) ?></td>
        <td class="t2 t22">26-39</td>
        <td class="t2 t22">28-38</td>
    </tr>

    <tr class="tr">
        <td class="t2">3</td>
        <td class="t2">Альфа-амилаза (общая)</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P36_Amilazavalue', $data) ?></td>
        <td class="t2 t22">до 1200</td>
        <td class="t2 t22">до 1600</td>
    </tr>

    <tr class="tr">
        <td class="t2">4</td>
        <td class="t2">Мочевина</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P26_Mochevinammvalue', $data) ?></td>
        <td class="t2 t22">3,5-9,2</td>
        <td class="t2 t22">5,4-12,1</td>
    </tr>

    <tr class="tr">
        <td class="t2">5</td>
        <td class="t2">Креатинин</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P30_Creatininemcmvalue', $data) ?></td>
        <td class="t2 t22">26-120</td>
        <td class="t2 t22">60-160</td>
    </tr>

    <tr class="tr">
        <td class="t2">6</td>
        <td class="t2">Глюкоза</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P40_Glukozamcmvalue', $data) ?></td>
        <td class="t2 t22">4,3-7,2</td>
        <td class="t2 t22">3,0-12,0</td>
    </tr>

    <tr class="tr">
        <td class="t2">7</td>
        <td class="t2">Общий билирубин</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P14_Totalbilirubinmcmvalue', $data) ?></td>
        <td class="t2 t22">3,0-13,0</td>
        <td class="t2 t22">3,0-12,0</td>
    </tr>

    <tr class="tr">
        <td class="t2">8</td>
        <td class="t2">Щелочная фосфатаза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P34_Shelochfosfatvalue', $data) ?></td>
        <td class="t2 t22">до 75</td>
        <td class="t2 t22">до 90</td>
    </tr>

    <tr class="tr">
        <td class="t2">9</td>
        <td class="t2">АЛТ</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P22_Altalanniamvalue', $data) ?></td>
        <td class="t2 t22">до 55</td>
        <td class="t2 t22">до 60</td>
    </tr>

    <tr class="tr">
        <td class="t2">10</td>
        <td class="t2">АСТ</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P24_Astaspartvalue', $data) ?></td>
        <td class="t2 t22">до 40</td>
        <td class="t2 t22">до 45</td>
    </tr>

    <tr class="tr">
        <td class="t2">11</td>
        <td class="t2">ГГТ</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P46_Lgtgammavalue', $data) ?></td>
        <td class="t2 t22">0-8</td>
        <td class="t2 t22">0-8</td>
    </tr>

    <tr class="tr">
        <td class="t2">12</td>
        <td class="t2">ЛДГ</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P44_Ldglactodvalue', $data) ?></td>
        <td class="t2 t22">до 220</td>
        <td class="t2 t22">до 220</td>
    </tr>

    <tr class="tr">
        <td class="t2">13</td>
        <td class="t2">Креатинфосфокиназа</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P48_Kfkcreatinevalue', $data) ?></td>
        <td class="t2 t22">32-154</td>
        <td class="t2 t22">40-220</td>
    </tr>

    <tr class="tr">
        <td class="t2">14</td>
        <td class="t2">Холестерин</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P50_Holestermmvalue', $data) ?></td>
        <td class="t2 t22">2,5-6,0</td>
        <td class="t2 t22">1,9-6,5</td>
    </tr>

    <tr class="tr">
        <td class="t2">15</td>
        <td class="t2">Триглицериды</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P54_Triglyceridsmmvalue', $data) ?></td>
        <td class="t2 t22">0,45-1,1</td>
        <td class="t2 t22">0,2-1,15</td>
    </tr>

    <tr class="tr">
        <td class="t2">16</td>
        <td class="t2">Липаза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P92_Lipazavalue', $data) ?></td>
        <td class="t2 t22">30-500</td>
        <td class="t2 t22">30-400</td>
    </tr>

    <tr class="tr">
        <td class="t2">17</td>
        <td class="t2">Кальций общий</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P70_Calciummmvalue', $data) ?></td>
        <td class="t2 t22">2,3-2,8</td>
        <td class="t2 t22">2,1-2,5</td>
    </tr>

    <tr class="tr">
        <td class="t2">18</td>
        <td class="t2">Фосфор</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P66_Phosphormmvalue', $data) ?></td>
        <td class="t2 t22">1,1-1,8</td>
        <td class="t2 t22">1,3-2,1</td>
    </tr>

    <tr class="tr">
        <td class="t2">19</td>
        <td class="t2">Магний</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P78_Magnesiummmvalue', $data) ?></td>
        <td class="t2 t22">0,8-1,1</td>
        <td class="t2 t22">0,9-1,2</td>
    </tr>

    <tr class="tr">
        <td class="t2">20</td>
        <td class="t2">Железо</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P74_Ironmcmvalue', $data) ?></td>
        <td class="t2 t22">19-32</td>
        <td class="t2 t22">19-37</td>
    </tr>

    <tr class="tr">
        <td class="t2">21</td>
        <td class="t2">Панкреатическая амилаза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P38_Pancreatinevalue', $data) ?></td>
        <td class="t2 t22">200-800</td>
        <td class="t2 t22">300-1200</td>
    </tr>

    <tr class="tr">
        <td class="t2">22</td>
        <td class="t2">Калий</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P58_Caliummmvalue', $data) ?></td>
        <td class="t2 t22">3,8-5,6</td>
        <td class="t2 t22">3,6-5,5</td>
    </tr>

    <tr class="tr">
        <td class="t2">23</td>
        <td class="t2">Натрий</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P62_Natrmmvalue', $data) ?></td>
        <td class="t2 t22">145-150</td>
        <td class="t2 t22">145-150</td>
    </tr>
</table>

<br><br>
<div>
<?php if (isset($data['P13_Serviceresultdesc'])): ?>
    <div><b>Примечание:</b></div>
    <?php echo $data['P13_Serviceresultdesc'];?>
<?php elseif (isset($data['P16_Analysisdesc'])): ?>
    <div><b>Примечание (к лабораторным исследованиям):</b></div>
    <?php echo $data['P16_Analysisdesc'];?>
<?php else: ?>
    <div class="hr">&nbsp;</div>
    <div class="hr">&nbsp;</div>
    <div class="hr">&nbsp;</div>
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
<br><br>
<div>
    <div class="t32" style="width: 120px;">Ветврач</div>
    <div class="t5" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 12px;">&nbsp;</div>
    <div class="t5" style="width: 300px;">( <span class="value"><?= $data['P33_SpecialistFIO'] ?? '&nbsp;' ?></span> )</div>
</div>
<div>
    <div class="t32" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 250px;">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>
