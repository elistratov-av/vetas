<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 21 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">ЭХО-кардиографическое исследование
    № <?= PdfGenerator::getValue('P2_SerialServiceNum', $data) ?>
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
        <div class="t2" style="width:290px;">Порода: <span class="value"><?= PdfGenerator::getValue('P7_Breedname', $data) ?></span></div>
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
        <div class="t2">Ветеринарное лечебное учреждение (подразделение): <span class="value"><?= PdfGenerator::getValue('P13_Orgshortname', $data) ?></span></div>
    </div>
</div>

<br>

<div style="padding-left:30px;">

    <div>
        <div class="t4">
            <div class="tr2">
                <div class="t32" style="width: 35px;">LVIDd</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P14_LVIDd', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 35px;">LVIDs</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P15_LVIDs', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 40px;">LVWTd</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P16_LVWTd', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 40px;">LVWTs</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P17_LVWTs', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 35px;">IVSTd</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P18_IVSTd', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 35px;">IVSTs</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P19_IVSTs', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 20px;">EF</div>
                <div class="t5" style="width: 100px;"><span class="value"><?= PdfGenerator::getValue('P20_EF', $data) ?></span></div>
                <div class="t32" style="width: 25px;text-align:right;">FS</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P21_FS', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 20px;">LA</div>
                <div class="t5" style="width: 60px;"><span class="value"><?= PdfGenerator::getValue('P22_LA', $data) ?></span></div>
                <div class="t32" style="width: 25px;text-align:right;">AO</div>
                <div class="t5" style="width: 60px;"><span class="value"><?= PdfGenerator::getValue('P23_AO', $data) ?></span></div>
                <div class="t32" style="width: 45px;text-align:right;">LA/AO</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P24_LA/AO', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Митральный клапан:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 53px;">Створки</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P35_Mitrklapnstvorki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 123px;">Скорость кровотока</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P36_Mitrklapnskorostkrovotoka', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 83px;">Регургитация</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P37_Mitrklapnregurgitatsiya', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Трикуспидальный клапан:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 53px;">Створки</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P38_Trikuspklapnstvorki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 123px;">Скорость кровотока</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P39_Trikuspklapnskorostkrovotoka', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 83px;">Регургитация</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P40_Trikuspklapnregurgitatsiya', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Аортальный клапан:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 53px;">Створки</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P41_Aortaklapnstvorki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 123px;">Скорость кровотока</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P42_Aortaklapnskorostkrovotoka', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 83px;">Регургитация</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P43_Aortaklapnregurgitatsiya', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Клапан легочной артерии:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 53px;">Створки</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P44_Klapnlegartstvorki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 123px;">Скорость кровотока</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P45_Klapnlegartskorostkrovotoka', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 83px;">Регургитация</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P46_Klapnlegartregurgitatsiya', $data) ?></span></div>
            </div>
        </div>

        <div class="t4">
            <div class="tr2">
                <div class="t32" style="width: 35px;">RVIDd</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P25_RVIDd', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 35px;">RVIDs</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P26_RVIDs', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 40px;">RVWTd</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P27_RVWTd', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 40px;">RVWTs</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P28_RVWTs', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 25px;">RA</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P29_RA', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 70px;">Дефект IVS</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P30_Defektivs', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 70px;">Дефект IAS</div>
                <div class="t5"><span class="value"><?= PdfGenerator::getValue('P31_Defektias', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 200px;">Свободная жидкость в перикарде</div>
                <div class="t5" ><span class="value"><?= PdfGenerator::getValue('P32_Svobodnzhidkostperikarde', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32">Свободная жидкость в плевральной полости</div>
                <div class="t5" ><span class="value"><?= PdfGenerator::getValue('P33_Svobodnzhidkostplevralpolosti', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 105px;">Новообразования</div>
                <div class="t5" ><span class="value"><?= PdfGenerator::getValue('P34_Novoobrazov', $data) ?></span></div>
            </div>

        </div>
    </div>


    <br>
    <div>
        <div style="padding-bottom:10px;">Заключение</div>
        <?php if (isset($data['P47_Serviceresult'])) {
            echo $data['P47_Serviceresult'];
        }
        else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <?php } ?>
    </div>

    <br>
    <div style="float:left;width: 300px;">
    <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = PdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
    ?>
    «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> г.
    <?php } else { ?>
    «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________201__г.
    <?php } ?>
    </div>
    <div style="float:right;min-width: 300px;"><div class="t3" style="width: 120px;">Ветеринарный врач</div> <div class="t5"><?=PdfGenerator::getValue('P33_SpecialistFIO', $data)?></div></div>
</div>

