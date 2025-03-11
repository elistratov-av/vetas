<?php
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 19 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Ультразвуковое исследование репродуктивной системы самца<br>
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
            <div class="title1">Предстательная железа</div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P14_Predstzhelezarazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Контуры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P15_Predstzhelezakontur', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 70px;">Паренхима</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P16_Predstzhelezaparenkhima', $data) ?></span></div>
            </div>

        </div>

        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P17_Predstzhelezaobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="title1">Правый семенник</div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P18_Pravsemrazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Контуры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P19_Pravsemkontur', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 70px;">Паренхима</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P20_Pravsemparenkhima', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P21_Pravsemobyemnobrazov', $data) ?></span></div>
            </div>
        </div>

        <div class="t4">
            <div class="title1">Придаток правого семенника</div>
            <div class="tr2">
                <div class="t32" style="width: 50px;">Головка</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P22_Pridatokpravsemgolovka', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 30px;">Тело</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P23_Pridatokpravsemtelo', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P24_Pridatokpravsemobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="title1">Левый семенник</div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P25_Levsemrazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Контуры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P26_Levsemkontur', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Паренхима</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P27_Levsemparenkhima', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P28_Levsemobyemnobrazov', $data) ?></span></div>
            </div>
        </div>

        <div class="t4">
            <div class="title1">Придаток левого семенника</div>
            <div class="tr2">
                <div class="t32" style="width: 50px;">Головка</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P29_Pridatoklevsemgolovka', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 30px;">Тело</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P30_Pridatoklevsemtelo', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P31_Pridatoklevsemobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>
    <div>
        <div style="padding-bottom:10px;">Заключение</div>
        <?php if (isset($data['P32_Abdomultmserviceresult'])) {
            echo $data['P32_Abdomultmserviceresult'];
        }
        else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <?php } ?>
    </div>

    <br>
    <div style="float:left;width: 300px;">
    <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = \Yii::$app->pdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
    ?>
    «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> г.
    <?php } else { ?>
    «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________201__г.
    <?php } ?>
    </div>
    <div style="float:right;min-width: 300px;"><div class="t3" style="width: 120px;">Ветеринарный врач</div> <div class="t5"><?= isset($data['P33_SpecialistFIO']) ? $data['P33_SpecialistFIO'] : '&nbsp;' ?></div></div>
</div>

