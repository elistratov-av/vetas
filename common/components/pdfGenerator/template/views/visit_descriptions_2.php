<?php

use app\common\components\pdfGenerator\PdfGenerator;
use app\common\components\pdfGenerator\PdfVisitGeneratorHelper;
use app\common\helpers\DateHelper;
use app\models\db\Pets;
use app\models\db\VisitDescriptions;
use app\models\db\Visits;
use app\modules\v2\modules\visit\models\BillModel;
use yii\web\View;

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
$visit = $data['visit'] ?? $petes->visit;
$visitStart = $visit->fact_start_dttm ?? $visit->start_dttm;
$visitDate = PdfGenerator::dateFromFormat($visitStart ?? date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
$visitBill = new BillModel($visit->id, null, null, null);
$vsd = PdfVisitGeneratorHelper::getVsd($visit);

$description_types_result = [];
$query = 'SELECT * FROM public.description_types WHERE entity_type=\'visit\'';
$description_types_ = \Yii::$app->db->createCommand($query)->queryAll();
$query = "SELECT * FROM public.visit_descriptions
          LEFT JOIN gost_diseases ON visit_descriptions.description = gost_diseases.gost_code
          WHERE id_visit = " . $visit->id;

$descriptions_ = \Yii::$app->db->createCommand($query)->queryAll();

foreach ($description_types_ as $description_type_) {
    $description_types_result[] = ['id' => $description_type_['id'], 'name' => $description_type_['name'], 'sort_by' => $description_type_['sort_by'], 'tech_name' => $description_type_['tech_name'], 'required' => ''];
}

$dtypes = [];
$dtypes = $description_types;

// Функция для поиска tech_name по id_description_type
function findTechName($id_description_type, $array)
{
    foreach ($array as $item) {
        if ($item['id'] == $id_description_type) {
            return $item['tech_name'];
        }
    }
    return null; // Если не найдено совпадение
}

function printTextAppoinment($text)
{
    $parts = explode("\n", $text);
    $finalParts = [];
    foreach ($parts as $part) {
        while (mb_strlen($part) > 80) {
            $breakpoint = mb_strrpos(mb_substr($part, 0, 80), ' ');
            if ($breakpoint === false) {
                $breakpoint = 80;
            }
            $finalParts[] = trim(mb_substr($part, 0, $breakpoint));
            $part = mb_substr($part, $breakpoint + 1);
        }
        $finalParts[] = trim($part);
    }
    foreach ($finalParts as $line) : ?>
        <tr>
            <td class="full-width underline-text">
                <?php echo $line;?>
            </td>
        </tr>
<?php endforeach;
}



// Обновление массива $descriptions_ с использованием $description_types_result
foreach ($descriptions_ as &$item) {
    $id_description_type = $item['id_description_type'];
    $item['tech_name'] = findTechName($id_description_type, $description_types_result);
}

$newDescriptionsArray = [];
foreach ($descriptions_ as $des) {
    $idPet = $des['id_pet'];
    $idDescriptionType = $des['id_description_type'];
    if (!isset($newDescriptionsArray[$idPet])) {
        $newDescriptionsArray[$idPet] = [];
    }
    if (!isset($newDescriptionsArray[$idPet][$idDescriptionType])) {
        $newDescriptionsArray[$idPet][$idDescriptionType] = [];
    }
    $newDescriptionsArray[$idPet][$idDescriptionType][] = $des;
}

foreach ($description_types_result as $dtype) {
    $dtypes[$dtype['tech_name']] = $dtype;
}

$descriptions = $newDescriptionsArray;
?>

<?php foreach ($pets as $num => $pet) : ?>
    <?php if (isset($data['pet_services'][$pet['id']])) : ?>
        <?php $total_price = 0; ?>
        <?php $total_price_tmc = 0; ?>
        <?php $total_price_with_discount = 0; ?>
        <?php $identification = PdfVisitGeneratorHelper::getIdentification($pet); ?>

        <table width="100%">
            <tr>
                <td style="width: 20%;"><img src="<?php echo Yii::$app->basePath . '/web/img/vetclinic.png' ?>" style="width: 100px;"></td>
                <td style="width: 35%;">
                    Государственная ветеринарная служба города Москвы
                    <table class="table">
                        <tr>
                            <td rowspan="2">
                                <img src="<?php echo Yii::$app->basePath . '/web/img/icon-mos.png' ?>" style="width: 30px; margin-right: 5px;">
                            </td>
                            <td>
                                <?php
                                if ($organizations[0]['short_name'] == 'ГБУ «Мосветстанция»') {
                                    echo '<a href="http://www.mosk-vet.ru">www.mosk-vet.ru</a>';
                                } else {
                                    echo '<a href="http://www.mos-obvet.ru">www.mos-obvet.ru</a>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                if ($organizations[0]['short_name'] == 'ГБУ «Мосветстанция»') {
                                    echo '+7(495)612-74-21';
                                } else {
                                    echo '+7(495)612-04-25';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 5%;"></td>
                <td style="width: 40%; vertical-align: top;">
                    <?php echo $organizations[0]['short_name']; ?><br />
                    <?php if (!empty($fias_address)) : ?>
                        <?php echo $fias_address->full_address; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <div class="wrapper">
            <div class="content">
                <div class="title_vd" style="text-align: center; font-size: 22px; font-weight: bold; margin-top: 30px; margin-bottom: 30px;">
                    Амбулаторный приём №<?php echo $visit->id ?> от «<?php echo $visitDate['d']; ?>
                    » <?php echo $visitDate['M']; ?> <?php echo $visitDate['Y']; ?> г
                </div>
                <div class="table-container">
                    <table class="table">
                        <tr>
                            <td class="table-td">
                                <strong>ФИО:</strong> <?php echo $visit->owner->fullname; ?>
                            </td>
                            <td class="table-td">
                                <strong>Животное:</strong>
                                <?php echo $pet->species->name ?? '-'; ?>
                                <?php if (!empty($pet->name)) : ?>, <?php echo $pet->name; ?><?php endif; ?>
                                <?php if (!empty($pet->getSexName())) : ?>, <?php echo $pet->getSexName(); ?><?php endif; ?>
                                <?php if (!empty($pet->birthday)) : ?>, <?php echo DateHelper::ageAtDate($pet->birthday, $visitStart); ?><?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    <?php $symbols = 90;
                    $worderStart = 0;
                    $worderFinish = 0;
                    $lastIsShow = 0; ?>
                    <?php if (isset($dtypes['VISIT_ANAMNEZ_1'])) : ?>
                        <?php if (count($descriptions) > 0 && isset($descriptions[$pet->id]) && !empty($descriptions[$pet->id][$dtypes['VISIT_ANAMNEZ_1']['id']])) {
                            $anamnez1 = isset($descriptions[$pet->id][$dtypes['VISIT_ANAMNEZ_1']['id']][0]['description']) ? $descriptions[$pet->id][$dtypes['VISIT_ANAMNEZ_1']['id']][0]['description'] : '';
                        } else {
                            $anamnez1 = '';
                        } ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Анамнез:</td>
                                    <?php if (!empty($anamnez1)) printTextAppoinment($anamnez1);?>
                                        
                            </tr>
                            
                           
                        </table>
                    <?php endif; ?>

                    <?php $symbols = 90;
                    $worderStart = 0;
                    $worderFinish = 0;
                    $lastIsShow = 0; ?>
                    <?php if (isset($dtypes['VISIT_CLINICAL_DATA'])) : ?>
                        <?php if (count($descriptions) > 0 && isset($descriptions[$pet->id]) && !empty($descriptions[$pet->id][$dtypes['VISIT_CLINICAL_DATA']['id']])) {
                            $clinic = isset($descriptions[$pet->id][$dtypes['VISIT_CLINICAL_DATA']['id']][0]['description']) ? $descriptions[$pet->id][$dtypes['VISIT_CLINICAL_DATA']['id']][0]['description'] : '';
                        } else {
                            $clinic = '';
                        } ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Клинический осмотр:</td>
                                       <?php if (!empty($clinic)) printTextAppoinment($clinic);?>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <?php $symbols = 90;
                    $worderStart = 0;
                    $worderFinish = 0;
                    $lastIsShow = 0; ?>
                    <?php if (isset($dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_3']['id']) && count($descriptions) > 0 && !empty($descriptions[$pet->id][$dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_3']['id']])) : ?>

                        <?php if (count($descriptions) > 0 && !empty($descriptions[$pet->id][$dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_3']['id']])) {
                            $predDiagnoz = isset($descriptions[$pet->id][$dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_3']['id']][0]['description']) ? $descriptions[$pet->id][$dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_3']['id']][0]['description'] : '';
                        } else {
                            $predDiagnoz = '';
                        } ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Предварительный диагноз:</td>
                                <td class="underline-text" style="width: 355px;">
                                    <?php if (!empty($predDiagnoz)) : ?>
                                        <?php $inFirstRow = 50;
                                        $symStart = 0;
                                        $symFinish = mb_strripos(mb_substr($predDiagnoz, $symStart, $inFirstRow), ' '); ?>
                                        <?php echo mb_substr($predDiagnoz, $symStart, $symFinish - $symStart); ?>
                                        <?php if (mb_strripos(mb_substr($predDiagnoz, $symFinish, $symbols), ' ') == 0 && $lastIsShow == 0) : ?>
                                            <?php echo mb_substr($predDiagnoz, $symFinish, $symbols);
                                            $lastIsShow = 1; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="full-width underline-text">
                                    <?php if (!empty($predDiagnoz)) : ?>
                                        <?php $symStart = $symFinish;
                                        $symFinish += mb_strripos(mb_substr($predDiagnoz, $symFinish, $symbols), ' '); ?>
                                        <?php echo mb_substr($predDiagnoz, $symStart, $symFinish - $symStart); ?>
                                        <?php if (mb_strripos(mb_substr($predDiagnoz, $symFinish, $symbols), ' ') == 0 && $lastIsShow == 0) : ?>
                                            <?php echo mb_substr($predDiagnoz, $symFinish, $symbols);
                                            $lastIsShow = 1; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    &nbsp;
                                </td>
                            </tr>
                            <?php if (!empty($predDiagnoz) && mb_strlen($predDiagnoz) > $symFinish) : ?>
                                <?php $symStart = $symFinish;
                                $symFinish += mb_strripos(mb_substr($predDiagnoz, $symFinish, $symbols), ' '); ?>
                                <?php for ($i = $symStart; ($i < mb_strlen($predDiagnoz) && $symStart != $symFinish); $i += $symFinish - $symStart) { ?>
                                    <tr>
                                        <td colspan="2" class="full-width underline-text">
                                            <?php echo mb_substr($predDiagnoz, $symStart, $symFinish - $symStart); ?>
                                            <?php $symStart = $symFinish;
                                            $symFinish += mb_strripos(mb_substr($predDiagnoz, $symFinish, $symbols), ' '); ?>
                                            <?php if ($symStart == $symFinish) : ?>
                                                <?php echo mb_substr($predDiagnoz, $symFinish, $symbols); ?>
                                            <?php endif; ?>
                                            &nbsp;
                                        </td>
                                    </tr>
                                <?php }; ?>

                            <?php endif; ?>
                        </table>
                    <?php endif; ?>

                    <?php if (isset($dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_DISEASE']['id']) && count($descriptions) > 0 && !empty($descriptions[$pet->id][$dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_DISEASE']['id']])) : ?>

                        <?php if (count($descriptions) > 0 && !empty($descriptions[$pet->id][$dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_DISEASE']['id']])) {
                            $predDiagnozString = '';
                            foreach ($descriptions[$pet->id][$dtypes['VISIT_PREDVARITELNYJ_DIAGNOZ_DISEASE']['id']] as $description) {
                                $gostCode = isset($description['gost_code']) ? $description['gost_code']  : '';
                                $descName = isset($description['name']) ? $description['name'] : '';
                                $predDiagnozString .= '(' . $gostCode . ') ' . $descName . ', ';
                            }
                            $predDiagnozString = rtrim($predDiagnozString, ', ');
                        } else {
                            $predDiagnozString = '';
                        } ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Предварительный диагноз:</td>
                                <td class="underline-text" style="width: 355px;">
                                    <?php if (!empty($predDiagnozString)) : ?>
                                        <?php $inFirstRow = 50;
                                        $symStart = 0;
                                        $symFinish = mb_strripos(mb_substr($predDiagnozString, $symStart, $inFirstRow), ' '); ?>
                                        <?php echo mb_substr($predDiagnozString, $symStart, $symFinish - $symStart); ?>
                                        <?php if (mb_strripos(mb_substr($predDiagnozString, $symFinish, $symbols), ' ') == 0 && $lastIsShow == 0) : ?>
                                            <?php echo mb_substr($predDiagnozString, $symFinish, $symbols);
                                            $lastIsShow = 1; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="full-width underline-text">
                                    <?php if (!empty($predDiagnozString)) : ?>
                                        <?php $symStart = $symFinish;
                                        $symFinish += mb_strripos(mb_substr($predDiagnozString, $symFinish, $symbols), ' '); ?>
                                        <?php echo mb_substr($predDiagnozString, $symStart, $symFinish - $symStart); ?>
                                        <?php if (mb_strripos(mb_substr($predDiagnozString, $symFinish, $symbols), ' ') == 0 && $lastIsShow == 0) : ?>
                                            <?php echo mb_substr($predDiagnozString, $symFinish, $symbols);
                                            $lastIsShow = 1; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    &nbsp;
                                </td>
                            </tr>
                            <?php if (!empty($predDiagnozString) && mb_strlen($predDiagnozString) > $symFinish) : ?>
                                <?php $symStart = $symFinish;
                                $symFinish += mb_strripos(mb_substr($predDiagnozString, $symFinish, $symbols), ' '); ?>
                                <?php for ($i = $symStart; ($i < mb_strlen($predDiagnozString) && $symStart != $symFinish); $i += $symFinish - $symStart) { ?>
                                    <tr>
                                        <td colspan="2" class="full-width underline-text">
                                            <?php echo mb_substr($predDiagnozString, $symStart, $symFinish - $symStart); ?>
                                            <?php $symStart = $symFinish;
                                            $symFinish += mb_strripos(mb_substr($predDiagnozString, $symFinish, $symbols), ' '); ?>
                                            <?php if ($symStart == $symFinish) : ?>
                                                <?php echo mb_substr($predDiagnozString, $symFinish, $symbols); ?>
                                            <?php endif; ?>
                                            &nbsp;
                                        </td>
                                    </tr>
                                <?php }; ?>

                            <?php endif; ?>
                        </table>
                    <?php endif; ?>


                    <?php $symbols = 90;
                    $worderStart = 0;
                    $worderFinish = 0;
                    $lastIsShow = 0; ?>
                    <?php if (isset($dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4']) && count($descriptions) > 0 && isset($descriptions[$pet->id]) && !empty($descriptions[$pet->id][$dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4']['id']])) : ?>

                        <?php if (count($descriptions) > 0 && isset($descriptions[$pet->id]) && !empty($descriptions[$pet->id][$dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4']['id']])) {
                            $zakDiagnoz = isset($descriptions[$pet->id][$dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4']['id']][0]['description']) ? $descriptions[$pet->id][$dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4']['id']][0]['description'] : '';
                        } else {
                            $zakDiagnoz = '';
                        } ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Заключительный диагноз:</td>

                                    <?php if (!empty($zakDiagnoz)) printTextAppoinment($zakDiagnoz);?>

                            </tr>
                        </table>
                    <?php endif; ?>

                    <?php if (isset($dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_DISEASE']['id']) && count($descriptions) > 0 && !empty($descriptions[$pet->id][$dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_DISEASE']['id']])) : ?>

                        <?php if (count($descriptions) > 0 && !empty($descriptions[$pet->id][$dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_DISEASE']['id']])) {
                            $zakDiagnozString = '';
                            foreach ($descriptions[$pet->id][$dtypes['VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_DISEASE']['id']] as $description) {
                                $gostCodeZak = isset($description['gost_code']) ? $description['gost_code']  : '';
                                $descNameZak = isset($description['name']) ? $description['name'] : '';
                                $zakDiagnozString .= '(' . $gostCodeZak . ') ' . $descNameZak . ', ';
                            }
                            $zakDiagnozString = rtrim($zakDiagnozString, ', ');
                        } else {
                            $zakDiagnozString = '';
                        } ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Заключительный диагноз:</td>
                                <td class="underline-text" style="width: 355px;">
                                    <?php if (!empty($zakDiagnozString)) : ?>
                                        <?php $inFirstRow = 50;
                                        $symStart = 0;
                                        $symFinish = mb_strripos(mb_substr($zakDiagnozString, $symStart, $inFirstRow), ' '); ?>
                                        <?php echo mb_substr($zakDiagnozString, $symStart, $symFinish - $symStart); ?>
                                        <?php if (mb_strripos(mb_substr($zakDiagnozString, $symFinish, $symbols), ' ') == 0 && $lastIsShow == 0) : ?>
                                            <?php echo mb_substr($zakDiagnozString, $symFinish, $symbols);
                                            $lastIsShow = 1; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="full-width underline-text">
                                    <?php if (!empty($zakDiagnozString)) : ?>
                                        <?php $symStart = $symFinish;
                                        $symFinish += mb_strripos(mb_substr($zakDiagnozString, $symFinish, $symbols), ' '); ?>
                                        <?php echo mb_substr($zakDiagnozString, $symStart, $symFinish - $symStart); ?>
                                        <?php if (mb_strripos(mb_substr($zakDiagnozString, $symFinish, $symbols), ' ') == 0 && $lastIsShow == 0) : ?>
                                            <?php echo mb_substr($zakDiagnozString, $symFinish, $symbols);
                                            $lastIsShow = 1; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    &nbsp;
                                </td>
                            </tr>
                            <?php if (!empty($zakDiagnozString) && mb_strlen($zakDiagnozString) > $symFinish) : ?>
                                <?php $symStart = $symFinish;
                                $symFinish += mb_strripos(mb_substr($zakDiagnozString, $symFinish, $symbols), ' '); ?>
                                <?php for ($i = $symStart; ($i < mb_strlen($zakDiagnozString) && $symStart != $symFinish); $i += $symFinish - $symStart) { ?>
                                    <tr>
                                        <td colspan="2" class="full-width underline-text">
                                            <?php echo mb_substr($zakDiagnozString, $symStart, $symFinish - $symStart); ?>
                                            <?php $symStart = $symFinish;
                                            $symFinish += mb_strripos(mb_substr($zakDiagnozString, $symFinish, $symbols), ' '); ?>
                                            <?php if ($symStart == $symFinish) : ?>
                                                <?php echo mb_substr($zakDiagnozString, $symFinish, $symbols); ?>
                                            <?php endif; ?>
                                            &nbsp;
                                        </td>
                                    </tr>
                                <?php }; ?>

                            <?php endif; ?>
                        </table>
                    <?php endif; ?>


                    <?php $symbols = 90;
                    $worderStart = 0;
                    $worderFinish = 0;
                    $lastIsShow = 0; ?>
                    <?php if (isset($dtypes['VISIT_SKHEMA_LECHENIYA_5'])) : ?>
                        <?php if (count($descriptions) > 0 && isset($descriptions[$pet->id]) && !empty($descriptions[$pet->id][$dtypes['VISIT_SKHEMA_LECHENIYA_5']['id']])) {
                            $shema = isset($descriptions[$pet->id][$dtypes['VISIT_SKHEMA_LECHENIYA_5']['id']][0]['description']) ? $descriptions[$pet->id][$dtypes['VISIT_SKHEMA_LECHENIYA_5']['id']][0]['description'] : '';
                        } else {
                            $shema = '';
                        } ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Схема лечения:</td>
                            </tr>
                            <tr>
                                    <?php if (!empty($shema))  printTextAppoinment($shema);?>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <?php $symbols = 90;
                    $worderStart = 0;
                    $worderFinish = 0;
                    $lastIsShow = 0; ?>

                    <?php if (isset($dtypes['VISIT_REKOMENDATSII'])):
                        if (count($descriptions) > 0 && isset($descriptions[$pet->id]) && !empty($descriptions[$pet->id][$dtypes['VISIT_REKOMENDATSII']['id']])) $recomend = $descriptions[$pet->id][$dtypes['VISIT_REKOMENDATSII']['id']][0]['description'] ?? '';
                        else $recomend = '';
                    ?>
                        <table class="fz-12">
                            <tr>
                                <td class="font-bold">Рекомендации:</td>
                            </tr>
                            <?php echo printTextAppoinment($recomend)?>
                        </table>
                    <?php endif; ?>
                    <table class="fz-12">
                        <tr>
                            <td class="font-bold">Повторный приём:</td>
                            <td>
                                <?php if (!empty($source_visit)) : ?>
                                    <?php echo date('d.m.Y', strtotime($source_visit['start_dttm'])); ?>
                                <?php else : ?>
                                    ---
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    <table class="table" style="width: 100%;">
                        <tr class="table__row">
                            <td class="table-td">
                                <strong>Стоимость оказания услуг:</strong>
                            </td>
                        </tr>
                    </table>
                    <table class="table" style="border: 1px solid black">
                        <tr class="table__row">
                            <td class="table-td" style="padding: 5px; text-align: center; border-right: 1px solid black">
                                <strong>Код услуги</strong>
                            </td>
                            <td class="table-td" style="padding: 5px; text-align: center; border-right: 1px solid black;">
                                <strong>Наименование услуги</strong>
                            </td>
                            <td class="table-td" style="padding: 5px; text-align: center;"><strong>Стоимость,
                                    руб</strong>
                            </td>
                        </tr>
                        <?php foreach ($visitBill->getBill()['services'] as $pet_service) : ?>
                            <?php if (isset($pet_service['id_pet']) && $pet_service['id_pet'] == $pet['id']) : ?>
                                <?php
                                $price = $pet_service['price_with_discount'] ?: $pet_service["service"]["price"];
                                $total_price += $price * $pet_service["count"]; ?>
                                <tr class="table__row">
                                    <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black"><?php echo $pet_service["service"]["cod"] ?? 'Нет данных' ?></td>
                                    <td class="table-value" style="padding: 5px; border-right: 1px solid black; border-top: 1px solid black"><?php echo $pet_service["service"]["name"] ?? 'Нет данных' ?></td>
                                    <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black"><?php echo number_format($price * $pet_service["count"], 2) ?? 'Нет данных' ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <tr class="table__row">
                            <td class="table-value" style="border-top: 1px solid black"></td>
                            <td class="table-value" style="text-align: right; padding: 5px; border-right: 1px solid black; border-top: 1px solid black; font-weight: bold;">
                                Итого:
                            </td>
                            <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black; font-weight: bold;"><?php echo number_format($total_price, 2) ?? '----'; ?></td>
                        </tr>
                    </table>


                    <table class="table" style="width: 100%;">
                        <tr class="table__row">
                            <td class="table-td">
                                <strong>Стоимость используемых ТМЦ:</strong>
                            </td>
                        </tr>
                    </table>
                    <table class="table" style="border: 1px solid black">
                        <tr class="table__row">
                            <td class="table-td" style="padding: 5px; text-align: center; border-right: 1px solid black;">
                                <strong>Наименование ТМЦ</strong>
                            </td>
                            <td class="table-td" style="padding: 5px; text-align: center; border-right: 1px solid black">
                                <strong>Количество</strong>
                            </td>
                            <td class="table-td" style="padding: 5px; text-align: center; border-right: 1px solid black">
                                <strong>Единица измерения</strong>
                            </td>
                            <td class="table-td" style="padding: 5px; text-align: center;">
                                <strong>Стоимость, руб</strong>
                            </td>
                        </tr>
                        <?php if (count($visitBill->getBill()['balance_tmc']) > 0) : ?>
                            <?php foreach ($visitBill->getBill()['balance_tmc'] as $pet_tmc) : ?>
                                <?php if (isset($pet_tmc['pet']) && ($pet_tmc['pet'] == $pet['id'])) : ?>
                                    <?php
                                    $price_tmc = $pet_tmc['price'];
                                    $total_price_tmc += $price_tmc; ?>
                                    <tr class="table__row">
                                        <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black"><?php echo $pet_tmc["name"] ?? 'Нет данных' ?></td>
                                        <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black"><?php echo $pet_tmc["count_selected"] ?? 'Нет данных' ?></td>
                                        <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black"><?php echo $pet_tmc["unit"] ?? 'Нет данных' ?></td>
                                        <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black"><?php echo number_format($price_tmc, 2) ?? 'Нет данных' ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <tr class="table__row">
                                <td class="table-value" style="border-top: 1px solid black"></td>
                                <td class="table-value" style="border-top: 1px solid black"></td>
                                <td class="table-value" style="text-align: right; padding: 5px; border-right: 1px solid black; border-top: 1px solid black; font-weight: bold;">
                                    Итого:
                                </td>
                                <td class="table-value" style="text-align: center; padding: 5px; border-right: 1px solid black; border-top: 1px solid black; font-weight: bold;"><?php echo number_format($total_price_tmc, 2) ?? '----'; ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="footer" style="margin-top: 100px;">
            <table class="footer__table">
                <tr class="footer__table-row">
                    <td class="table-td" style="text-align: center; padding-bottom: 20px; width: 400px;">
                        Владелец ознакомлен и согласен с данными приема
                    </td>
                    <td class="table-td" style="text-align: center; padding-bottom: 20px;">
                        Ветеринарный специалист
                    </td>
                </tr>
                <tr class="footer__table-row">
                    <td class="table-td" style="text-align: center; padding-bottom: 20px;">
                        <?php echo $visit->owner->fullname; ?>
                    </td>
                    <td class="table-td" style="text-align: center; padding-bottom: 20px;">
                        <?php echo $visit->specialists->fullname; ?>
                    </td>
                </tr>
                <tr class="footer__table-row">
                    <td class="table-td" style="text-align: center;">
                        _________________________________________
                    </td>
                    <td class="table-td" style="text-align: center;">
                        _________________________________________
                    </td>
                </tr>
                <tr class="footer__table-row">
                    <td class="table-td" style="text-align: center;">
                        подпись
                    </td>
                    <td class="table-td" style="text-align: center;">
                        подпись
                    </td>
                </tr>
            </table>
        </div>
        <?php if ($num < count($pets) - 1) : ?>
            <pagebreak />
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>


<style>
    .font-bold {
        font-weight: bold;
    }

    .underline-text {
        border-bottom: 1px solid black;
    }

    .full-width {
        width: 700px;
    }

    .fz-12 {
        font-size: 15px;
    }
</style>