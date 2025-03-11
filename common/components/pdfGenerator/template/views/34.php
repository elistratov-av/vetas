<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this View
 * @var $data array
 */

?>
<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Ультразвуковое исследование щитовидной, паращитовидной железы
    <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = PdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
    ?>
    от «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> г.
    <?php } else { ?>
    от «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________201__ г.
    <?php } ?>
</div>

<div class="clear"></div>
<br>
<div class="t1">
    <div class="tr">
        <div class="t2" style="width:380px;">Ф. И. О. владельца: <span class="value"><?= $data['P4_Ownername'] ?? '' ?></span></div>
        <div class="t2">контактный телефон: <span class="value"><?= $data['P5_Ownercontact'] ?? '' ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:120px;">Животное: вид:</div>
        <div class="t2" style="width:80px;"><?= PdfGenerator::getValue('P6_Speciesname', $data) ?></div>
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
        <div class="t2" style="width:92px;">Возраст: <span class="value"><?= PdfGenerator::petAge($data) ?></span></div>
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
<div>
    <div class="title1">Правая доля щитовидной железы</div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Размер:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P1_Pravaya_dolya_shchitovidnoy_zhelezy_razmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Контуры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P2_Pravaya_dolya_shchitovidnoy_zhelezy_kontury', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 80px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P3_Pravaya_dolya_shchitovidnoy_zhelezy_echogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 110px;">Новообразования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P4_Pravaya_dolya_shchitovidnoy_zhelezy_novoobrazovaniya', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="title1">Левая доля щитовидной железы</div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Размер:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P5_Levaya_dolya_shchitovidnoy_zhelezy_razmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Контуры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P6_Levaya_dolya_shchitovidnoy_zhelezy_kontury', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 80px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P7_Levaya_dolya_shchitovidnoy_zhelezy_echogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 110px;">Новообразования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P8_Levaya_dolya_shchitovidnoy_zhelezy_novoobrazovaniya', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="title1">Паращитовидная железа</div>
    <div class="tr2">
        <div class="t32" style="width: 130px;">Левая доля. Размер:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P10_Parashchitovidnaya_zheleza_levaya_dolya_razmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 130px;">Правая доля. Размер:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P9_Parashchitovidnaya_zheleza_pravaya_dolya_razmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 95px;">Визуализация:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P11_Parashchitovidnaya_zheleza_vizualizatsiya', $data) ?></span></div>
    </div>
</div>

<div>
    <div><b><i>Примечание:</i></b></div>
    <?= PdfGenerator::getValue('P13_Serviceresultdesc', $data, str_repeat('<div class="hr">&nbsp;</div>', 3)) ?>
</div>
<div>
    <div><b><i>Заключение:</i></b></div>
    <?= PdfGenerator::getValue('P112_Serviceresult', $data, str_repeat('<div class="hr">&nbsp;</div>', 3)) ?>
</div>

<br><br>
<div>
    <div class="t32" style="width: 290px;">
        <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = PdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
        ?>
            «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> г.
        <?php } else { ?>
            «____»____________________201___г.
        <?php } ?>
    </div>
    <div style="float:right;min-width: 300px;">
        <div class="t32" style="width: 120px;">Ветеринарный врач</div>
        <div class="t5">( <?= PdfGenerator::getValue('P33_SpecialistFIO', $data) ?> )</div>
    </div>
</div>