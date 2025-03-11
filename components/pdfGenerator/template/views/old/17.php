<?php
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.moskomvet.ru</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 10 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Результат общего клинического анализа крови<br>
   экспертиза № <?= \Yii::$app->pdfGenerator->getValue('P2_SerialServiceNum', $data) ?>
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
        <div class="t2" style="width:120px;">Животное:</div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'собаки' ? '<span class="strike">&nbsp;собака&nbsp;</span>' : 'собака') : 'собака' ?></div>
        <div class="t2" style="width:80px;"><?= isset($data['P6_Speciesname']) ? (mb_strtolower($data['P6_Speciesname']) != 'кошки' ? '<span class="strike">&nbsp;кошка&nbsp;</span>' : 'кошка') : 'кошка' ?></div>
        <?php if (isset($data['P6_Speciesname'])) { ?>
        <div class="t2" style="width:110px;"><?= (!in_array(mb_strtolower($data['P6_Speciesname']), ['кошки', 'собаки'])) ? $data['P6_Speciesname'] : ''; ?></div>
        <?php } ?>
        <div class="t2" style="width:80px;">&nbsp;</div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;">Порода: <span class="value"><?= \Yii::$app->pdfGenerator->getValue('P7_Breedname', $data) ?></span></div>
        <div class="t2" style="width:92px;">Пол:</div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'f' ? '<span class="strike">&nbsp;M&nbsp;</span>' : 'M') : 'M' ?></b></div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'm' ? '<span class="strike">&nbsp;F&nbsp;</span>' : 'F') : 'F' ?></b></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:160px;">Кличка: <span class="value"><?= isset($data['P9_Petname']) ? $data['P9_Petname'] : '' ?></span></div>
        <div class="t2" style="width:92px;">Возраст: <span class="value"><?= Yii::$app->pdfGenerator::petAge($data); ?></span></div>
        <div class="t2" style="width:180px;color:#949494;"><b>Рег. № &nbsp;&nbsp;&nbsp; Журнала № <?= isset($data['P11_Regnum']) ? $data['P11_Regnum'] : '' ?></b></div>
    </div>
    <div class="tr">
        <div class="t2">Лечащий врач: <span class="value"><?= isset($data['P12_Attendingdoctor']) ? $data['P12_Attendingdoctor'] : '' ?></span></div>
    </div>
    <div class="tr" style="border-bottom:0;">
        <div class="t2">Ветеринарное лечебное учреждение (подразделение): <span class="value"><?= \Yii::$app->pdfGenerator->getValue('P13_Orgshortname', $data) ?></span></div>
    </div>
</div>

<br>

