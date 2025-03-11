<?php
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 1 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title">результат биохимического исследования крови</div>
<div class="title2">
    экспертиза № <?= isset($data['P2_SerialServiceNum']) ? $data['P2_SerialServiceNum'] : '' ?>
    <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = \Yii::$app->pdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
    ?>
    от «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> года
    <?php } else { ?>
    от «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________201.. года
    <?php } ?>
</div>

<div class="clear"></div>

<div class="t1">
    <div class="tr">
        <div class="t2" style="width:380px;">Ф. И. О. владельца: <span class="value"><?= isset($data['P4_Ownername']) ? $data['P4_Ownername'] : '' ?></span></div>
        <div class="t2">его контактный телефон: <span class="value"><?= isset($data['P5_Ownercontact']) ? $data['P5_Ownercontact'] : '' ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:120px;">Животное: вид:</div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'собаки' ? '<span class="strike">&nbsp;собака&nbsp;</span>' : 'собака') : 'собака' ?></div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'кошки' ? '<span class="strike">&nbsp;кошка&nbsp;</span>' : 'кошка') : 'кошка' ?></div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'лошадь' ? '<span class="strike">&nbsp;лошадь&nbsp;</span>' : 'лошадь') : 'лошадь' ?></div>
        <div class="t2" style="width:80px;">&nbsp;</div>
        <div class="t2" >ненужное зачеркнуть</div>
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


