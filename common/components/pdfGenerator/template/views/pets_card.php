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

/**
 * @var View                $this
 * @var array               $visitsData
 * @var array               $data
 * @var Pets                $pets
 * @var array               $description_types
 * @var VisitDescriptions[] $descriptions
 * @var PetIdentification   $identification
 */

$value = $data['data'];
$visit = $data['visits'];
$history = $data['his'];
$historyowner = $data['historyowner'];
//$servicePet = $data['petService'];

?>
<div class="header">
    <table width="100%">
        <tr>
            <td class="header__img"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAgCAMAAAAynjhNAAACPVBMVEX///8TIoH////q6u5eZaExO4z///8XKYNgZ6YMIX8JH34SJIHb3eoVJ4IUJ4IeLIYiMYgYKoYNI4F4f7L19vnr7PPU1uYgL4fj5e8zP48rN4sQJIDm5/Hk5vDU1ufR1OWvstCWnMNpcaolMoj8/P3Oz+KipsmUmsJ+hLZlbahka6hYYKBET5hfV5a9dI8OIX/qV2TsTVjsLDL5+fvv7/Xg4u3W2OjR0+LExdy4u9W0t9SpsNCZncORlb+CiLdvdq1rc6tcZKNKU5liV5U3QZLDdY8vOY0hOI3kKDHfKS/6+/z29/ry8/ja2+nBxdu6v9ieocaKkLx+jLuFirl7grSJfatnbqlSWp5JVZ09T5qRk5Q/SZTBco4kOI4pNortbnx3d3ZzWFrpTlpPVljLT1iPTVjcRFjBRE25QUqZQEjHPUe1N0KANzzeLDTpJy+zJi7p5/HLzeDIyt+7v9m+v9a/ts2oq8ynqsyPl8Gvt7e6traqtbN2f7NzerHNl6zJqKrWmKqmpaVZZaVhaaROW6Cbd59nZJ9wZp6lnZxPWJxiWJeKlZQxRZPKepDog48oPI8eN4zFh4sgNYuCiYmEhYXgbnrVcHndY3TZZnKya28vSG2qZmqqY2jNXGSrXWNxYGLnVWKTXWHhUF8YR19CVV7sUl3gT1qSVlncT1mvUleDUFd2S03VRkywQElLSUi+O0fUO0VjQEKfO0BHLz+pNT1aNzqMNDrfLjnPKji7Mje3MTbaMDXpLjV+LjLcJS5eCXmKAAAABnRSTlNJSUpJSUm2S2VjAAACH0lEQVQoz3XS9XPaYBjA8XTCSxOSMkYIIVDcGQyKO5QxaFd3WWV1n7u7u7u7u/9tCzTIreF7l8t7z+fyJD8E4jBVqityISoNM4TyrI3ycm1oqVnAbj0nV5Xu/6c9YZWp0Z09rvEZuHu2CotZYfbzdMSJevoobJJ0yEUt7XiBRR3SzEqFJ8Obg3L6FjQUOLQtu0OT2VhTq8m+wijNs8GD17lcYrFMTF8yqUzmknJaRTQsgnAcr1IqeJFkBaHuk9Cp+UQy5hWqpE7cuRgiyaTRy3Vy5KFYQo0iCNHTrt8o5OjCPpJMLIUQBIFVIZM281VuEoa319InXHmosc+GYGUQoLPFo2aV3lsnjsbj/vVaRSvXFFFTACybZ0ARMf2Wa6P3V9I9HN0fCPcgGCgwQCnBMXs6PTOVmk3NnjldjgDAMJNgxbqZsamX42PffveeRwELpyYeD106MOxYtRpjYfv3m4/edu97TzPKxpMjn4Yu73yTZuXq4Vs3jg907xj8aV3LxncmLuwaGBx596OYy5ng6ucfb09/vfti8pf1Ijw/E5RBXKbOk/bxZ6/unXrw2dHbxcxMS/L/mvzok79zf75Mf5hzWJtzQ6jAlr39/ddfP7165Wzb7oUsIs51NiuPtAUOdgmWszCfgs3Bw0alxAazcCUfQyMBf0OTBSnBiNG3qV5poUowllBoGwx8tAQDjDRbCBSwsYhmgNgoFJRkpiL+Bysrf5s4XVwGAAAAAElFTkSuQmCC" alt="" srcset=""></td>
            <td class="header__title">Государственная ветеринарная<br/> служба города Москвы</td>
        </tr>
    </table>
