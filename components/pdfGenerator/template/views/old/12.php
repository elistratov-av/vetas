<?php
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 17 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта<br>
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
            <div class="title1">Печень:</div>
            <div class="tr2">
                <div class="t32" style="width: 88px;">Расположение</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P14_Pechenraspoloshenie', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Контуры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P15_Pechenkontur', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P16_Pechenrazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Эхоструктура</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P17_Pechenekhostruktura', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 145px;">Эхогенность паренхимы</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P18_Pechenekhogennost', $data) ?></span></div>
            </div>

        </div>

        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32">Периферический сосудистый рисунок <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P19_Pechenperifsosudrisunok', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 55px;">v. portae</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P20_Pechenportae', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 65px;">v. hepatica</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P21_Pechenvhepatica', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 65px;">a. hepatica</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P22_Pechenahepatica', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P23_Pechenobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="title1">Желчный пузырь:</div>
            <div class="tr2">
                <div class="t32" style="width: 125px;">Степень наполнения</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P24_Zhelchpuzyrstepennapolneniya', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 145px;">Форма желчного пузыря</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P25_Zhelchpuzyrformazhelchpuzyrya', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 96px;">Толщина стенки</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P26_Zhelchpuzyrtolshchinastenki', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 75px;">Деформация</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P27_Zhelchpuzyrdeformatsiya', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 104px;">Структура желчи</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P28_Zhelchpuzyrstrukturazhelchi', $data) ?></span></div>
            </div>
        </div>

        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32" style="width: 105px;">Пузырный проток</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P29_Zhelchpuzyrpuzyrprotok', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 141px;">Общий желчный проток</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P30_Zhelchpuzyrobshzhelchprotok', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 125px;">Печеночные протоки</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P31_Zhelchpuzyrpechenochnprotok', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P32_Zhelchpuzyrobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="title1">Селезенка:</div>
            <div class="tr2">
                <div class="t32" style="width: 88px;">Расположение</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P33_Selezenkaraspoloshenie', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 54px;">Контуры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P34_Selezenkakontur', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 54px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P35_Selezenkarazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Эхоструктура</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P36_Selezenkaekhostruktura', $data) ?></span></div>
            </div>
        </div>

        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32" style="width: 145px;">Эхогенность паренхимы</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P37_Selezenkaekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 125px;">Сосудистый рисунок</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P38_Selezenkasosudrisunok', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P39_Selezenkaobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="title1">Поджелудочная железа:</div>
            <div class="tr2">
                <div class="t32" style="width: 88px;">Расположение</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P40_Podzhelzhelezaraspoloshenie', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 54px;">Контуры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P41_Podzhelzhelezakontur', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 54px;">Размеры</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P42_Podzhelzhelezarazmer', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="width: 79px;">Эхоструктура</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P43_Podzhelzhelezaekhostruktura', $data) ?></span></div>
            </div>
        </div>

        <div class="t4">
            <br>
            <div class="tr2">
                <div class="t32" style="width: 145px;">Эхогенность</div>
                <div class="t5"><span class="value"><?= \Yii::$app->pdfGenerator->getValue('P44_Podzhelzhelezaekhogennost', $data) ?></span></div>
            </div>
            <div class="tr2">
                <div class="t32" style="">Объемные образования <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P45_Podzhelzhelezaobyemnobrazov', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="tr2">
                <div class="t32" style=""><b>Желудочно-кишечный тракт</b> <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P46_Zheludkishechntrakt', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>

    <div>
        <div class="t4">
            <div class="tr2">
                <div class="t32" style=""><b>Свободная жидкость в брюшной полости</b> <span class="underline"><?= \Yii::$app->pdfGenerator->getValue('P47_Svobodnzhidkost', $data) ?></span></div>
            </div>
        </div>
    </div>

    <br>
    <div>
        <div style="padding-bottom:10px;">Заключение</div>
        <?php if (isset($data['P48_Serviceresult'])) {
            echo $data['P48_Serviceresult'];
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

