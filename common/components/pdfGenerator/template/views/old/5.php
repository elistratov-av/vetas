<?php
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 5 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Результат клинического анализа мочи</div>
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
        <div class="t2">контактный телефон: <span class="value"><?= isset($data['P5_Ownercontact']) ? $data['P5_Ownercontact'] : '' ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:120px;">Животное: вид:</div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'собаки' ? '<span class="strike">&nbsp;собака&nbsp;</span>' : 'собака') : 'собака' ?></div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'кошки' ? '<span class="strike">&nbsp;кошка&nbsp;</span>' : 'кошка') : 'кошка' ?></div>
        <div class="t2" style="width:80px;"></div>
        <div class="t2" style="width:80px;">&nbsp;</div>

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
        <td class="t2 t22 b" style="width:180px;height: 50px;">Показатели мочи</td>
        <td class="t2 t22" style="width:150px;">Средние значения</td>
        <td class="t2 t22" style="width:160px;">Результат исследования</td>
        <td class="t2 t22">примечание</td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Цвет мочи</td>
        <td class="t2">желтая</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P14_Colorurinevalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P15_Colorurinedesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Прозрачность</td>
        <td class="t2">прозрачная</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P16_Transparencyvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P17_Transparencydesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Кислотность (рН)</td>
        <td class="t2"><b>5,5 – 6,5</b></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P18_Acidityvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P19_Aciditydesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Белок</td>
        <td class="t2">0,0-0,4 г/л</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P20_Proteinvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P21_Proteindesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Глюкоза</td>
        <td class="t2">0,0-1,5 ммоль/л</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P22_Glukozavalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P23_Glukozadesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Кетоновые тела</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P24_Ketonbodvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P25_Ketonboddesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Относительная плотность</td>
        <td class="t2">1,015-1,025</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P26_Relativedensityvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P27_Relativedensitydesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Билирубин</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P28_Bilirubinvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P29_Bilirubindesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Гемоглобин</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P30_Hemeglvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P31_Hemegldesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Мочевой осадок:</td>
        <td class="t2"></td>
        <td class="t2"></td>
        <td class="t2"></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">эритроциты</td>
        <td class="t2">единичные</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P32_Erythrocytvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P33_Erythrocytdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">лейкоциты</td>
        <td class="t2">0-5 в поле зрения</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P34_Leucocytvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P34_Leucocytvalue', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Эпителий:</td>
        <td class="t2"></td>
        <td class="t2"></td>
        <td class="t2"></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">плоский</td>
        <td class="t2">единичный</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P36_Ploskiyvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P37_Ploskiydesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">переходный</td>
        <td class="t2">единичный</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P38_Perehodvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P39_Perehoddesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">почечный</td>
        <td class="t2">единичный</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P40_Pochechnvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P41_Pochechndesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Цилиндры:</td>
        <td class="t2"></td>
        <td class="t2"></td>
        <td class="t2"></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">гиалиновые</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P42_Hyalinevalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P43_Hyalinedesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">зернистые</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P44_Granularvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P45_Granulardesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">восковидные</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P46_Waxvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P47_Waxdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">лейкоцитарные</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P48_Lekocitvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P49_Lekocitdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">эритроцитарные</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P50_Eritrocitvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P51_Eritrocitdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">эпителиальные</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P52_Epitelvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P53_Epiteldesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">цилиндроиды</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P54_Cilindvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P55_Cilinddesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Бактерии</td>
        <td class="t2">отсутствует</td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P56_Bacteriavalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P57_Bacteriadesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" colspan="4">Неорганизованный осадок:</td>
    </tr>
    <tr class="tr">
        <td class="t2 b">Соли</td>
        <td class="t2"></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P58_Saltvalue', $data) ?></td>
        <td class="t2"><?= \Yii::$app->pdfGenerator->getValue('P59_Saltdesc', $data) ?></td>
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