<table class="t1" cellpadding="0" cellspacing="0">
    <tr class="tr">
        <td class="t2 t22 b" style="width:180px;height: 50px;">биохимические показатели крови</td>
        <td class="t2 t22" style="width:70px;">Единицы измерения</td>
        <td class="t2 t22" style="width:120px;">Коэффициент пересчета в ед. СИ</td>
        <td class="t2 t22" style="width:70px;">норма <b>СОБАКИ</b></td>
        <td class="t2 t22" style="width:70px;">норма <b>КОШКИ</b></td>
        <td class="t2 t22" style="width:110px;">результат исследования</td>
        <td class="t2 t22">примечание</td>
    </tr>
    <tr class="tr">
        <td class="t2 b" rowspan="2">билирубин общий</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">3-13,5</td>
        <td class="t2 t22">3.0-12,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P14_Totalbilirubinmcmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P15_Totalbilirubinmcmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*17,1</td>
        <td class="t2 t22">0,18-0,79</td>
        <td class="t2 t22">0,18-0,7</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P16_Totalbilirubinmgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P17_Totalbilirubinmgdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b" rowspan="2">билирубин прямой</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">0-5,5</td>
        <td class="t2 t22">0-5,5</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P18_Conjugatedbilirubinmcmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P19_Conjugatedbilirubinmcmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*17,1</td>
        <td class="t2 t22">0-0,32</td>
        <td class="t2 t22">0-0,32</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P20_Conjugatedbilirubinmgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P21_Conjugatedbilirubinmgdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">АЛТ аланиниаминотрансфераза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">9-52</td>
        <td class="t2 t22">19-79</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P22_Altalanniamvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P23_Altalanniamvadesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">АСТ аспартатаминотрансфераза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">11-42</td>
        <td class="t2 t22">9-29</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P24_Astaspartvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P25_Astaspartdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">мочевина</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">3,5-9,2l</td>
        <td class="t2 t22">5,5-9,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P26_Mochevinammvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P27_Mochevinammdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*0,3570</td>
        <td class="t2 t22">9,8-25,8</td>
        <td class="t2 t22">15,41-25,21</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P28_Mochevinamgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P29_Mochevinamgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">креатинин</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">26-120</td>
        <td class="t2 t22">70-165</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P30_Creatininemcmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P31_Creatininemcmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*88,40</td>
        <td class="t2 t22">0,29-1,36</td>
        <td class="t2 t22">0,79-1,87</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P32_Creatininemgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P33_Creatininemgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">Щелочная фософотаза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">18-70</td>
        <td class="t2 t22">39-55</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P34_Shelochfosfatvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P35_Shelochfosfatdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">ά- амилаза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">685-2155</td>
        <td class="t2 t22">580-1720</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P36_Amilazavalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P37_Amilazadesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">Панкреатическая амилаза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">200-700</td>
        <td class="t2 t22">300-800</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P38_Pancreatinevalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P39_Pancreatinedesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">глюкоза</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">4,3-7,3</td>
        <td class="t2 t22">3,3-6,3</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P40_Glukozamcmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P41_Glukozamcmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*0,05551</td>
        <td class="t2 t22">77,46-131,51</td>
        <td class="t2 t22">59,45-113,49</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P42_Glukozamgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P43_Glukozamgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">ЛДГ лактодегидрогиназа</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">23-164</td>
        <td class="t2 t22">55-155</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P44_Ldglactodvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P45_Ldglactoddesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">ГГТ гамма-глутамилтрансфераза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">1-10</td>
        <td class="t2 t22">1-10</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P46_Lgtgammavalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P47_Lgtgammadesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">КФК креатинфосфокиназа</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">32,0-157,0</td>
        <td class="t2 t22">150,0-440,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P48_Kfkcreatinevalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P49_Kfkcreatinedesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">холестерол</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">2,9-6,5</td>
        <td class="t2 t22">1,6-3,7</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P50_Holestermmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P51_Holestermmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*0,0258</td>
        <td class="t2 t22">112,4-251,94</td>
        <td class="t2 t22">62,02-143,41</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P52_Holestermgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P53_Holestermgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">триглицериды</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">0,24-0,98</td>
        <td class="t2 t22">0,38-1,10</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P54_Triglyceridsmmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P55_Triglyceridsmmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*0,0113</td>
        <td class="t2 t22">21,24-86,73</td>
        <td class="t2 t22">33,63-97,35</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P56_Triglyceridsmgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P57_Triglyceridsmgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">калий</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">4,3-6,2</td>
        <td class="t2 t22">4,1-5,4</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P58_Caliummmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P59_Caliummmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мэкв/дл</td>
        <td class="t2 t22">*1,0</td>
        <td class="t2 t22">4,3-6,2</td>
        <td class="t2 t22">4,1-5,4</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P60_Caliummecvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P61_Caliummecdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">натрий</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">138-164</td>
        <td class="t2 t22">143-165</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P62_Natrmmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P63_Natrmmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мэкв/дл</td>
        <td class="t2 t22">*1,0</td>
        <td class="t2 t22">138-164</td>
        <td class="t2 t22">143-165</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P64_Natrmecvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P65_Natrmecdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">фосфор</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">1,13-3,0</td>
        <td class="t2 t22">1,1-2,3</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P66_Phosphormmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P67_Phosphormmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*0,3229</td>
        <td class="t2 t22">3,5-9,29</td>
        <td class="t2 t22">3,41-7,12</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P68_Phosphormgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P69_Phosphormgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">кальций</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">2,3-3,3</td>
        <td class="t2 t22">2,0-2,7</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P70_Calciummmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P71_Calciummmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*0,2495</td>
        <td class="t2 t22">9,22-13,23</td>
        <td class="t2 t22">8,02-10,82</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P72_Calciummcgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P73_Calciummcgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">железо</td>
        <td class="t2">мкмоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">20-30</td>
        <td class="t2 t22">20-30</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P74_Ironmcmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P75_Ironmcmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мкг/дл</td>
        <td class="t2 t22">*0,1791</td>
        <td class="t2 t22">111,67-167,5</td>
        <td class="t2 t22">111,67-167,5</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P76_Ironmcgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P77_Ironmcgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">магний</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">0,8-1,4</td>
        <td class="t2 t22">0,9-1,6</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P78_Magnesiummmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P79_Magnesiummmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мэкв/дл</td>
        <td class="t2 t22">*0,4114</td>
        <td class="t2 t22">1,95-3,40</td>
        <td class="t2 t22">2,19-3,89</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P80_Magnesiummecvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P81_Magnesiummecdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">хлорид</td>
        <td class="t2">ммоль/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">96-118</td>
        <td class="t2 t22">107-122</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P82_Chloridemmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P83_Chloridemmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мэкв/дл</td>
        <td class="t2 t22">*1,0</td>
        <td class="t2 t22">96-118</td>
        <td class="t2 t22">107-122</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P84_Chloridemecvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P85_Chloridemecdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">кислотность</td>
        <td class="t2">РН</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">7,35-7,45</td>
        <td class="t2 t22">7,35-7,45</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P86_Acidityvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P87_Aciditydesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">мочевая кислота</td>
        <td class="t2"><b>нмоль/л</b></td>
        <td class="t2 t22"></td>
        <td class="t2 t22"></td>
        <td class="t2 t22">0-0,0714</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P88_Mochekislnmvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P89_Mochekislnmdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">мг/дл</td>
        <td class="t2 t22">*0,3570</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">0-0,2</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P90_Mochekislmgvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P91_Mochekislmgdesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">липаза</td>
        <td class="t2">ЕД/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">до 50</td>
        <td class="t2 t22">до 50</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P92_Lipazavalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P93_Lipazadesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">Общий белок</td>
        <td class="t2">г/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">40-73</td>
        <td class="t2 t22">54-77</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P94_Totalproteinglvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P95_Totalproteingldesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">г/дл</td>
        <td class="t2 t22">*10</td>
        <td class="t2 t22">400-730</td>
        <td class="t2 t22">540-770</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P96_Totalproteingdlvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P97_Totalproteingdldesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b" rowspan="2">Альбумины</td>
        <td class="t2">г/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">22-39</td>
        <td class="t2 t22">25-37</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P98_Albuminglvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P99_Albumingldesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">г/дл</td>
        <td class="t2 t22">*10</td>
        <td class="t2 t22">220-390</td>
        <td class="t2 t22">250-370</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P100_Albumingdlvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P101_Albumingdldesc', $data) ?></td>
    </tr>

    <tr class="tr">
        <td class="t2 b">Гемоглобин</td>
        <td class="t2">г/л</td>
        <td class="t2 t22"></td>
        <td class="t2 t22">110-170</td>
        <td class="t2 t22">80-150</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P102_Hemoglobinvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P103_Hemoglobindesc', $data) ?></td>
    </tr>

</table>

<br><br>
<div>
    <div class="t32" style="width: 120px;">Лаборант (ветврач)</div>
    <div class="t5" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 12px;">&nbsp;</div>
    <div class="t5" style="width: 300px;">( <span class="value"><?= isset($data['P33_SpecialistFIO']) ? $data['P33_SpecialistFIO'] : '&nbsp;' ?></span> )</div>
</div>
<div>
    <div class="t32" style="width: 200px;">&nbsp;</div>
    <div class="t32" style="width: 250px;">подпись</div>
    <div class="t32" style="width: 100px;">Ф.И.О. врача</div>
</div>

