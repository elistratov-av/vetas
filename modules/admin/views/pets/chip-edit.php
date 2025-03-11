<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.01.19
 * Time: 13:53
 */

use app\models\db\IdentificationTypes;
use \app\modules\admin\models\Pets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$identType = IdentificationTypes::find()->select('name')->orderBy(['name' => SORT_ASC])->indexBy('id')->asArray()->column();
$pet = Pets::findOne(['id' => $model->id_pet]);

$this->blocks['content-header'] = 'Редактирование метки';
?>

<div class="user-form">
    <div class="box">
        <div class="box-body">
            <table class="table table-bordered table-hover">
                <tr>
                    <th>
                        Имя
                    </th>
                    <td>
                        <?= Html::encode($pet->name) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Дата рождения
                    </th>
                    <td>
                        <?= Html::encode($pet->birthday) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Пол
                    </th>
                    <td>
                        <? switch($pet->sex) {
                            case 'm':
                                echo 'М';
                                break;
                            case 'f':
                                echo 'Ж';
                                break;
                            default:
                                echo '';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Вид
                    </th>
                    <td>
                        <?= Html::encode($pet->species->name)?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Порода
                    </th>
                    <td>
                        <? if ($pet->breeds) {
                            echo Html::encode($pet->breeds->name);
                        } else {
                            echo '';
                        } ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Поводырь
                    </th>
                    <td>
                        <? if ($pet->guide_dog) {
                            echo 'Да';
                        } else {
                            echo 'Нет';
                        }?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Кастрирован
                    </th>
                    <td>
                        <? if ($pet->castrated) {
                            echo 'Да';
                        } else {
                            echo 'Нет';
                        }?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Владелец
                    </th>
                    <td>
                        <? foreach ($pet->owners as $owner) {
                            echo Html::a(Html::encode($owner->fullname), Url::to(['owners/profile', 'id' => $owner->id]), [
                                'target' => '_blank',
                            ]);
                        } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php $form = ActiveForm::begin([
        'id' => 'ChipsEditForm'
    ]); ?>
    <?= $form->field($model, 'id_ident_type')
        ->dropDownList($identType, [
            'prompt' => 'Выберите тип метки'
        ])
        ->label('Тип метки') ?>
    <?= $form->field($model, 'identification_code', ['enableAjaxValidation' => true])->label('Код метки') ?>
</div>
<div class="form-group">
    <?= Html::submitButton( 'Сохранить',  ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Отмена',  Url::to('/admin/pets/chips'), ['class' => 'btn btn-default']) ?>
</div>
<?php ActiveForm::end(); ?>
</div>