</div>
<div class="wrapper">
    <div class="content">
        <div class="title_vd">Амбулаторная карта</div>
            <div class="table-container">
            <table class="table">
                <caption class="table__caption"><?= sprintf('Сведения о животн%s', count($value) > 1 ? 'ых' : 'ом') ?></caption>
                <?php foreach ($value as $values): ?>
                <tr>
                      <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key">Кличка</td>
                                <td class="table-value"><?php echo $values['name'] ;?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Вид животного</td>
                                <td class="table-value"><?php echo $values['species'] ; ?></td>
                            </tr>s
                            <tr class="table__row">
                                <td class="table-key">Порода</td>
                                <td class="table-value"><?php echo $values['breed'] ; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Пол</td>
                                <td class="table-value"><?php echo $values['sex']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Возраст</td>
                                <td class="table-value"><?php echo $values['birthday'] ; ?></td>
                            </tr>
                        </table>
                    </td>
                        <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key">Способ индетификации</td>
                                <td class="table-value"><?php echo $values['ident_type']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Идентификационный номер</td>
                                <td class="table-value"><?php echo $values['pet_ident']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Номер регистрационного удостоверения</td>
                                <td class="table-value"><?php echo $values['regnum']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Дата вакцинации против бешенства</td>
                                <td class="table-value"><?php echo $values['vaccination_date']; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php foreach ($visit as $visits): ?>
                <table class="table">
                <caption class="table__caption">Приём №<?php echo $visits['id']; ?> от <?php echo $visits['start_date'] ?? '-'; ?></caption>
                <tr class="table__row">
                    <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key">ФИО обратившегося</td>
                                <td class="table-value"><?php echo $visits['owner']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Клиника</td>
                                <td class="table-value"><?php echo $visits['clinic']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">ФИО Специалиста</td>
                                <td class="table-value"><?php echo $visits['spec']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Анамнез</td>
                                <td class="table-value"><?php echo $visits['anamnesis']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Клинические признаки (симптомы)</td>
                                <td class="table-value"><?php echo $visits['clinicalSigns']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Предварительный диагноз</td>
                                <td class="table-value"><?php echo $visits['preDiagnosis']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Заключительный диагноз</td>
                                <td class="table-value"><?php echo $visits['finDiagnosis']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Схема лечения и рекомендации</td>
                                <td class="table-value"><?php echo $visits['treatment']; ?> <?php echo $visits['recommendations']; ?></td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Лечебная помощь</td>
                                <td class="table-value"><?php echo $visits['assurance']; ?></td>
                            </tr>
                            <?php //foreach ($servicePet as $service): ?>
                            <tr class="table__row">
                                <td class="table-key">Стоимость оказанных услуг</td>
                                <td class="table-value-description"><?php //echo $service['cod']; ?> <?php echo $visits['serv']; ?> <?php //echo 'x'.$service['count']; ?> <?php //echo $service['price']; ?></td>
                            </tr>
                            <?php //endforeach;?>
                            <tr class="table__row">
                                <td class="table-key">Общая стоимость приёма</td>
                                <td class="table-value-description"><?php echo $visits['pricesum']; ?></td>
                            </tr>
                            <?php if (!empty($visits->visitsGovService->visitServiceParamValues->num_value)): ?>
                            <tr class="table__row">
                                <td class="table-key">Данные отчётов по оказанным услугам:</td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key">Услуга</td>
                                <td class="table-key">Название показателя</td>
                                <td class="table-key" >Данные показателя</td>
                            </tr>
                            <tr class="table__row">
                                <td class="table-key"><?php echo $visits['reportName']; ?></td>
                                <td class="table-key"><?php echo $visits['nameIndicator']; ?></td>
                                <td class="table-key" ><?php echo $visits['indicatorData']; ?> <?php echo $visits['indicatorEd'] ?? ' '; ?></td>
                            </tr>
                            <?php endif;?>
                        </table>
                    </td>
            </table>
            <?php endforeach; ?>
                <?php if (empty($visits)): ?>
                <table class="table">
                    <caption class="table__caption">Сведения о владельце</caption>
                    <tr>
                        <td class="table-td">
                            <table class="table">
                                <tr class="table__row">
                                    <td class="table-key">ФИО</td>
                                    <td class="table-value"><?php echo $historyowner['name']; ?></td>
                                </tr>
                                <tr class="table__row">
                                    <td class="table-key">Телефон</td>
                                    <td class="table-value"><?php echo $historyowner['phone']; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td class="table-td">
                            <table class="table">
                                <tr class="table__row">
                                    <td class="table-key">E-mail</td>
                                    <td class="table-value"><?php echo $historyowner['email']; ?></td>
                                </tr>
                                <?php if ($historyowner['fact_addr'] === 'Не указан'): ?>
                                    <tr class="table__row">
                                        <td class="table-key">Адрес содержания животного</td>
                                        <td class="table-value"><?php echo $historyowner['addr']; ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr class="table__row">
                                        <td class="table-key">Адрес содержания животного</td>
                                        <td class="table-value"><?php echo $historyowner['fact_addr']; ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>
        </div>
    </div>
</div>
<div class="footer">
    <table class="footer__table">
        <tr class="footer__table-row">
            <td class="footer__table--right footer__sign-title">Подпись вет. специалиста</td>
        </tr>
        <tr class="footer__table-row">
            <td class="footer__sign--right">_______________________________</td>
        </tr>
        <tr class="footer__table-row">
            <td class="footer__table--right footer__sign-name"><?php echo $values['spec_fullname']; ?></td>
        </tr>
    </table>
</div>
<div class="title_vd">История изменения владельцев</div>
<?php if(!$history): ?>
    <table class="table">
    <tr class="table__row">
        <td class="table-td">
            <table class="table">
                <caption class="table__caption"><?php echo $historyowner['date']; ?> </caption>
                <tr class="table__row">
                    <td class="table-key">Владелец</td>
                    <td class="table-value"><?php echo $historyowner['name']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Адрес регистрации</td>
                    <td class="table-value"><?php echo $historyowner['addr']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Проживания</td>
                    <td class="table-value"><?php echo $historyowner['fact_addr']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Телефон</td>
                    <td class="table-value"><?php echo $historyowner['phone']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">E-mail</td>
                    <td class="table-value"><?php echo $historyowner['email']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php endif; ?>
<?php foreach ($history as $his): ?>
<table class="table">
    <caption class="table__caption"><?php echo $his['date']; ?> </caption>
    <tr class="table__row">
        <td class="table-td">
            <table class="table">
                <tr class="table__row">
                    <td class="table-key">Владелец</td>
                    <td class="table-value"><?php echo $his['name']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Адрес регистрации</td>
                    <td class="table-value"><?php echo $his['addr']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Проживания</td>
                    <td class="table-value"><?php echo $his['fact_addr']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">Телефон</td>
                    <td class="table-value"><?php echo $his['phone']; ?></td>
                </tr>
                <tr class="table__row">
                    <td class="table-key">E-mail</td>
                    <td class="table-value"><?php echo $his['email']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php endforeach; ?>
