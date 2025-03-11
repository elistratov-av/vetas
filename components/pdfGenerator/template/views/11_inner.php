<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\web\View;

/**
 * @var $this View
 * @var $data array
 */
?>
<div class="clear"></div>
<div class="title title3">Ультразвуковое исследование мочевыделительной системы</div>
<br>
<div>
    <div class="title1">Правая почка</div>
    <div class="tr2">
        <div class="t32" style="width: 88px;">Расположение:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P14_Prvpochraspoloshenie', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Границы:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P15_Prvpochgranica', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Размеры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P16_Prvpochrazmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Кортикальный слой:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Толщина:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P17_Prvpochkortiksloytolshina', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P18_Prvpochkortiksloyekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхоструктура:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P19_Prvpochkortiksloyekhostruktura', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Медуллярный слой:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Толщина:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P20_Prvpochmedullyarsloytolshchina', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P21_Prvpochmedullyarsloyekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхоструктура:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P22_Prvpochmedullyarsloyekhostruktura', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 240px;">Кортико-медуллярная дифференциация</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P23_Prvpochmedullyarsloykortmeddiffer', $data) ?></span></div>
    </div>

    <div class="tr2">
        <div class="t32 b" style="width: 208px;">Паренхимо-пиелический индекс</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P24_Prvpochpiyelicheskiyindeks', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Почечный синус:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P25_Prvpochpochsinusekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 163px;">Четкость дифференциации:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P26_Prvpochpochsinuschetkostdifferents', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 101px;">Полость лоханки:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P27_Prvpochpochsinuspolostlokhanki', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 100px;">Стенки лоханки:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P28_Prvpochpochsinusstepenlokhanki', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 115px;">Сосуды паренхимы:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P29_Prvpochsosudyparenkhimy', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Индекс резистентности:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 175px;">На участке почечной артерии:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P30_PrvpochIndeksrezistivnpochechnart', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 195px;">На участке междолевой артерии:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P31_PrvpochIndeksrezistivnmezhdolevoyart', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b" style="width: 95px;">Конкременты</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P32_Prvpochkonkrementy', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b" style="width: 170px;">Объемные образования</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P33_Prvpochobyemnobrazov', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="title1">Левая почка</div>
    <div class="tr2">
        <div class="t32" style="width: 88px;">Расположение:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P34_Levpochraspoloshenie', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Границы:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P35_Levpochgranica', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Размеры:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P36_Levpochrazmer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Кортикальный слой:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Толщина:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P37_Levpochkortiksloytolshina', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P38_Levpochkortiksloyekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхоструктура:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P39_Levpochkortiksloyekhostruktura', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Медуллярный слой:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 55px;">Толщина:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P40_Levpochmedullyarsloytolshchina', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P41_Levpochmedullyarsloyekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхоструктура:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P42_Levpochmedullyarsloyekhostruktura', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 240px;">Кортико-медуллярная дифференциация:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P43_Levpochmedullyarsloykortmeddiffer', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b" style="width: 208px;">Паренхимо-пиелический индекс</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P44_Levpochpiyelicheskiyindeks', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Почечный синус:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Эхогенность:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P45_Levpochpochsinusekhogennost', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 163px;">Четкость дифференциации:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P46_Levpochpochsinuschetkostdifferents', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 101px;">Полость лоханки:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P47_Levpochpochsinuspolostlokhanki', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 100px;">Стенки лоханки:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P48_Levpochpochsinusstenkilokhanki', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 115px;">Сосуды паренхимы:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P49_Levpochsosudyparenkhimy', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b">Индекс резистентности:</div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 175px;">На участке почечной артерии:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P50_LevpochIndeksrezistivnpochechnart', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 195px;">На участке междолевой артерии:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P51_LevpochIndeksrezistivnmezhdolevoyart', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b" style="width: 95px;">Конкременты</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P52_Levpochkonkrementy', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b" style="width: 170px;">Объемные образования</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P53_Levpochobyemnobrazov', $data) ?></span></div>
    </div>
</div>

<br>

<div>
    <div class="title1">Мочевой пузырь</div>
    <div class="tr2">
        <div class="t32" style="width: 132px;">Степень наполнения:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P54_Mochpuzstepnapoln', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 100px;">Толщина стенки:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P55_Mochpuztolshchinastenki', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32" style="width: 83px;">Деформация:</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P56_Mochpuzdeformatsiya', $data) ?></span></div>
    </div>

    <div class="tr2">
        <div class="t32 b" style="width: 63px;">Уретра</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P57_Mochpuzuretra', $data) ?></span></div>
    </div>
    <div class="tr2">
        <div class="t32 b" style="width: 170px;">Объемные образования</div>
        <div class="t5"><span class="value"><?= PdfGenerator::getValue('P58_MochpuzObyemnobrazov', $data) ?></span></div>
    </div>
</div>
