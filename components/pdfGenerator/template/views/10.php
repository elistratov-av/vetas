<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 15 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Ультразвуковое исследование глаза
    № <?= $data['P2_SerialServiceNum'] ?? '' ?>
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

<div style="padding-left:30px;">
    <div class="t4">
        <div class="title1">OD</div>
        <div class="tr2">
            <div class="t32" style="width: 178px;">Размер переднего отрезка оси</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P14_Odrazmerperednegootrezka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 163px;">Размер заднего отрезка оси</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P15_Odrazmerzadnegootrezka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура передней камеры <span class="underline"><?= PdfGenerator::getValue('P16_Odstructuraperedcamer', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 123px;">Размеры хрусталика</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P17_Odrazmerhrust', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура хрусталика <span class="underline"><?= PdfGenerator::getValue('P18_Odstructurahrust', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 120px;">Капсула хрусталика</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P19_Odcapsulahrust', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура стекловидного тела <span class="underline"><?= PdfGenerator::getValue('P20_Odstructurasteklotelo', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 180px;">Диаметр зрачкового отверстия</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P21_Oddiametrzrachka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32">Задняя стенка глазного яблока:</div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Контуры <span class="underline"><?= PdfGenerator::getValue('P22_Odcontur', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура <span class="underline"><?= PdfGenerator::getValue('P23_Odstructura', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура диска зрительного нерва <span class="underline"><?= PdfGenerator::getValue('P24_Odstructuradiskazritnerva', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура ретробульбарного пространства <span class="underline"><?= PdfGenerator::getValue('P25_Odstructuraretrobulyar', $data) ?></span></div>
        </div>
    </div>
    <div class="t4">
        <div class="title1">OS</div>
        <div class="tr2">
            <div class="t32" style="width: 178px;">Размер переднего отрезка оси</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P26_Osrazmerperednegootrezka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 163px;">Размер заднего отрезка оси</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P27_Osrazmerzadnegootrezka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура передней камеры <span class="underline"><?= PdfGenerator::getValue('P28_Osstructuraperedcamer', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 123px;">Размеры хрусталика</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P29_Osrazmerhrust', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура хрусталика <span class="underline"><?= PdfGenerator::getValue('P30_Osstructurahrust', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 120px;">Капсула хрусталика</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P31_Oscapsulahrust', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура стекловидного тела <span class="underline"><?= PdfGenerator::getValue('P32_Osstructurasteklotelo', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 180px;">Диаметр зрачкового отверстия</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P33_Osdiametrzrachka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32">Задняя стенка глазного яблока:</div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Контуры <span class="underline"><?= PdfGenerator::getValue('P34_Oscontur', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура <span class="underline"><?= PdfGenerator::getValue('P35_Osstructura', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура диска зрительного нерва <span class="underline"><?= PdfGenerator::getValue('P36_Osstructuradiskazritnerva', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="">Структура ретробульбарного пространства <span class="underline"><?= PdfGenerator::getValue('P37_Osstructuraretrobulyar', $data) ?></span></div>
        </div>
    </div>
    <br>
    <div>
        <div style="padding-bottom:10px;">Заключение</div>
        <?php if (isset($data['P38_Serviceresult'])) {
            echo $data['P38_Serviceresult'];
        }
        else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <?php } ?>
    </div>

    <br>
    <br>
    <div style="float:left;width: 300px;"><?= $data['P3_Visitstartdate'] ?? '' ?> г.</div>
    <div style="float:right;min-width: 300px;"><div class="t3" style="width: 120px;">Ветеринарный врач</div> <div class="t5"><?= $data['P33_SpecialistFIO'] ?? '&nbsp;' ?></div></div>
</div>

