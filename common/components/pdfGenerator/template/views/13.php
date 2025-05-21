<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this View
 * @var $data array
 */
?>

<div style="float:left;width: 150px; text-align:center;padding-top: 40px;">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div style="color:#1F324D;font-weight:bold;padding-top:15px;">www.mos.ru/moskomvet</div>
</div>
<div style="float:left;width: 340px;font-weight:bold;font-size: 14px;padding-top: 40px;">Государственная ветеринарная служба  города Москвы</div>
<div style="float:right;width:150px;padding-top:15px;font-size: 10px;color:#949494;">штамп организации проводившей исследование</div>

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
<div class="title title3">Ультразвуковое исследование репродуктивной системы самки</div>
<br>

<div>
    <div class="title1">Матка:</div>
    <div class="tr2">
        <div class="t3" style="width: 85px;">Диаметр тела:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P14_Matkadiametrtela', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 145px;">Толщина стенки матки:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P15_Matkatolshinatela', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 137px;">Структура стенки тела:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P16_Matkastrukturastenkitela', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 117px;">Состояние полости:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P17_Matkasostoyanpolosti', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 137px;">Диаметр правого рога:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P18_Matkadiametrpravroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 177px;">Толщина стенки правого рога:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P19_Matkatolshinapravroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 187px;">Структура стенки правого рога:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P20_Matkastrukturastenkipravroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 127px;">Содержимое полости:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P21_Matkasoderzhimpolostipravroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 127px;">Диаметр левого рога:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P22_Matkadiametrlevroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 173px;">Толщина стенки левого рога:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P23_Matkatolshinalevroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 183px;">Структура стенки левого рога:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P24_Matkastrukturastenkilevroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 127px;">Содержимое полости:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P25_Matkasoderzhimpolostilevroga', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 107px;">Новообразования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P25_Matkanovoobrazov', $data) ?></span></div>
    </div>

    <div style="height:10px;">&nbsp;</div>
    <div class="title1">Правый яичник:</div>
    <div class="tr2">
        <div class="t3" style="width: 57px;">Размеры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P26_Pravyaichnikrazmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 57px;">Контуры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P27_Pravyaichnikkontur', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 107px;">Новообразования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P28_Pravyaichniknovoobrazov', $data) ?></span></div>
    </div>

    <div style="height:10px;">&nbsp;</div>
    <div class="title1">Левый яичник:</div>
    <div class="tr2">
        <div class="t3" style="width: 57px;">Размеры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P29_Levyaichnikrazmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 57px;">Контуры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P30_Levyaichnikkontur', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 107px;">Новообразования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P31_Levyaichniknovoobrazov', $data) ?></span></div>
    </div>

    <br>
    <div>
        <div><b>Заключение:</b></div>
        <?php if (isset($data['P32_Serviceresult'])) {
            echo $data['P32_Serviceresult'];
        } else {
            ?>
            <div class="hr">&nbsp;</div>
            <div class="hr">&nbsp;</div>
            <div class="hr">&nbsp;</div>
        <?php } ?>
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
