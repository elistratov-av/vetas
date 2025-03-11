<?php

use app\common\components\pdfGenerator\PdfGenerator;
use app\models\db\Agreements;
use app\models\db\Contacts;
use app\models\db\FiasAddresses;
use app\models\db\PetOwners;
use app\models\db\Visits;
use app\modules\admin\models\Owners;
use yii\web\View;

/**
 * @var View                $this
 * @var array               $data
 * @var Owners              $owner
 * @var Agreements          $agreement
 * @var array               $initial_data
 */
extract($data);

if (!empty($data)) {
    $visitStart = $visit['fact_start_dttm'] ?? $visit['start_dttm'] ?? $agreement['updated_at'] ?? date('Y-m-d H:i:s');
    $visitDate = PdfGenerator::dateFromFormat($visitStart, 'Y-m-d H:i:s');
    $prettyVisitDate = "«" . $visitDate['d'] . "»" . ' ' . $visitDate['M'] . ' ' . $visitDate['Y'] . 'г.';
    $fullname = $owner->fullname;
    $fullAddress = $initial_data['fact'] ?? $initial_data['reg'];
    $phone = $initial_data['phone'];
    $phoneName = $phone ?? "___________";
    $mail = $initial_data['mail'];
    $mailName = $mail ?? "________________________";
} else {
    $prettyVisitDate = "«___» ___________ 20__ г.";
    $fullname = "___________________________________";
    $fullAddress = "____________________________________________ __________________________________";
    $phoneName = "___________";
    $mailName = "________________________";
}
?>
<div class="wrapper">
    <div class="content">
        <div class="header__title"><br><br><br><br><br><br>СОГЛАСИЕ<br>на обработку персональных данных</div>
        <br>
        <table class="header_city_date">
            <tr>
                <td class="header__left">г. Москва</td>
                <td class="header__right"><?php echo $prettyVisitDate?></td>
            </tr>
        </table>
        <div class="body__content">
            Я, ____<span class="body_prefilled_fields"><?php echo $fullname?></span>________________________________________
            <br><div class="body_comments">(Ф.И.О.)</div><br>
            ____________________________________________________________ серия ________ № __________
            <br><div class="body_comments">(вид документа, удостоверяющего личность)</div><br>
            выдан ________________________________________________________________________________,
            <br><div class="body_comments">(когда и кем)</div><br>
            проживающий(ая) по адресу: ___<span class="body_prefilled_fields"><?php echo $fullAddress?></span>______________________________________________________,
            телефон ___<span class="body_prefilled_fields"><?php echo $phoneName?></span>___,
            электронная почта ___<span class="body_prefilled_fields"><?php echo $mailName?></span>___,
            настоящим даю своё согласие на обработку ГБУ «Мосветобъединение» (ИНН 7708006274, адрес: г. Москва,
            ул. Донская, д. 37, корп.3) (далее – Оператор) моих персональных данных и подтверждаю, что давая такое
            согласие я действую своей волей и в своих интересах.
        </div>
        <div class="body__content">
            Согласие дается мною для целей предоставления мне Оператором ветеринарных услуг, в том числе и не
            ограничиваясь этим, для внесения моих персональных данных в информационную систему в области ветеринарии
            на базе программного обеспечения «ВетАС», получения информационных рассылок, и распространяется на
            следующие мои персональные данные: фамилия, имя, отчество; число, месяц, год рождения; вид, серия, номер
            документа, удостоверяющего личность, дата выдачи, наименование органа, выдавшего его; адрес регистрации по
            месту жительства (месту пребывания), адрес фактического проживания; номер контактного телефона, электронной
            почты или сведения о других способах связи; иные персональные данные, необходимые для предоставления мне
            ветеринарных услуг.
        </div>
        <div class="body__content">
            Настоящее согласие предоставляется на осуществление любых действий в отношении моих персональных данных,
            которые необходимы или желаемы для достижения указанных выше целей, включая (без ограничения) сбор,
            систематизацию, накопление, хранение, уточнение (обновление, изменение), использование, распространение
            (в том числе передача), обезличивание, блокирование, уничтожение, а также осуществление любых иных
            действий с моими персональными данными с учетом федерального законодательства.
        </div>
        <div class="body__content">
            Я оставляю за собой право отозвать свое согласие полностью или частично по моей инициативе на основании
            личного письменного заявления. В случае получения моего письменного заявления об отзыве настоящего согласия
            на обработку персональных данных Оператор обязан прекратить их обработку.
        </div>
        <br>
        <div class="footer__content">
            Данное согласие действует с
            <?php echo $prettyVisitDate ?? "«___» ___________ 20__ г."?>
            по «___» ________ 20__г.
            <br><br>
            _________________________________________________________________________________________
            <br><div class="body_comments">(Ф.И.О., подпись лица, давшего согласие)</div>
        </div>
</div>