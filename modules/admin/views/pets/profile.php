<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.01.19
 * Time: 16:01
 */


use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Профиль питомца №' . Html::encode($model->id). ' ' . Html::encode($model->name);
 ?>

    <div class="box">
        <div class="box-body">
            <table class="table table-bordered table-hover">
                <tr>
                    <th>
                        Имя
                    </th>
                    <td>
                        <?= Html::encode($model->name) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Дата рождения
                    </th>
                    <td>
                        <?= Html::encode($model->birthday) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Пол
                    </th>
                    <td>
                        <? switch($model->sex) {
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
                        <?= Html::encode($model->species->name)?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Порода
                    </th>
                    <td>
                        <? if ($model->breeds) {
                            echo Html::encode($model->breeds->name);
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
                        <? if ($model->guide_dog) {
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
                        <? if ($model->castrated) {
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
                        <? foreach ($model->owners as $owner) {
                            echo Html::a(Html::encode($owner->fullname), Url::to(['owners/profile', 'id' => $owner->id]), [
                                'target' => '_blank',
                            ]);
                        } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>