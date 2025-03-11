<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 14 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Результат гормональных исследований крови</div>
<div class="title2">
    Экспертиза № <?= $data['P2_SerialServiceNum'] ?? '' ?>
    <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = PdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
    ?>
    от «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> года
    <?php } else { ?>
    от «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________201__ года
    <?php } ?>
</div>

<div class="clear"></div>
<div class="t1">
    <div class="tr">
        <div class="t2" style="width:380px;">Ф. И. О. владельца: <span class="value"><?= $data['P4_Ownername'] ?? '' ?></span></div>
        <div class="t2">контактный телефон: <span class="value"><?= $data['P5_Ownercontact'] ?? '' ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:120px;">Животное: вид:</div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'собаки' ? '<span class="strike">&nbsp;собака&nbsp;</span>' : 'собака') : 'собака' ?></div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'кошки' ? '<span class="strike">&nbsp;кошка&nbsp;</span>' : 'кошка') : 'кошка' ?></div>
        <div class="t2" style="width:80px;">&nbsp;</div>
        <div class="t2" >ненужное зачеркнуть</div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;">Порода: <span class="value"><?= $data['P7_Breedname'] ?? '' ?></span></div>
        <div class="t2" style="width:92px;">Пол:</div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'f' ? '<span class="strike">&nbsp;M&nbsp;</span>' : 'M') : 'M' ?></b></div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'm' ? '<span class="strike">&nbsp;F&nbsp;</span>' : 'F') : 'F' ?></b></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:130px;">Кличка: <span class="value"><?= $data['P9_Petname'] ?? '' ?></span></div>
        <div class="t2" style="width:220px;color:#949494;">Идентификационный № <span class="value"><?= $data['P0_Petchpidentificationcode'] ?? '' ?></span></div>
        <div class="t2" style="width:92px;">Возраст: <span class="value"><?= Yii::$app->pdfGenerator::petAge($data) ?></span></div>
        <div class="t2" style="width:180px;color:#949494;"><b>Рег. № &nbsp;&nbsp;&nbsp; Журнала № <?= $data['P11_Regnum'] ?? '' ?></b></div>
    </div>
    <div class="tr">
        <div class="t2">Лечащий врач: <span class="value"><?= $data['P12_Attendingdoctor'] ?? '' ?></span></div>
    </div>
    <div class="tr" style="border-bottom:0;">
        <div class="t2">Ветеринарное лечебное учреждение (подразделение): <span class="value"><?= $data['P13_Orgshortname'] ?? '' ?></span></div>
    </div>
</div>

<br>

<table class="t1" cellpadding="0" cellspacing="0">
    <tr class="tr">
        <td class="t2 t22 b" style="width:20px;" rowspan="2">№</td>
        <td class="t2 t22 b" style="width:250px;" rowspan="2">Биохимические показатели</td>
        <td class="t2 t22 b" style="width:90px;" rowspan="2">Единицы измерен.</td>
        <td class="t2 t22 b" style="width:160px;" colspan="3">Результаты</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" style="height: 40px;">Данные исследования</td>
        <td class="t2 t22 b" style="width:80px;">Собаки</td>
        <td class="t2 t22 b" style="width:70px;">Кошки</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22">1</td>
        <td class="t2"><b>Кортизол</b> <i>базальный</i></td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P14_Cortisolbazalvalue', $data) ?></td>
        <td class="t2 t22">0-125</td>
        <td class="t2 t22">0-150</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22">2</td>
        <td class="t2"><b>Кортизол</b> <i>после стимуляции АКТГ</i></td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P15_Cortisolactgvalue', $data) ?></td>
        <td class="t2 t22">200-550</td>
        <td class="t2 t22">130-450</td>
    </tr>
    <tr class="tr">
        <td class="t2 t22">3</td>
        <td class="t2"><b>Кортизол</b> 8ч <i>после дексаметазоновой пробы (0,01-0,015 мг/кг)</i></td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P16_Cortisoldexvalue', $data) ?></td>
        <td class="t2 t22">0-40</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22">4</td>
        <td class="t2"><b>Прогестерон</b> </td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"></td>
        <td class="t2 t22"></td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">анэструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P17_Proganesvalue', $data) ?></td>
        <td class="t2 t22">1,9+0,3</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">проэструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P18_Progproenstvalue', $data) ?></td>
        <td class="t2 t22">5,4+0,95</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">эструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P19_Progestrusvalue', $data) ?></td>
        <td class="t2 t22">менее5,4</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">метэструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P20_Progmetestvalue', $data) ?></td>
        <td class="t2 t22">37-86</td>
        <td class="t2 t22"></td>
    </tr>

    <tr class="tr">
        <td class="t2 t22">5</td>
        <td class="t2"><b>Эстрадиол проэструс</b> </td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"></td>
        <td class="t2 t22"></td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">анэструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P21_Estradanesvalue', $data) ?></td>
        <td class="t2 t22">0,157-0,267</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">проэструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P22_Estradproenstvalue', $data) ?></td>
        <td class="t2 t22">0,187-0,239</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">эструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P23_Estradestrusvalue', $data) ?></td>
        <td class="t2 t22">0,213-0,293</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">метэструс</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P24_Estradmetestvalue', $data) ?></td>
        <td class="t2 t22">0,055-0,077</td>
        <td class="t2 t22"></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22"></td>
        <td class="t2">самцы</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P25_Estradmalevalue', $data) ?></td>
        <td class="t2 t22">0,05-0,2</td>
        <td class="t2 t22"></td>
    </tr>

    <tr class="tr">
        <td class="t2 t22">6</td>
        <td class="t2"><b>Тестостерон</b> (самцы)</td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P26_Testostervalue', $data) ?></td>
        <td class="t2 t22">3,5-34</td>
        <td class="t2 t22">3,5-50</td>
    </tr>

    <tr class="tr">
        <td class="t2 t22">7</td>
        <td class="t2"><b>Тироксин (Т4)</b></td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P27_Thyroxvalue', $data) ?></td>
        <td class="t2 t22">12-50</td>
        <td class="t2 t22">10-50</td>
    </tr>

    <tr class="tr">
        <td class="t2 t22">8</td>
        <td class="t2"><b>Трийодтиронин (Т3)</b></td>
        <td class="t2 t22">nmol/l</td>
        <td class="t2 t22"><?= PdfGenerator::getValue('P28_Triiodtirvalue', $data) ?></td>
        <td class="t2 t22">0,7-2,3</td>
        <td class="t2 t22">0,5-2,0</td>
    </tr>
</table>

<br><br>
<div>
    <div class="t32" style="width: 120px;">Лаборант (ветврач)</div>
    <div class="t5" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 12px;">&nbsp;</div>
    <div class="t5" style="width: 300px;">( <span class="value"><?= $data['P33_SpecialistFIO'] ?? '&nbsp;' ?></span> )</div>
</div>
<div>
    <div class="t32" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 250px;">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>

