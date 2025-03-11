<?php
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 13 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">результат биохимического исследования кала</div>
<div class="title2">
    Экспертиза № <?= isset($data['P2_SerialServiceNum']) ? $data['P2_SerialServiceNum'] : '' ?>
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
        <td class="t2 t22 b" style="width:180px;height: 50px;">Показатели кала</td>
        <td class="t2 t22" style="width:150px;">Средние значения</td>
        <td class="t2 t22" style="width:160px;">Результат исследования</td>
        <td class="t2 t22">примечание</td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Консистенция, форма</td>
        <td class="t2">оформленные, некрошащийся</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P14_Coprformvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P15_Coprformdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Цвет</td>
        <td class="t2">коричневый</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P16_Coprcolorvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P17_Coprcolordesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Запах</td>
        <td class="t2">специфический, нерезкий</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P18_Coprodorvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P19_Coprodordesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Кислотность</td>
        <td class="t2">рН 5,5-7,0</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P20_Acidityvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P21_Aciditydesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Стеркобелин</td>
        <td class="t2">20-350 мг/в сутки</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P22_Stercobilinvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P23_Stercobilindesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Билирубин</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P24_Bilirubinvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P25_Bilirubindesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Кровь</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P26_Bloodvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P27_Blooddesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Микроскопия кала:</td>
        <td class="t2"></td>
        <td class="t2"></td>
        <td class="t2"></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">мышечные волокна</td>
        <td class="t2"></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P28_Muscledfibersvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P29_Muscledfibersdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">соединительно-тканные волокна</td>
        <td class="t2"></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P30_Contissuefibersvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P31_Contissuefibersdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">нейтральный жир</td>
        <td class="t2"></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P32_Neutralfatvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P33_Neutralfatdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">жирные кислоты</td>
        <td class="t2"></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P34_Fattyacidsvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P35_Fattyacidsdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">мыла</td>
        <td class="t2"></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P36_Soapvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P37_Soapdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">крахмал</td>
        <td class="t2"></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P38_Starchvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P39_Starchdesc', $data) ?></td>
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

