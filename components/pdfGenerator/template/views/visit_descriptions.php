<?php

use app\common\components\pdfGenerator\PdfGenerator;
use app\common\components\pdfGenerator\PdfVisitGeneratorHelper;
use app\common\helpers\DateHelper;
use app\models\db\Pets;
use app\models\db\VisitDescriptions;
use app\models\db\Visits;
use app\modules\v2\modules\visit\models\BillModel;
use yii\helpers\ArrayHelper;
use yii\web\View;
use app\modules\v2\modules\visit\models\BillModelForAP;

extract($data);

/**
 * @var View $this
 * @var array $data
 * @var Visits $visit
 * @var array $description_types
 * @var \app\models\db\DescriptionTypes $descriptions_types
 * @var Visits[] $visits
 * @var VisitDescriptions[] $descriptions
 */
/** @var Pets[] $pets */
/** @var Pets $petes */
/** @var \app\models\db\VisitsGovServices[] $govs */
$pets = $data['pets'] ?? $visit->pets;
$govs = $visit->pets;
$visits = $data['visit'] ?? $petes->visit;
$visitStart = $visit->fact_start_dttm ?? $visit->start_dttm;
$visitDate = PdfGenerator::dateFromFormat($visitStart, 'Y-m-d H:i:s');
$visitBill = new BillModel($visit->id, null, null, null);
$vsd = PdfVisitGeneratorHelper::getVsd($visit);

?>