<table class="t1" cellpadding="0" cellspacing="0">
    <tr class="tr">
        <td class="t2 t22 b" style="width: 220px; height: 50px;">Показатели крови</td>
        <td class="t2 t22" style="width: 90px;">Единицы измерения</td>
        <td class="t2 t22" style="width: 90px;">Норма<br><b>СОБАКИ</b></td>
        <td class="t2 t22" style="width: 90px;">Норма<br><b>КОШКИ</b></td>
        <td class="t2 t22" style="width: 110px;">Результат исследования</td>
        <td class="t2 t22">Примечание</td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>WBC</b> <small>(общее кол-во лейкоцитов)</small></td>
        <td class="t2 t22">10<sup>9</sup>/l</td>
        <td class="t2 t22">6,0-12,0</td>
        <td class="t2 t22">5,5-19,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P14_Wbcvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P15_Wbcdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>LYM</b> <small>(лимфоциты)</small></td>
        <td class="t2 t22">10<sup>9</sup>/l</td>
        <td class="t2 t22">1,0-4,8</td>
        <td class="t2 t22">1,5-7,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P16_Lymvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P17_Lymdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>MON</b> <small>(моноциты)</small></td>
        <td class="t2 t22">10<sup>9</sup>/l</td>
        <td class="t2 t22">0,0-1,0</td>
        <td class="t2 t22">0,1-1,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P18_Monvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P19_Mondesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>GRA</b> <small>(гранулоциты)</small></td>
        <td class="t2 t22">10<sup>9</sup>/l</td>
        <td class="t2 t22">3,0-12,0</td>
        <td class="t2 t22">2,5-14,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P20_Gravalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P21_Gradesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>RBC</b> <small>(общее кол-во эритроцитов)</small></td>
        <td class="t2 t22">10<sup>12</sup>/l</td>
        <td class="t2 t22">5,50-12,00</td>
        <td class="t2 t22">5,00-10,00</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P22_Rbcvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P23_Rbcdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>HGB</b> <small>(гемоглобин)</small></td>
        <td class="t2 t22">g/l</td>
        <td class="t2 t22">120-180</td>
        <td class="t2 t22">80-150</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P24_Hgbvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P25_Hgbdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>HCT</b> <small>(гематокрит)</small></td>
        <td class="t2 t22">l/l</td>
        <td class="t2 t22">37,0-55,0<br>0,370-0,550</td>
        <td class="t2 t22">24,0-45,0<br>0,240-0,450</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P26_Hctvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P27_Hctdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>MCV</b> <small>(средний объем эритроцита)</small></td>
        <td class="t2 t22">fl</td>
        <td class="t2 t22">60,0-77,0</td>
        <td class="t2 t22">39,0-55,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P28_Mcvvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P29_Mcvdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>MCH</b> <small>(сод. гемоглобина в 1 эритроците)</small></td>
        <td class="t2 t22">pg</td>
        <td class="t2 t22">19,5-24,5</td>
        <td class="t2 t22">12,5-17,5</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P30_Mchvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P31_Mchdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>MCHC</b> <small>(конц. гемоглобина в 1 эритроците)</small></td>
        <td class="t2 t22">g/l</td>
        <td class="t2 t22">31,0-34,0<br>310-340</td>
        <td class="t2 t22">30,0-36,0<br>300-360</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P32_Mchcvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P33_Mchcdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>RDW</b> <small>(ширина распределения эритроцитов)</small></td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">10,0-16,0</td>
        <td class="t2 t22">10,0-16,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P34_Rdwvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P35_Rdwdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>PLT</b> <small>(тромбоциты)</small></td>
        <td class="t2 t22">10<sup>9</sup>/l</td>
        <td class="t2 t22">200-500</td>
        <td class="t2 t22">300-800</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P36_Pltvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P37_Pltdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>MPV</b> <small>(средний объем тромбоцита)</small></td>
        <td class="t2 t22">fl</td>
        <td class="t2 t22">7,0-11,0</td>
        <td class="t2 t22">7,0-11,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P38_Mpvvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P39_Mpvdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>PCT</b> <small>(тромбокрит)</small></td>
        <td class="t2 t22">cl/l</td>
        <td class="t2 t22">0,200-0,500</td>
        <td class="t2 t22">0,300-0,800</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P40_Pctvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P41_Pctdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"><b>PDW</b> <small>(ширина распределения тромбоцитов)</small></td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">10,0-18,0</td>
        <td class="t2 t22">10,0-18,0</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P42_Pdwvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P43_Pdwdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">СОЭ</td>
        <td class="t2 t22">мм/час</td>
        <td class="t2 t22">2-5</td>
        <td class="t2 t22">6-10</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P44_Soevalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P45_Soedesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2 t22 b" colspan="6">Лейкоцитарная формула:</td>
    </tr>
    <tr class="tr">
        <td class="t2 b" colspan="6">Нейтрофилы:</td>
    </tr>
    <tr class="tr">
        <td class="t2"> - Юные</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">0-1</td>
        <td class="t2 t22">0-1</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P46_Youngvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P47_Youngdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"> - Палочкоядерные</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">0-6</td>
        <td class="t2 t22">0-6</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P48_Palochkoyadervalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P49_Palochkoyaderdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2"> - Сегментоядерные</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">50-72</td>
        <td class="t2 t22">35-45</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P50_Segmentvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P51_Segmentdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">Эозинофилы</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">0-5</td>
        <td class="t2 t22">0-5</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P52_Eosinophilsvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P53_Eosinophilsdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">Моноциты</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">0-6</td>
        <td class="t2 t22">1-3</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P54_Monocitvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P55_Monocitdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">Базофилы</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">0-1</td>
        <td class="t2 t22">0-1</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P56_Bazophilvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P57_Bazophildesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">Лимфоциты</td>
        <td class="t2 t22">%</td>
        <td class="t2 t22">12-30</td>
        <td class="t2 t22">20-55</td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P58_Limphocitvalue', $data) ?></td>
        <td class="t2 t22"><?= \Yii::$app->pdfGenerator->getValue('P59_Limphocitdesc', $data) ?></td>
    </tr>
    <tr class="tr">
        <td class="t2">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
    </tr>
    <tr class="tr">
        <td class="t2">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
    </tr>
    <tr class="tr">
        <td class="t2">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
    </tr>
    <tr class="tr">
        <td class="t2">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
    </tr>
    <tr class="tr">
        <td class="t2">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
        <td class="t2 t22">&nbsp;</td>
    </tr>
</table>


    <br>
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
