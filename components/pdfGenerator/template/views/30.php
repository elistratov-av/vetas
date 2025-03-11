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
<div class="head2">Приложение 8 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Результат микроскопического исследования</div>
<div class="title2">экспертиза № <?= PdfGenerator::getValue('P0_Cytologicsanalysisnum', $data) ?></div>

<div class="clear"></div>

<div class="t1">
    <div class="tr">
        <div class="t2"><i>Ветеринарное лечебное учреждение (подразделение):</i> <?= PdfGenerator::getValue('P13_Orgshortname', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:380px;"><i>Ф. И. О. владельца:</i> <?= PdfGenerator::getValue('P4_Ownername', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2"><i>Адрес, телефон:</i> <?= PdfGenerator::getValue('P5_Owneraddres', $data) ?> <?= PdfGenerator::getValue('P5_Ownercontact', $data) ?></div>
    </div>
    <div class="tr">
        <div class="t2"><i>Сведения о животном:</i></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;"><i>Вид:</i> <?= PdfGenerator::getValue('P6_Speciesname', $data) ?></div>
        <div class="t2" style="width:200px;"><i>Пол:</i> <?= PdfGenerator::petSex($data) ?></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;"><i>Порода:</i> <?= PdfGenerator::getValue('P7_Breedname', $data) ?></div>
        <div class="t2" style="width:200px;"><i>Кличка:</i> <?= PdfGenerator::getValue('P9_Petname', $data) ?></div>
    </div>
    <div class="tr" style="border-bottom:0;">
        <div class="t2" style="width:92px;"><i>Возраст:</i> <?= PdfGenerator::petAge($data) ?></div>
    </div>

</div>

<br>

<div>
    <div><b><i>Материал для исследования:</i></b></div>
    <?= PdfGenerator::getValue('P111_Measurements', $data, '<div class="hr">&nbsp;</div>') ?>
</div>
<br>

<div>
    <div><b><i>Дата взятия материала:</i></b></div>
    <?= PdfGenerator::getValue('P18_Analysisdate', $data, '<div class="hr">&nbsp;</div>') ?>
</div>
<br>

<div>
    <div><b><i>Количество поступивших проб:</i></b></div>
    <?= PdfGenerator::getValue('P14_Analysiscount', $data, '<div class="hr">&nbsp;</div>') ?>
</div>
<br>

<div>
    <div><b><i>Ход и результаты исследования:</i></b></div>
    <?= PdfGenerator::getValue('P15_Analysisresult', $data, '<div class="hr">&nbsp;</div>') ?>
</div>

<br>
<div>
    <div><b><i>Примечание:</i></b></div>
    <?= PdfGenerator::getValue('P16_Analysisdesc', $data, str_repeat('<div class="hr">&nbsp;</div>', 3)) ?>
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
   <div style="float:right;min-width: 300px;"><div class="t32" style="width: 120px;">Ветеринарный врач</div> <div class="t5">( <?= PdfGenerator::getValue('P33_SpecialistFIO', $data) ?> )</div></div>
</div>