<div class="wrapper">
    <div class="content">
        <div class="title_vd">Амбулаторный приём №<?php echo $visit->id ?> от «<?php echo $visitDate['d']; ?>» <?php echo $visitDate['M']; ?> <?php echo $visitDate['Y']; ?> г</div>
        <div class="table-container">
            <table class="table">
                <caption class="table__caption">Сведения об обратившемся</caption>
                <tr>
                    <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key">ФИО</td>
                                <td class="table-value"><?php echo $visit->owner->fullname; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Льготная категория</td>
                                <td class="table-value"><?php echo PdfVisitGeneratorHelper::getOwnerPrivileges($visit); ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Номер документа, подтверждающего льготу</td>
                                <td class="table-value"><?php echo $visit->preferences_document ?: '-'; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key">Телефон</td>
                                <td class="table-value"><?php echo PdfVisitGeneratorHelper::getOwnerPhone($visit); ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">E-mail</td>
                                <td class="table-value"><?php echo PdfVisitGeneratorHelper::getOwnerEmail($visit); ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Адрес проживания обратившегося</td>
                                <td class="table-value"><?php echo $visit->owner->fact_fias_addresses->full_address ?? '-'; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Адрес регистрации обратившегося</td>
                                <td class="table-value"><?php echo $visit->owner->fias_addresses->full_address ?? '-'; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <?php foreach ($pets as $pet): ?>
            <table class="table">
                <caption class="table__caption">Сведение о животном</caption>
                <?php $identification = PdfVisitGeneratorHelper::getIdentification($pet); ?>
                <tr class="table__row">
                    <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key" colspan="2">Кличка</td>
                                <td class="table-value"><?php echo $pet->name; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key" colspan="2">Вид животного</td>
                                <td class="table-value"><?php echo $pet->species->name ?? '-'; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key" colspan="2">Порода</td>
                                <td class="table-value"><?php echo $pet->breeds->name ?? '-'; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key" colspan="2">Пол</td>
                                <td class="table-value"><?= $pet->getSexName() ?: '-' ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key" colspan="2">Возраст</td>
                                <td class="table-value"><?php echo empty($pet->birthday) ? '-' : DateHelper::ageAtDate($pet->birthday, $visitStart); ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key" colspan="2">Адрес содержания животного</td>
                                <td class="table-value"><?php echo $pet->fias_address->full_address ?? '-'; ?></td>
                            </tr>
                            <?php if (!empty($vsd)): ?>
                                <tr class="table__row">
                                    <td class="table-key" colspan="2">ВСД</td>
                                </tr>
                                <?php foreach ($vsd as $vsdRow): ?>
                                    <tr class="table__row">
                                        <td class="table-vsd-p">-</td>
                                        <td class="table-vsd-key"><?php echo $vsdRow['type']; ?></td>
                                        <td class="table-value"><?php echo $vsdRow['value']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </table>
                    </td>
                    <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key">Способ идентификации</td>
                                <td class="table-value"><?php echo $identification === null ? '-' : $identification->ident_type->name; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Идентификационный номер</td>
                                <td class="table-value"><?php echo $identification === null ? '-' : $identification->identification_code; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Номер регистрационного удостоверения</td>
                                <td class="table-value"><?php echo $pet->reg_certificate->number ?? '-'; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Дата вакцинации против бешенства</td>
                                <td class="table-value"><?php echo PdfVisitGeneratorHelper::getRabiesVaccination($pet) ?: '-'; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Дата вакцинации от лептоспироза</td>
                                <td class="table-value"><?php echo PdfVisitGeneratorHelper::getLeptVaccination($pet) ?: '-'; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table class="table">
                <caption class="table__caption">Данные приема</caption>
                    <?php foreach ($pet->descriptions as $descriptiones): ?>
                    <?php  $description_pets = $descriptiones->id_pet ?? null; ?>
                    <?php  $description_visits = $descriptiones->id_visit ?? null; ?>
                    <?php if ($pet->id == $description_pets and $visit->id == $description_visits):?>
                    <tr class="table__row">
                        <td class="table-key"><?php echo $descriptiones->description_types->name; ?></td>
                        <td class="table-value-description"><?php echo ($descriptiones === null) ? '-' : nl2br($descriptiones->description); ?></td>
                    </tr>
                <?php endif;?>
                <?php endforeach; ?>
                <?php $visitBill = new BillModelForAP($visit->id,null, null, null, $pet->id); ?>
                <tr class="table__row">
                    <td class="table-key">Стоимость оказанных услуг</td>
                    <td class="table-value-description"><?php echo PdfVisitGeneratorHelper::getServicesForAP($visitBill); ?> </td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Общая стоимость приёма</td>
                    <td class="table-value-description"><?php echo PdfVisitGeneratorHelper::getPriceSumForAP($visitBill); ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Ветеринарное лечебное учреждение (подразделение)</td>
                    <td class="table-value-description"><?php echo $visit->organization->short_name; ?></td>
                </tr>
            <br>
                <?php if (!empty($govs->visitServiceParamValues->num_value)): ?>
                <tr class="table__row">
                    <td class="table-key">Данные отчётов по оказанным услугам:</td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Услуга</td>
                    <td class="table-key">Название показателя</td>
                    <td class="table-key">Данные показателя</td>
                </tr>
                <?php foreach ($govs as $gov): ?>
                <tr class="table__row">
                    <td class="table-value"><?php echo $gov->visit->visitsGovService->service->name ?? 'Нет услуг'; ?></td>
                    <td class="table-value"><?php echo $gov->visit->visitsGovService->service->briefname ?? 'Нет данных'; ?></td>
                    <td class="table-value"><?php echo $gov->visitServiceParamValues->num_value ?? 'Нет данных'; ?> <?php echo $gov->visitServiceParamValues->char_value ?? ' '; ?></td>
                </tr>
                <?php endforeach;?>
                <?php endif;?>
            </table>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="footer">
    <div class="footer__title">Владелец ознакомлен и согласен с данными приема</div>
    <table class="footer__table">
        <tr class="footer__table-row">
            <td class="footer__table--left footer__sign-title">Подпись владельца</td>
            <td class="footer__table--right footer__sign-title">Подпись вет. специалиста</td>
        </tr>
        <tr class="footer__table-row">
            <td class="footer__sign">________________________________</td>
            <td class="footer__sign--right">_______________________________</td>
        </tr>
        <tr class="footer__table-row">
            <td class="footer__table--left footer__sign-name"><?php echo $visit->owner->fullname; ?></td>
            <td class="footer__table--right footer__sign-name"><?php echo $visit->specialists->fullname; ?></td>
        </tr>
    </table>
</div>
