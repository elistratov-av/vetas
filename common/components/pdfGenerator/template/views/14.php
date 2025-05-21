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

<div class="clear"></div>
<div class="title title3">Ультразвуковое исследование репродуктивной системы самца</div>
<br>

<div>
    <div>
        <div class="title1">Предстательная железа</div>
        <div class="tr2">
            <div class="t32" style="width: 58px;">Размеры:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P14_Predstzhelezarazmer', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 58px;">Контуры:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P15_Predstzhelezakontur', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 73px;">Паренхима:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P16_Predstzhelezaparenkhima', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 150px;">Объемные образования:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P17_Predstzhelezaobyemnobrazov', $data) ?></span></div>
        </div>
    </div>
    <div>
        <div class="title1">Правый семенник</div>
        <div class="tr2">
            <div class="t32" style="width: 58px;">Размеры:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P18_Pravsemrazmer', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 58px;">Контуры:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P19_Pravsemkontur', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 80px;">Паренхима:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P20_Pravsemparenkhima', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 150px;">Объемные образования:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P21_Pravsemobyemnobrazov', $data) ?></span></div>
        </div>
        <div class="title1">Придаток правого семенника</div>
        <div class="tr2">
            <div class="t32" style="width: 53px;">Головка:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P22_Pridatokpravsemgolovka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 33px;">Тело:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P23_Pridatokpravsemtelo', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 150px;">Объемные образования:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P24_Pridatokpravsemobyemnobrazov', $data) ?></span></div>
        </div>
    </div>
    <div>
        <div class="title1">Левый семенник</div>
        <div class="tr2">
            <div class="t32" style="width: 58px;">Размеры:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P25_Levsemrazmer', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 58px;">Контуры:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P26_Levsemkontur', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 80px;">Паренхима:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P27_Levsemparenkhima', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 150px;">Объемные образования:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P28_Levsemobyemnobrazov', $data) ?></span></div>
        </div>
        <div class="title1">Придаток левого семенника</div>
        <div class="tr2">
            <div class="t32" style="width: 53px;">Головка:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P29_Pridatoklevsemgolovka', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 33px;">Тело:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P30_Pridatoklevsemtelo', $data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t32" style="width: 150px;">Объемные образования:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::getValue('P31_Pridatoklevsemobyemnobrazov', $data) ?></span></div>
        </div>
    </div>

    <br>
    <div>
        <div style="padding-bottom:10px;"><b>Заключение:</b></div>
        <?php if (isset($data['P32_Abdomultmserviceresult'])) {
            echo $data['P32_Abdomultmserviceresult'];
        } else {
            ?>
            <div class="hr">&nbsp;</div>
            <div class="hr">&nbsp;</div>
            <div class="hr">&nbsp;</div>
        <?php } ?>
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
    <br>
    <div>
        <div class="t32" style="width: 120px;">&nbsp;</div>
        <div class="t32" style="width: 200px; padding: 0 0 0 5px;">&nbsp;</div>
        <div class="t32" style="width: 12px;">&nbsp;</div>
        <div class="t32" style="width: 100px; padding: 0 0 0 5px;">Дата</div>
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
</div>

