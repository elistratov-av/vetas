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

<div class="title title3">Общеклинический анализ крови<br>
   экспертиза № <?= PdfGenerator::getValue('P2_SerialServiceNum', $data) ?></div>

<div class="clear"></div>

<br>

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
        <td class="t2 t22 b" style="width:180px;" rowspan="2">Показатели</td>
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
        <td class="t2">Эритроциты (RBC)</td>
        <td class="t2 t22">*10<sup>12</sup>/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P22_Rbcvalue', $data) ?></td>
        <td class="t2 t22">5,5 - 8,4</td>
        <td class="t2 t22">5,3 - 10,0</td>
    </tr>
    <tr class="tr">
        <td class="t2">2</td>
        <td class="t2">Лейкоциты (WBC)</td>
        <td class="t2 t22">*10<sup>9</sup>/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P14_Wbcvalue', $data) ?></td>
        <td class="t2 t22">6,0 - 12,0</td>
        <td class="t2 t22">5,5 - 18,5</td>
    </tr>
    <tr class="tr">
        <td class="t2">3</td>
        <td class="t2">Гемоглобин (Hgb)</td>
        <td class="t2 t22">г/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P24_Hgbvalue', $data) ?></td>
        <td class="t2 t22">130 - 190</td>
        <td class="t2 t22">90 - 150</td>
    </tr>
    <tr class="tr">
        <td class="t2">4</td>
        <td class="t2">Гематокрит (Hct, PCV) </td>
        <td class="t2 t22">%</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P26_Hctvalue', $data) ?></td>
        <td class="t2 t22">37 - 54</td>
        <td class="t2 t22">26 - 48</td>
    </tr>
    <tr class="tr">
        <td class="t2">5</td>
        <td class="t2">Тромбоциты (PLT)</td>
        <td class="t2 t22">*10<sup>9</sup>/л</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P36_Pltvalue', $data) ?></td>
        <td class="t2 t22">160 - 430</td>
        <td class="t2 t22">300 - 800</td>
    </tr>
    <tr class="tr">
        <td class="t2">6</td>
        <td class="t2">Средний объем эритроцита (MCV)</td>
        <td class="t2 t22">мкм<sup>3</sup>(фл)</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P28_Mcvvalue', $data) ?></td>
        <td class="t2 t22">60 - 75</td>
        <td class="t2 t22">43 - 53</td>
    </tr>
    <tr class="tr">
        <td class="t2">7</td>
        <td class="t2">Среднее содержание гемоглобина в эритроците (MCH)</td>
        <td class="t2 t22">Пг</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P30_Mchvalue', $data) ?></td>
        <td class="t2 t22">21  - 27</td>
        <td class="t2 t22">14 - 19</td>
    </tr>
    <tr class="tr">
        <td class="t2">8</td>
        <td class="t2">Средняя концентрация гемоглобина в эритроците (MCHC)</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P32_Mchcvalue', $data) ?></td>
        <td class="t2 t22">33 - 38</td>
        <td class="t2 t22">31 - 36</td>
    </tr>
    <tr class="tr">
        <td class="t2">9</td>
        <td class="t2">Показатель анизоцитоза эритроцитов (RDW)</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P34_Rdwvalue', $data) ?></td>
        <td class="t2 t22">11,9 - 16,0</td>
        <td class="t2 t22">14,0 - 18,0</td>
    </tr>
    <tr class="tr">
        <td class="t2">10</td>
        <td class="t2">СОЭ (ESR)</td>
        <td class="t2 t22">мм/ч</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P44_Soevalue', $data) ?></td>
        <td class="t2 t22">1 - 6</td>
        <td class="t2 t22">1 - 5</td>
    </tr>
</table>
<table class="t1" cellpadding="0" cellspacing="0">
    <tr class="tr">
        <td class="t2 t22 b" colspan="9">Лейкоцитарная формула:</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22" style="width:70px;height: 50px;" rowspan="2">Норма</td>
        <td class="t2 t22" style="width:70px;" rowspan="2">Базофилы</td>
        <td class="t2 t22" style="width:70px;" rowspan="2">Эозинофилы</td>
        <td class="t2 t22" style="width:240px;" colspan="4">Нейтрофилы</td>
        <td class="t2 t22" style="width:70px;" rowspan="2">Лимфоциты</td>
        <td class="t2 t22" style="width:70px;" rowspan="2">Моноциты</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22" style="width:60px;">Миелоциты</td>
        <td class="t2 t22" style="width:60px;">Юные</td>
        <td class="t2 t22" style="width:60px;">Палочко- ядерные</td>
        <td class="t2 t22" style="width:60px;">Сегменто- ядерные</td>
    </tr>
    <tr class="tr">
        <td class="t2">собаки</td>
        <td class="t2 t22">0 - 1</td>
        <td class="t2 t22">2 - 6</td>
        <td class="t2 t22">-</td>
        <td class="t2 t22">-</td>
        <td class="t2 t22">1 - 6</td>
        <td class="t2 t22">50 - 72</td>
        <td class="t2 t22">18 - 32</td>
        <td class="t2 t22">0 - 6</td>
    </tr>
    <tr class="tr">
        <td class="t2">кошки</td>
        <td class="t2 t22">0 - 1</td>
        <td class="t2 t22">2 - 8</td>
        <td class="t2 t22">-</td>
        <td class="t2 t22">-</td>
        <td class="t2 t22">1 - 9</td>
        <td class="t2 t22">40 - 45</td>
        <td class="t2 t22">20 - 55</td>
        <td class="t2 t22">1 - 3</td>
    </tr>
    <tr class="tr">
        <td class="t2">результат</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P56_Bazophilvalue', $data) ?></td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P52_Eosinophilsvalue', $data) ?></td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P46_Mielvalue', $data) ?></td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P46_Youngvalue', $data) ?></td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P48_Palochkoyadervalue', $data) ?></td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P50_Segmentvalue', $data) ?></td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P58_Limphocitvalue', $data) ?></td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P54_Monocitvalue', $data) ?></td>
    </tr>
</table>

<br><br>
<div>
    <div><b>Морфологические изменения крови:</b></div>
    <?php if (isset($data['P58_Morphbloodchangeresult'])) {
        echo $data['P58_Morphbloodchangeresult'];
    } else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
    <?php } ?>
</div>
<br>
<div>
    <div><b>Исследования на инвазионные болезни:</b></div>
    <?php if (isset($data['P58_Invasionresult'])) {
        echo $data['P58_Invasionresult'];
    } else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
    <?php } ?>
</div>

<br>
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
    <div class="t5" style="width: 300px;">( <span class="value"><?=PdfGenerator::getValue('P33_SpecialistFIO', $data)?></span> )
    </div>
</div>
<div>
    <div class="t32" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 250px;">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>
