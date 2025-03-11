<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\web\View;

/**
 * @var $this View
 * @var $data array
 */
?>
<div class="clear"></div>
<div class="title title3">Ультразвуковое исследование пищеварительной системы</div>
<br>
<div>
    <div class="title1">Печень</div>
    <div class="tr2">
        <div class="t32" style="width: 88px;">Расположение:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P14_Pechenraspoloshenie', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Контуры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P15_Pechenkontur', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Капсула:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P16_Capsula', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхоструктура:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P17_Pechenekhostruktura', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 145px;">Эхогенность паренхимы:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P18_Pechenekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 230px;">Периферический сосудистый рисунок:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P19_Pechenperifsosudrisunok', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">v. portae</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P20_Pechenportae', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 65px;">v. hepatica</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P21_Pechenvhepatica', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 65px;">a. hepatica</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P22_Pechenahepatica', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 170px;">Объемные образования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P23_Pechenobyemnobrazov', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="title1">Желчный пузырь</div>
    <div class="tr2">
        <div class="t32" style="width: 88px;">Расположение:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P24_Zhelchpuzyrraspoloshenie', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 125px;">Степень наполнения:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P24_Zhelchpuzyrstepennapolneniya', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 145px;">Форма желчного пузыря:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P25_Zhelchpuzyrformazhelchpuzyrya', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 100px;">Толщина стенки:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P26_Zhelchpuzyrtolshchinastenki', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 104px;">Структура желчи</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P28_Zhelchpuzyrstrukturazhelchi', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 105px;">Пузырный проток</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P29_Zhelchpuzyrpuzyrprotok', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 141px;">Общий желчный проток</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P30_Zhelchpuzyrobshzhelchprotok', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 125px;">Печеночные протоки</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P31_Zhelchpuzyrpechenochnprotok', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 170px;">Объемные образования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P32_Zhelchpuzyrobyemnobrazov', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="title1">Селезенка</div>
    <div class="tr2">
        <div class="t32" style="width: 88px;">Расположение:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P33_Selezenkaraspoloshenie', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 54px;">Контуры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P34_Selezenkakontur', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 54px;">Размеры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P35_Selezenkarazmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхоструктура:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P36_Selezenkaekhostruktura', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 145px;">Эхогенность паренхимы:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P37_Selezenkaekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 125px;">Сосудистый рисунок:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P38_Selezenkasosudrisunok', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 170px;">Объемные образования:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P39_Selezenkaobyemnobrazov', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="title1">Поджелудочная железа</div>
    <div class="tr2">
        <div class="t32" style="width: 88px;">Визуализация:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P42_Podzhelzhelezavisualization', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 88px;">Расположение:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P40_Podzhelzhelezaraspoloshenie', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 54px;">Контуры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P41_Podzhelzhelezakontur', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхоструктура:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P43_Podzhelzhelezaekhostruktura', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="tr2">
        <div class="t32 b">Желудочно-кишечный тракт</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P46_Zheludkishechntrakt', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="tr2">
        <div class="t32 b">Свободная жидкость в брюшной полости</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P47_Svobodnzhidkost', $data) ?></span></div>
    </div>
</div>

<br>
<div>
    <div style="padding-bottom:10px;"><b>Заключение:</b></div>
    <?php if (isset($data['P48_Serviceresult'])) {
        echo $data['P48_Serviceresult'];
    } else { ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
    <?php } ?>
</div>
