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

<div class="title title3">Протокол ультразвукового исследования<br>
    № <?= $data['P2_SerialServiceNum'] ?? '' ?></div>

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

<div>
    <?php 
    error_reporting(0);
    echo $this->render('11_inner', compact('data', 'pdfGenerator'));
    ?>
    <br><br>
    <?php
    error_reporting(0);
    echo $this->render('12_inner', compact('data', 'pdfGenerator'));
    ?>
    <br><br>
    <div>
        <div class="t32" style="width: 100px;">Дата</div>
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
        <div class="t5" style="width: 300px;">( <span class="value"><?=PdfGenerator::getValue('P33_SpecialistFIO', $data)?></span> )</div>
    </div>
    <div>
        <div class="t32" style="width: 200px;">&nbsp;</div>
        <div class="t32" style="width: 250px;">подпись</div>
        <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
    </div>
</div>

