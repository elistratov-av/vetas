<?php


use app\common\components\pdfGenerator\PdfGenerator;
use app\common\components\pdfGenerator\PdfVisitGeneratorHelper;
use app\common\helpers\DateHelper;
use app\modules\v2\modules\pets\controllers\TestController;
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
        <div class="title_vd">Документы из ЦХЭД</div>
            <div class="table-container">
            <table class="table">
               
                <?php foreach ($data as $item): ?>
                <tr>
                      <td class="table-td">
                        <table class="table">
                            <tr class="table__row">
                                <td class="table-key">Содержание документа</td>
                                <td class="table-value"><?php echo $item['str'] ;?></td>
                            </tr>
                        </table>
                    </td>
                        
                </tr>
                <?php endforeach; ?>
            </table>
           
            
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
           123
        </tr>
    </table>
</div>

