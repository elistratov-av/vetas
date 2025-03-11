<?php

use app\modules\subscription\assets\SubscriptionAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $data array */
$values = $data['data'];
function convert_dt($date){

    if(!$date)
        return '';

    return \DateTime::createFromFormat('Y-m-d', $date)->format('d.m.Y');
}

?>
<div class="wrapper">
  <div class="content">
    <div class="header">
      <img
        class="header__img"
        src='img/ic-journal-reg-logo.png'
      /><span class="header__title"
        >Государственная ветеринарная<br />
        служба города Москвы</span
      >
    </div>
    <div class="title">Электронный паспорт животного</div>
    <div class="table-container">
      <table class="table">
        <tr class="table__row">
          <td class="table__key">ФИО владельца</td>
          <td class="table__value"><?php echo $values[ 'fullname'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Контактный телефон</td>
          <td class="table__value"><?php echo $values[ 'phone'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">E-mail</td>
          <td class="table__value"><?php echo $values[ 'email'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Адрес (факт)</td>
          <td class="table__value">
              <?php echo $values[ 'address'] ??  'Нет данных';?>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Вид животного</td>
          <td class="table__value"><?php echo $values[ 'species'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Порода</td>
          <td class="table__value"><?php echo $values[ 'breed'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Кличка</td>
          <td class="table__value"><?php echo $values[ 'name'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Способ идентификации</td>
          <td class="table__value"><?php echo $values[ 'ident_type'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Идентификационный номер</td>
          <td class="table__value"><?php echo $values[ 'ident_code'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Пол</td>
          <td class="table__value"><?php echo $values[ 'sex'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Дата рождения</td>
          <td class="table__value"><?php echo $values[ 'birthday'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Организация регистрации</td>
          <td class="table__value"><?php echo $values[ 'org_name'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Дата регистрации</td>
          <td class="table__value"><?php echo $values[ 'reg_date'] ??  'Нет данных';?></td>
        </tr>
        <tr class="table__row">
          <td class="table__key">Регистрационное удостоверение</td>
          <td class="table__value"><?php echo $values[ 'reg_cert'] ??  'Нет данных';?></td>
        </tr>
      </table>
      <table class="table table--border">
        <caption class="table__caption">
          Вакцинация против бешенства
        </caption>
        <tr class="table__headlines">
          <th class="table__headline">Наименование вакцины</th>
          <th class="table__headline">Производитель</th>
          <th class="table__headline">Номер партии / серии</th>
          <th class="table__headline">Срок годности</th>
          <th class="table__headline">Дата вакцинации</th>
        </tr>
          <?php if(!$values['pet_rabies_vaccination']): ?>
              <tr class="table__row--border">
                  <td class="table__item" colspan="5">Нет данных</td>
              </tr>
          <?php endif; ?>
          <?php foreach ($values['pet_rabies_vaccination'] as $vac): ?>
              <tr class="table__row table__row--border">
                  <td class="table__item"><?php echo $vac['drug_name']?></td>
                  <td class="table__item"><?php echo $vac['producer_name']?></td>
                  <td class="table__item"><?php echo $vac['batch']?></td>
                  <td class="table__item"><?php echo convert_dt($vac['expiry_date'])?></td>
                  <td class="table__item"><?php echo convert_dt($vac['date'])?></td>
              </tr>
          <?php endforeach; ?>
      </table>
      <table class="table table--border">
        <caption class="table__caption">
          Другие вакцинации
        </caption>
        <tr class="table__headlines">
          <th class="table__headline">Наименование вакцины</th>
          <th class="table__headline">Производитель</th>
          <th class="table__headline">Номер партии / серии</th>
          <th class="table__headline">Срок годности</th>
          <th class="table__headline">Дата вакцинации</th>
        </tr>
          <?php if(!$values['pet_other_vaccinations']): ?>
              <tr class="table__row--border">
                  <td class="table__item" colspan="5">Нет данных</td>
              </tr>
          <?php endif; ?>
          <?php foreach ($values['pet_other_vaccinations'] as $vac): ?>
              <tr class="table__row table__row--border">
                  <td class="table__item"><?php echo $vac['drug_name']?></td>
                  <td class="table__item"><?php echo $vac['producer_name']?></td>
                  <td class="table__item"><?php echo $vac['batch']?></td>
                  <td class="table__item"><?php echo convert_dt($vac['expiry_date'])?></td>
                  <td class="table__item"><?php echo convert_dt($vac['date'])?></td>
              </tr>
          <?php endforeach; ?>
      </table>
      <table class="table table--border">
        <caption class="table__caption">
          Дегельминтизация
        </caption>
        <tr class="table__headlines">
          <th class="table__headline">Дегельминтизация</th>
          <th class="table__headline">Производитель</th>
          <th class="table__headline">Дата обработки</th>
        </tr>
          <?php if(!$values['pet_dehelmintization']): ?>
            <tr class="table__row--border">
                <td class="table__item" colspan="3">Нет данных</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($values['pet_dehelmintization'] as $vac): ?>
              <tr class="table__row table__row--border">
                  <td class="table__item"><?php echo $vac['drug_name']?></td>
                  <td class="table__item"><?php echo $vac['producer_name']?></td>
                  <td class="table__item"><?php echo convert_dt($vac['date'])?></td>
              </tr>
          <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>
<div class="footer__container">
    <div class="footer"><!--.footer__title Владелец ознакомлен и согласен с данными приема-->
        <table class="footer__table">
            <tr class="footer__table-row">
                <td class="footer__table--left footer__sign footer__sign-title">ФИО специалиста, сформировавшего печатную форму
                </td>
                <td class="footer__table--right footer__sign footer__sign-title"><span>Дата формирования </span><span
                        class="data"><?php echo date('d.m.Y') ?></span></td>
            </tr>
            <tr class="footer__table-row">
                <td class="footer__sign-name"><?php echo $values['spec_fullname'];?></td>
                <td class="footer__sign-sign">Подпись_________________________</td>
            </tr>
        </table>
    </div>
</div>
