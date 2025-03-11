<?php
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 16 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Ультразвуковое исследование мочевыделительной системы<br>
    № <?= isset($data['P2_SerialServiceNum']) ? $data['P2_SerialServiceNum'] : '' ?>
    <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = \Yii::$app->pdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
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
        <div class="t2" style="width:380px;">Ф. И. О. владельца: <span class="value"><?= isset($data['P4_Ownername']) ? $data['P4_Ownername'] : '' ?></span></div>
        <div class="t2">контактный телефон: <span class="value"><?= isset($data['P5_Ownercontact']) ? $data['P5_Ownercontact'] : '' ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:120px;">Животное: вид:</div>
        <div class="t2" style="width:80px;"><?= \Yii::$app->pdfGenerator->getValue('P6_Speciesname', $data) ?></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;">Порода: <span class="value"><?= isset($data['P7_Breedname']) ? $data['P7_Breedname'] : '' ?></span></div>
        <div class="t2" style="width:92px;">Пол:</div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'f' ? '<span class="strike">&nbsp;M&nbsp;</span>' : 'M') : 'M' ?></b></div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'm' ? '<span class="strike">&nbsp;F&nbsp;</span>' : 'F') : 'F' ?></b></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:130px;">Кличка: <span class="value"><?= isset($data['P9_Petname']) ? $data['P9_Petname'] : '' ?></span></div>
        <div class="t2" style="width:220px;color:#949494;">Идентификационный № <span class="value"><?= isset($data['P0_Petchpidentificationcode']) ? $data['P0_Petchpidentificationcode'] : '' ?></span></div>
        <div class="t2" style="width:92px;">Возраст: <span class="value"><?= Yii::$app->pdfGenerator::petAge($data); ?></span></div>
        <div class="t2" style="width:180px;color:#949494;"><b>Рег. № &nbsp;&nbsp;&nbsp; Журнала № <?= isset($data['P11_Regnum']) ? $data['P11_Regnum'] : '' ?></b></div>
    </div>
    <div class="tr">
        <div class="t2">Лечащий врач: <span class="value"><?= isset($data['P12_Attendingdoctor']) ? $data['P12_Attendingdoctor'] : '' ?></span></div>
    </div>
    <div class="tr" style="border-bottom:0;">
        <div class="t2">Ветеринарное лечебное учреждение (подразделение): <span class="value"><?= isset($data['P13_Orgshortname']) ? $data['P13_Orgshortname'] : '' ?></span></div>
    </div>
</div>

<br>

<div style="padding-left:30px;">
    <div>
        <div class="t4">
            <div class="title1">Правая почка:</div>
            <div class="tr2">
                <div class="t32" style="width: 88px;">Расположение</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P14_Prvpochraspoloshenie', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Границы</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P15_Prvpochgranica', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P16_Prvpochrazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Кортикальный слой:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Толщина</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P17_Prvpochkortiksloytolshina', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 75px;">Эхогенность</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P18_Prvpochkortiksloyekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Эхоструктура</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P19_Prvpochkortiksloyekhostruktura', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Медуллярный слой:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Толщина</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P20_Prvpochmedullyarsloytolshchina', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 75px;">Эхогенность</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P21_Prvpochmedullyarsloyekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Эхоструктура</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P22_Prvpochmedullyarsloyekhostruktura', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Кортико-медуллярная дифференциация <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P23_Prvpochmedullyarsloykortmeddiffer', $data) ?></span></div>
            </div>

        </div>

        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32 b" style="width: 208px;">Паренхимо-пиелический индекс</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P24_Prvpochpiyelicheskiyindeks', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Почечный синус:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 75px;">Эхогенность</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P25_Prvpochpochsinusekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 163px;">Четкость дифференциации</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P26_Prvpochpochsinuschetkostdifferents', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 101px;">Полость лоханки</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P27_Prvpochpochsinuspolostlokhanki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 100px;">Стенки лоханки</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P28_Prvpochpochsinusstepenlokhanki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 115px;">Сосуды паренхимы</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P29_Prvpochsosudyparenkhimy', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Индекс резистивности:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 175px;">На участке почечной артерии</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P30_PrvpochIndeksrezistivnpochechnart', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 195px;">На участке междолевой артерии</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P31_PrvpochIndeksrezistivnmezhdolevoyart', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b" style="width: 95px;">Конкременты</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P32_Prvpochkonkrementy', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style=""><b>Объемные образования</b> <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P33_Prvpochobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="title1">Левая почка:</div>
            <div class="tr2">
                <div class="t32" style="width: 88px;">Расположение</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P34_Levpochraspoloshenie', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Границы</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P35_Levpochgranica', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P36_Levpochrazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Кортикальный слой:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Толщина</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P37_Levpochkortiksloytolshina', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 75px;">Эхогенность</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P38_Levpochkortiksloyekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Эхоструктура</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P39_Levpochkortiksloyekhostruktura', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Медуллярный слой:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Толщина</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P40_Levpochmedullyarsloytolshchina', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 75px;">Эхогенность</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P41_Levpochmedullyarsloyekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Эхоструктура</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P42_Levpochmedullyarsloyekhostruktura', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Кортико-медуллярная дифференциация <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P43_Levpochmedullyarsloykortmeddiffer', $data) ?></span></div>
            </div>
        </div>

        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32 b" style="width: 208px;">Паренхимо-пиелический индекс</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P44_Levpochpiyelicheskiyindeks', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Почечный синус:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 75px;">Эхогенность</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P45_Levpochpochsinusekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 163px;">Четкость дифференциации</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P46_Levpochpochsinuschetkostdifferents', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 101px;">Полость лоханки</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P47_Levpochpochsinuspolostlokhanki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 100px;">Стенки лоханки</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P48_Levpochpochsinusstenkilokhanki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 115px;">Сосуды паренхимы</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P49_Levpochsosudyparenkhimy', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b">Индекс резистивности:</div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 175px;">На участке почечной артерии</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P50_LevpochIndeksrezistivnpochechnart', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 195px;">На участке междолевой артерии</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P51_LevpochIndeksrezistivnmezhdolevoyart', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32 b" style="width: 95px;">Конкременты</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P52_Levpochkonkrementy', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style=""><b>Объемные образования</b> <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P53_Levpochobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="title1">Мочевой пузырь:</div>
            <div class="tr2">
                <div class="t32" style="width: 123px;">Степень наполнения</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P54_Mochpuzstepnapoln', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 96px;">Толщина стенки</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P55_Mochpuztolshchinastenki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 77px;">Деформация</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P56_Mochpuzdeformatsiya', $data) ?></span></div>
            </div>
        </div>
        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32 b" style="width: 63px;">Уретра</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P57_Mochpuzuretra', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style=""><b>Объемные образования</b> <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P58_MochpuzObyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>
    <div>
        <div style="padding-bottom:10px;">Заключение</div>
        <?php if (isset($data['P59_Serviceresult'])) {
            echo $data['P59_Serviceresult'];
        }
        else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <?php } ?>
    </div>

    <br>
    <div style="float:left;width: 300px;"><?= isset($data['P3_Visitstartdate']) ? $data['P3_Visitstartdate'] : '' ?> г.</div>
    <div style="float:right;min-width: 300px;"><div class="t3" style="width: 120px;">Ветеринарный врач</div> <div class="t5"><?= isset($data['P33_SpecialistFIO']) ? $data['P33_SpecialistFIO'] : '&nbsp;' ?></div></div>
</div>

