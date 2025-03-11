<?php

use app\common\components\pdfGenerator\PdfGenerator;
use app\common\components\pdfGenerator\PdfVisitGeneratorHelper;
use app\common\helpers\DateHelper;
use app\models\db\PetIdentification;
use app\models\db\VisitsGovServices;
use app\models\db\VisitDescriptions;
use app\models\db\Visits;
use app\models\db\Pets;
use app\modules\v2\modules\pets\models\PetsModel;
use app\modules\v2\modules\visit\models\BillModel;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\helpers\Url;

/**
 * @var View                $this
 * @var array               $data
 * @var Pets                $pets
 * @var PetIdentification   $identification
 */


$healths = $data['health'];
$drugs = $data['drugs'];
$vaccines = $data['vaccines'];

?>


<div style="font-family:'OpenSans'">
    <p class="header__right_first">
        <span>Приложение 1</span>
    </p>
    <p class="header__right">
        <span>к Описанию интерфейса просмотра карточки животного</span>
    </p>
    <p class="header__right">
        <span>&#xa0;</span>
    </p>
    <p class="header__right">
        <span>&#xa0;</span>
    </p>
    <p class="header__center">
        <span class="bold">КАРТОЧКА УЧЕТА ЖИВОТНОГО №
        </span>
        <span class="bold-italic-underline">
            <?php echo $data['card_num'] ;?>
        </span>
    </p>
    <p class="header__right">
        <span>&#xa0;</span>
    </p>
    <p class="header__right">
        <span>&#xa0;</span>
    </p>
    <p class="body_left">
        <span>г. Москва</span>
        <span>
            &#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;
        </span>
        <span>&#xa0;&#xa0; </span>
        <span><?php echo $data['date'] ;?></span>
    </p>
    <p class="body_justify">
        <span>Приют для животных <u><?php echo $data['shelter_name'] ;?></u></span>
    </p>
    <p class="body_justify">
        <span>
            Адрес приюта: <u><?php echo $data['shelter_address'] ;?></u>
        </span>
    </p>
    <p class="body_justify">
        <span>Управляющая организация:
            _________________________________________
        </span>
    </p>
    <p class="body_justify">
        <span>Номер вольера: <u><?php echo $data['aviary'] ;?></u>
        </span>
    </p>
    <table width="100%">
        <tr>
            <td class="table-left">
                <p class="table-left-p">
                    <span class="table-left-span">
                        <?php if (!$data['exist_photo']): ?>
                        <img src="<?=Url::to('@web/img/photo.png')?>" width="156" height="206" alt="" class="image">
                        <?php else: ?>
                        <img src=<?php echo $data['photoPath'] ;?> width="156" height="206" alt="" class="image">
                        <?php endif;?>
                    </span>
                </p>
            </td>
            <td class="table-right">
                <table cellspacing="0" cellpadding="0"
                    style="margin-top: 16pt; margin-bottom:0pt; border-collapse:collapse">
                    <tr class="fix-height">
                        <td
                            style="width:167.45pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; margin-right: 10pt;">
                            <table>
                                <tr>
                                    <td>
                                        <span class="row">
                                            Собака
                                        </span>
                                    </td>
                                    <?php if ($data['is_dog']): ?>
                                    <td>
                                        <span class="checkbox-image">
                                            <img src="<?=Url::to('@web/img/checkbox_checked.png')?>" height="21" width="24" alt="" />
                                        </span>
                                    </td>
                                    <?php else: ?>
                                    <td>
                                        <span class="checkbox-image">
                                            <img src="<?=Url::to('@web/img/photo.checkbox')?>" height="21" width="24" alt="" />
                                        </span>
                                    </td>
                                    <?php endif;?>
                                </tr>
                            </table>

                        </td>
                        <td class="row-top">
                            <p class="row">
                                <span>Возраст: <u><?php echo $data['age'] ;?></u></span>
                            </p>
                        </td>
                    </tr>
                    <tr class="fix-height">
                        <td class="row-small">
                            <table>
                                <tr>
                                    <td>
                                        <span class="row">
                                            Кошка
                                        </span>
                                    </td>
                                    <?php if (!$data['is_dog']): ?>
                                    <td>
                                        <span class="checkbox-image">
                                            <img src="<?=Url::to('@web/img/checkbox_checked.png')?>" height="21" width="24" alt="" />
                                        </span>
                                    </td>
                                    <?php else: ?>
                                    <td>
                                        <span class="checkbox-image">
                                            <img src="<?=Url::to('@web/img/checkbox.png')?>" height="21" width="24" alt="" />
                                        </span>
                                    </td>
                                    <?php endif;?>
                                </tr>
                            </table>
                        </td>
                        <td class="row-top">
                            <p class="row">
                                <span>Вес: <u><?php echo $data['weight'] ;?></u></span>
                            </p>
                        </td>
                    </tr>
                    <tr class="fix-height">
                        <td class="row-small">
                            <p class="row">
                                <span>Пол: <u><?php echo $data['sex'] ;?></u></span>
                            </p>
                        </td>
                        <td class="row-top">
                            <p class="row">
                                <span>Кличка: <u><?php echo $data['name'] ;?></u></span>
                            </p>
                        </td>
                    </tr>
                    <tr style="height:28.65pt">
                        <td class="row-small">
                            <p class="row">
                                <span>Окрас: <u><?php echo $data['color'] ;?></u></span>
                            </p>
                        </td>
                        <td class="row-top">
                            <p class="row">
                                <span>Порода: <u><?php echo $data['breed'] ;?></u></span>
                            </p>
                        </td>
                    </tr>
                    <tr class="fix-height">
                        <td class="row-small">
                            <p class="row">
                                <span>Уши: <u><?php echo $data['ears'] ;?></u></span>
                            </p>
                        </td>
                        <td class="row-top">
                            <p class="row">
                                <span>Шерсть: <u><?php echo $data['wool'] ;?></u></span>
                            </p>
                        </td>
                    </tr>
                    <tr style="height:26.05pt">
                        <td class="row-small">
                            <p class="row">
                                <span>Размер: <u><?php echo $data['size'] ;?></u></span>
                            </p>
                        </td>
                        <td class="row-top">
                            <p class="row">
                                <span>Хвост: <u><?php echo $data['tail'] ;?></u></span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <p
        style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; text-align:justify; line-height:108%; font-size:14pt">
        <span>Особые приметы: <u><?php echo $data['characteristics'] ;?></u></span>
    </p>
    <p class="row-text">
        <span>Характер: <u><?php echo $data['character'] ;?></u></span>
    </p>
    <p class="row-text">
        <span>Идентификационная метка <u><?php echo $data['ident'] ;?></u></span>
    </p>
    <p class="row-text">
        <span>Социализировано/готово к пристройству:
            <?php if($data['socialized']) : ?>
            <u>да</u>/нет
            <?php else: ?>
            да/<u>нет</u>
            <?php endif?>
        </span>
    </p>
    <p class="row-text">
        <span>&#xa0;</span>
    </p>
    <p class="row-label">
        <span class="bold">Сведения об обработке от экто- и эндопаразитов</span>
    </p>
    <p style="margin-right:0.3pt; text-align:center; line-height:108%; font-size:5pt">
        <span class="bold">&#xa0;</span>
    </p>

    <table cellspacing="0" cellpadding="0" class="table-grid">
        <tr>
            <td class="td-number">
                <p class="table-row">
                    <span>№</span>
                </p>
                <p style="margin-right:0.3pt; margin-bottom:0pt; text-align:center"><span>п/п</span></p>
            </td>
            <td class="row-date-label">
                <p class="table-row">
                    <span>Дата</span>
                </p>
            </td>
            <td class="row-big-label">
                <p class="table-row">
                    <span>Препарат</span>
                </p>
            </td>
            <td
                style="width:92.1pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single">
                <p class="table-row">
                    <span>Доза</span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single">
                <p class="table-row">
                    <span>Подпись ветеринарного врача и печать</span>
                </p>
            </td>
        </tr>
        <?php if(count($drugs) > 0) : ?>
        <?php for($i = 1; $i <= count($drugs); $i++) : ?>
        <tr>
            <td
                style="width:30.3pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold"><?php echo $i;?></span>
                </p>
            </td>
            <td
                style="width:77.75pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold"><?php echo $drugs[$i - 1]->date;?></span>
                </p>
            </td>
            <td
                style="width:113.65pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold"><?php echo $drugs[$i - 1]->drug_name ;?></span>
                </p>
            </td>
            <td
                style="width:92.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
        </tr>
        <?php endfor?>
        <?php else: ?>
        <tr>
            <td
                style="width:30.3pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:77.75pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:113.65pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:92.1pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
        </tr>
        <?php endif?>
    </table>
    <p class="row-label-margin">
        <span class="bold">&#xa0;</span>
    </p>
    <p class="row-label">
        <span class="bold">Сведения о вакцинации</span>
    </p>
    <p style="margin-right:0.3pt; text-align:center; line-height:108%; font-size:5pt">
        <span class="bold">&#xa0;</span>
    </p>
    <table cellspacing="0" cellpadding="0" class="table-grid">
        <tr>
            <td
                style="width:30.3pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-right:0.5pt single">
                <p class="table-row">
                    <span>№</span>
                </p>
                <p style="margin-right:0.3pt; margin-bottom:0pt; text-align:center"><span>п/п</span></p>
            </td>
            <td class="row-date-label">
                <p class="table-row">
                    <span>Дата</span>
                </p>
            </td>
            <td
                style="width:113.35pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single">
                <p class="table-row">
                    <span>Вид вакцины</span>
                </p>
            </td>
            <td
                style="width:92.4pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single">
                <p class="table-row"><span>№
                        серии</span></p>
            </td>
            <td
                style="width:113.8pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single">
                <p class="table-row"><span>Подпись
                        ветеринарного врача и печать</span></p>
            </td>
        </tr>
        <?php if(count($vaccines) > 0) : ?>
        <?php for($i = 1; $i <= count($vaccines); $i++) : ?>
        <tr>
            <td
                style="width:30.3pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span></span><?php echo $i;?></span>
                </p>
            </td>
            <td
                style="width:77.75pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span><?php echo $vaccines[$i - 1]->date;?></span>
                </p>
            </td>
            <td
                style="width:113.35pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span><?php echo $vaccines[$i - 1]->drug_name;?></span>
                </p>
            </td>
            <td
                style="width:92.4pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span><?php echo $vaccines[$i - 1]->batch;?></span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span>&#xa0;</span>
                </p>
            </td>
        </tr>
        <?php endfor?>
        <?php else: ?>
        <tr>
            <td
                style="width:30.3pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span>&#xa0;</span>
                </p>
            </td>
            <td
                style="width:77.75pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span>&#xa0;</span>
                </p>
            </td>
            <td
                style="width:113.35pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span>&#xa0;</span>
                </p>
            </td>
            <td
                style="width:92.4pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span>&#xa0;</span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-left:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span>&#xa0;</span>
                </p>
            </td>
        </tr>
        <?php endif?>
    </table>
    <p class="row-label-margin">
        <span style="-aw-import:ignore">&#xa0;</span>
    </p>

    <p style="margin-right:0.3pt; text-align:center; line-height:108%; font-size:14pt">
        <span class="bold">Сведения о состоянии здоровья</span>
    </p>
    <table cellspacing="0" cellpadding="0" class="table-grid">
        <tr>
            <td class="td-number">
                <p class="table-row">
                    <span>№</span>
                </p>
                <p style="margin-right:0.3pt; margin-bottom:0pt; text-align:center"><span>п/п</span></p>
            </td>
            <td class="row-date-label">
                <p class="table-row">
                    <span>Дата</span>
                </p>
            </td>
            <td
                style="width:113.65pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single">
                <p class="table-row">
                    <span>Вес</span>
                </p>
            </td>
            <td
                style="width:92.1pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-right:0.5pt single">
                <p class="table-row">
                    <span>Анамнез</span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:middle; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single">
                <p class="table-row">
                    <span>Подпись ветеринарного врача и печать</span>
                </p>
            </td>
        </tr>
        <?php if(count($healths) > 0) : ?>
        <?php for($i = 1; $i <= count($healths); $i++) : ?>
        <tr>
            <td
                style="width:30.3pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold"><?php echo $i ;?></span>
                </p>
            </td>
            <td
                style="width:77.75pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold"><?php echo $healths[$i - 1]->date ;?></span>
                </p>
            </td>
            <td
                style="width:113.65pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold"><?php echo $healths[$i - 1]->weight ;?></span>
                </p>
            </td>
            <td
                style="width:92.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold"><?php echo $healths[$i - 1]->anamnesis ;?></span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
        </tr>
        <?php endfor?>
        <?php else: ?>
        <tr>
            <td
                style="width:30.3pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-right:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:77.75pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:113.65pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:92.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
            <td
                style="width:113.8pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-left:0.5pt single; -aw-border-top:0.5pt single">
                <p class="table-row">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
        </tr>
        <?php endif?>
    </table>
    <p style="margin-top:0.05pt; margin-right:0.3pt; text-align:center; line-height:108%; font-size:14pt">
        <span class="bold">Сведения о движении животного</span>
    </p>
    <table cellspacing="0" cellpadding="0" class="TableGrid" style="margin-bottom:0pt; border-collapse:collapse">
        <tr style="height:22.05pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Акт приема-передачи/приема №</span>
                </p>
            </td>
            <td
                style="width:251.6pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:11pt">
                    <span style=" font-style:italic; -aw-import:ignore"><?php echo $data['arrival_act_number'] ;?>
                    </span>
                </p>
            </td>
        </tr>
        <tr style="height:40.05pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Животное поступило:</span>
                </p>
            </td>
            <td
                style="width:251.6pt; border-top-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:11pt"><span
                        style="font-style:italic; -aw-import:ignore"><?php echo $data['arrival_reason'] ;?></span>
                </p>
            </td>
        </tr>
        <?php if ($data['is_catch']): ?>
        <tr style="height:22.75pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Заказ-наряд </span>
                </p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt">
                    <span>№</span>
                    <span class="bold">
                        <u><?php echo $data['arrival_work_order'] ;?></u>
                    </span>
                    <span>
                        от <u><?php echo $data['arrival_work_order_date'] ;?></u>
                    </span>
                </p>
            </td>
        </tr>
        <tr style="height:20.95pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Акт отлова </span>
                </p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt">
                    <span>№</span>
                    <span class="bold">
                        <u><?php echo $data['catching_act_number'] ;?></u>
                    </span>
                    <span>
                        от <u><?php echo $data['catching_act_date'] ;?></u>
                    </span>
                </p>
            </td>
        </tr>
        <tr style="height:21.3pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Адрес места отлова:</span>
                </p>
            </td>
            <td
                style="width:251.6pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:11pt"><span
                        style="font-style:italic; -aw-import:ignore"><?php echo $data['catching_address'] ;?></span></p>
            </td>
        </tr>
        <tr style="height:28.65pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Видеофиксация отлова:</span>
                </p>
            </td>
            <td
                style="width:251.6pt; border-top-style:solid; border-top-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-top:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt">
                    <span>
                        <?php if($data['is_catching_video']) : ?>
                        <u>да</u>/нет
                        <?php else: ?>
                        да/<u>нет</u>
                        <?php endif?>
                    </span>
                </p>
            </td>
        </tr>
        <?php endif;?>
        <tr style="height:40.05pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Период проведения карантинных мероприятий:</span>
                </p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>с </span><span
                        style=" -aw-import:spaces">&#xa0;&#xa0;
                    </span><span><u><?php echo $data['quarantine_from'] ;?></u>
                    </span><br /><span>по <u><?php echo $data['quarantine_to'] ;?></u></span></p>
            </td>
        </tr>
        <tr style="height:4pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; text-align:justify; font-size:11pt">
                    <span style="-aw-import:ignore">&#xa0;</span>
                </p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
            </td>
        </tr>
        <tr style="height:21.45pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p class="row">
                    <span>Ранее стерилизован: </span>
                </p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>
                        <?php if ($data['castrated']): ?><u>да</u>/нет<?php else: ?>да/<u>нет</u><?php endif;?></span>
                </p>
            </td>
        </tr>
        <?php if (!$data['castrated']): ?>
        <tr style="height:24.85pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Дата
                        стерилизации: </span></p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt">
                    <span><?php echo $data['castrated_date'] ;?></span>
                </p>
            </td>
        </tr>
        <tr style="height:20.25pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Место
                        стерилизации:</span></p>
            </td>
            <td
                style="width:251.6pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; text-align:center; font-size:14pt">
                    <span class="bold"><?php echo $data['castrated_org'] ;?></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Ф.И.О.
                        ветеринарного врача:</span></p>
            </td>
            <td
                style="width:251.6pt; border-top-style:solid; border-top-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single; -aw-border-top:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; text-align:center; font-size:14pt">
                    <span class="bold"><?php echo $data['castrated_specialist'] ;?></span>
                </p>
            </td>
        </tr>
        <?php endif;?>
        <tr style="height:4pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
            </td>
            <td
                style="width:251.6pt; border-top-style:solid; border-top-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-top:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
            </td>
        </tr>
        <tr style="height:33.35pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><a
                        name="_Hlk90406911"><span>Дата поступления в приют на
                            содержание</span></a><span>:</span></p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
                <p style="margin-right:0.3pt; margin-bottom:0pt; font-size:14pt">
                    <span><u><?php echo $data['arrival_date'] ;?></u></span>
                </p>
            </td>
        </tr>
        <tr style="height:4.6pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
            </td>
        </tr>
        <tr style="height:21.05pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Дата выбытия
                        из приюта:</span></p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt">
                    <span><u><?php echo $data['departure_date'] ;?></u></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Причина
                        выбытия из приюта:</span></p>
            </td>
            <td
                style="width:251.6pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt"><span
                        style="font-style:italic; -aw-import:ignore"><?php echo $data['departure_comment'] ;?></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
            </td>
            <td
                style="width:251.6pt; border-top-style:solid; border-top-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-top:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt"><span
                        style=" font-style:italic; -aw-import:ignore">&#xa0;</span></p>
            </td>
        </tr>
        <?php if ($data['is_new_owner']): ?>
        <tr>
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><a
                        name="_Hlk90406984"><span>Договор о передаче животного
                        </span><br /><span>в собственность (под
                            опеку)</span></a><span> </span></p>
            </td>
            <td style="width:251.6pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; text-align:justify"><span
                        style=" font-style:italic; -aw-import:ignore">&#xa0;</span></p>
                <p style="margin-right:0.3pt; margin-bottom:0pt; text-align:justify; font-size:14pt">
                    <span>№</span>
                    <span class="bold"> _____ </span>
                    <span>от <?php echo $data['departure_date'] ;?></span>
                </p>
            </td>
        </tr>
        <?php endif;?>
        <?php if ($data['is_death']): ?>
        <tr style="height:20.95pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Причина
                        смерти:</span></p>
            </td>
            <td
                style="width:251.6pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt"><span
                        style="font-style:italic; -aw-import:ignore">&#xa0;</span></p>
            </td>
        </tr>
        <tr style="height:27.85pt">
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:bottom">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Акт смерти
                    </span></p>
            </td>
            <td
                style="width:251.6pt; border-top-style:solid; border-top-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:bottom; -aw-border-top:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt">
                    <span>№</span>
                    <span class="bold"> _____ </span>
                    <span>от <?php echo $data['departure_date'] ;?></span>
                </p>
            </td>
        </tr>
        <?php endif;?>
        <?php if ($data['is_ephtanazia']): ?>
        <tr>
            <td style="width:194.55pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span
                        style=" -aw-import:ignore">&#xa0;</span></p>
                <p style="margin-right:0.3pt; margin-bottom:0pt; font-size:14pt"><span>Причина эвтаназии:</span></p>
            </td>
            <td
                style="width:251.6pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.4pt; padding-left:5.4pt; vertical-align:top; -aw-border-bottom:0.5pt single">
                <p style="margin-top:0.05pt; margin-right:0.3pt; margin-bottom:0pt; text-align:center; font-size:14pt">
                    <span class="bold">&#xa0;</span>
                </p>
            </td>
        </tr>
        <?php endif;?>
    </table>
    <p class="row-label-margin">
        <span class="bold">&#xa0;</span>
    </p>
    <?php if ($data['is_new_owner']): ?>
    <p class="row-label">
        <span class="bold">Сведения о новых владельцах</span>
    </p>
    <?php if (!$data['is_legal']): ?>
    <p class="row-text">
        <span>Физическое лицо (Ф.И.О.):
            <u><?php echo $data['fio'] ;?></u></span>
    </p>
    <p class="row-text"><span>адрес:
            <u><?php echo $data['address'] ;?></u></span></p>
    <p class="row-text">
        <span>контактные данные:
            <u><?php echo $data['phone'] ;?></u></span></span>
    </p>
    <p class="row-text"><span style="-aw-import:ignore">&#xa0;</span></p>
    <?php endif;?>
    <?php if ($data['is_legal']): ?>
    <p class="row-text">
        <span>Юридическое лицо: <u><?php echo $data['fio'] ;?></u>
        </span>
    </p>
    <p class="row-text"><span>Ф.И.О.
            опекунов:
            <u><?php echo $data['tutor'] ;?></u></span></p>
    <p class="row-text"><span>адрес:
            <u><?php echo $data['address'] ;?></u></span></span></p>
    <p class="row-text">
        <span>контактные данные:
            <u><?php echo $data['phone'] ;?></u></span>
    </p>
    <?php endif;?>
    <?php endif;?>
    <p class="row-label">
        <span class="bold">&#xa0;</span>
    </p>
    <p class="row-label">
        <span class="bold">Ответственные за животное</span>
    </p>
    <p class="row-text"><a name="_Hlk90407276"><span>Ф.И.О. руководителя приюта</span></a><span>/подпись:
            <u><?php echo $data['chief_name'] ;?></u>/___________,</span></p>
    <p class="row-text"><span>Ф.И.О.
            сотрудника по уходу за животным/подпись:
            __________/___________.</span></p>
    <p style="text-align:justify; line-height:108%; font-size:12pt"><span style=" -aw-import:ignore">&#xa0;</span></p>
    <p style="text-align:justify; line-height:108%; font-size:12pt"><span style=" -aw-import:ignore">&#xa0;</span></p>
    <p style="text-align:justify; line-height:108%; font-size:12pt">
        <span class="bold">&#xa0;</span>
    </p>
    <p><span style="-aw-import:ignore">&#xa0;</span></p>
</div>