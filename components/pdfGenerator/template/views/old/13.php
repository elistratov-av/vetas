<?php
use yii\helpers\Url;
?>

<div style="float:left;width: 150px; text-align:center;padding-top: 40px;">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div style="color:#1F324D;font-weight:bold;padding-top:15px;">www.moskomvet.ru</div>
</div>
<div style="float:left;width: 340px;font-weight:bold;font-size: 14px;padding-top: 40px;">Государственная ветеринарная служба  города Москвы</div>
<div style="float:right;width:180px;padding-top:10px;">Приложение 18 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div style="float:right;width:150px;padding-top:15px;font-size: 10px;color:#949494;">штамп организации проводившей исследование</div>
<div style="clear:both;text-align:center;padding-top: 20px;font-weight:bold;">Ультразвуковое исследование репродуктивной системы самки № <span><?= isset($data['P2_SerialServiceNum']) ? $data['P2_SerialServiceNum'] : '' ?></span> от <?= isset($data['P3_Visitstartdate']) ? $data['P3_Visitstartdate'] : '' ?> г.</div>
<br>

<div class="t1">
    <div class="tr">
        <div class="t2" style="width:380px;">Ф. И. О. владельца: <span class="value"><?= isset($data['P4_Ownername']) ? $data['P4_Ownername'] : '' ?></span></div>
        <div class="t2">его контактный телефон: <span class="value"><?= isset($data['P5_Ownercontact']) ? $data['P5_Ownercontact'] : '' ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:120px;">Животное:</div>
        <div class="t2" style="width:90px;text-align:center;">вид:</div>
        <div class="t2" style="width:80px;"><span class="value"><?= isset($data['P6_Speciesname']) ? $data['P6_Speciesname'] : '' ?></span>&nbsp;</div>
        <div class="t2" style="width:80px;"><span class="value"><?= isset($data['pet2']) ? $data['pet2'] : '' ?></span>&nbsp;</div>
        <div class="t2" style="width:80px;"><span class="value"><?= isset($data['pet3']) ? $data['pet3'] : '' ?></span>&nbsp;</div>
        <div class="t2" style="width:80px;"><span class="value"><?= isset($data['pet4']) ? $data['pet4'] : '' ?></span>&nbsp;</div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;">Порода: <span class="value"><?= isset($data['P7_Breedname']) ? $data['P7_Breedname'] : '' ?></span></div>
        <div class="t2" style="width:92px;">Пол:</div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'f' ? '<span class="strike">&nbsp;M&nbsp;</span>' : 'M') : 'M' ?></b></div>
        <div class="t2" style="width:80px;"><b><?= isset($data['P8_Petsex']) ? (strtolower($data['P8_Petsex']) == 'm' ? '<span class="strike">&nbsp;F&nbsp;</span>' : 'F') : 'F' ?></b></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:130px;">Кличка: <span class="value"><?= isset($data['P9_Petname']) ? $data['P9_Petname'] : '' ?></span></div>
        <div class="t2" style="width:154px;color:#949494;">Идентификационный № <span class="value"><?= isset($data['P0_Petchpidentificationcode']) ? $data['P0_Petchpidentificationcode'] : '' ?></span></div>
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
    <div class="t4">
        <div class="title1">Матка:</div>
        <div class="tr2">
            <div class="t3" style="width: 83px;">Диаметр тела</div>
            <div class="t5"><span class="value"><?= isset($data['P14_Matkadiametrtela']) ? $data['P14_Matkadiametrtela'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 130px;">Толщина стенки тела</div>
            <div class="t5"><span class="value"><?= isset($data['P15_Matkatolshinatela']) ? $data['P15_Matkatolshinatela'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 135px;">Структура стенки тела</div>
            <div class="t5"><span class="value"><?= isset($data['P16_Matkastrukturastenkitela']) ? $data['P16_Matkastrukturastenkitela'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 115px;">Состояние полости</div>
            <div class="t5"><span class="value"><?= isset($data['P17_Matkasostoyanpolosti']) ? $data['P17_Matkasostoyanpolosti'] : '&nbsp;' ?></span></div>
        </div>
        <div style="height:10px;">&nbsp;</div>
        <div class="title1">Правый яичник:</div>
        <div class="tr2">
            <div class="t3" style="width: 55px;">Размеры</div>
            <div class="t5"><span class="value"><?= isset($data['P26_Pravyaichnikrazmer']) ? $data['P26_Pravyaichnikrazmer'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 55px;">Контуры</div>
            <div class="t5"><span class="value"><?= isset($data['P27_Pravyaichnikkontur']) ? $data['P27_Pravyaichnikkontur'] : '&nbsp;' ?></span></div>
        </div>
        <div class="title1">Левый яичник:</div>
        <div class="tr2">
            <div class="t3" style="width: 55px;">Размеры</div>
            <div class="t5"><span class="value"><?= isset($data['P29_Levyaichnikrazmer']) ? $data['P29_Levyaichnikrazmer'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 55px;">Контуры</div>
            <div class="t5"><span class="value"><?= isset($data['P30_Levyaichnikkontur']) ? $data['P30_Levyaichnikkontur'] : '&nbsp;' ?></span></div>
        </div>

    </div>
    <div class="t4">
        <div class="tr2">
            <div class="t3" style="width: 135px;">Диаметр правого рога</div>
            <div class="t5"><span class="value"><?= isset($data['P18_Matkadiametrpravroga']) ? $data['P18_Matkadiametrpravroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 175px;">Толщина стенки правого рога</div>
            <div class="t5"><span class="value"><?= isset($data['P19_Matkatolshinapravroga']) ? $data['P19_Matkatolshinapravroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 185px;">Структура стенки правого рога</div>
            <div class="t5"><span class="value"><?= isset($data['P20_Matkastrukturastenkipravroga']) ? $data['P20_Matkastrukturastenkipravroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 125px;">Содержимое полости</div>
            <div class="t5"><span class="value"><?= isset($data['P21_Matkasoderzhimpolostipravroga']) ? $data['P21_Matkasoderzhimpolostipravroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 125px;">Диаметр левого рога</div>
            <div class="t5"><span class="value"><?= isset($data['P22_Matkadiametrlevroga']) ? $data['P22_Matkadiametrlevroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 170px;">Толщина стенки левого рога</div>
            <div class="t5"><span class="value"><?= isset($data['P23_Matkatolshinalevroga']) ? $data['P23_Matkatolshinalevroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 180px;">Структура стенки левого рога</div>
            <div class="t5"><span class="value"><?= isset($data['P24_Matkastrukturastenkilevroga']) ? $data['P24_Matkastrukturastenkilevroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 125px;">Содержимое полости</div>
            <div class="t5"><span class="value"><?= isset($data['P25_Matkasoderzhimpolostilevroga']) ? $data['P25_Matkasoderzhimpolostilevroga'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 105px;">Новообразования</div>
            <div class="t5"><span class="value"><?= isset($data['P28_Pravyaichniknovoobrazov']) ? $data['P28_Pravyaichniknovoobrazov'] : '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 105px;">Новообразования</div>
            <div class="t5"><span class="value"><?= isset($data['P31_Levyaichniknovoobrazov']) ? $data['P31_Levyaichniknovoobrazov'] : '&nbsp;' ?></span></div>
        </div>
    </div>
    <br>
    <div>
        <div style="padding-bottom:10px;">Заключение</div>
        <?php if (isset($data['P32_Serviceresult'])) {
            echo $data['P32_Serviceresult'];
        }
        else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <?php } ?>
    </div>

    <br>
    <br>
    <div style="float:left;width: 300px;"><?= isset($data['P3_Visitstartdate']) ? $data['P3_Visitstartdate'] : '' ?> г.</div>
    <div style="float:right;min-width: 300px;"><div class="t3" style="width: 120px;">Ветеринарный врач</div> <div class="t5"><?= isset($data['P33_SpecialistFIO']) ? $data['P33_SpecialistFIO'] : '&nbsp;' ?></div></div>
</div>
