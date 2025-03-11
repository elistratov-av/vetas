<?php

use app\models\db\Visits;
use app\modules\admin\helpers\VisitStatusHelper;
use app\common\models\VisitStatus;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Visits */
/* @var $mosruChannel int */

$this->blocks['content-header'] = 'Прием № ' . Html::encode($model->id);
?>
<div class="box">
    <div class="box-body">
        <table class="table table-bordered table-hover">
            <tr>
                <th>
                    Номер талона
                </th>
                <td>
                    <?php echo Html::encode($model->ticket_number) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Время
                </th>
                <td>
                    <?php if ($model->channel == 4) {
                        echo Html::encode($model->fact_start_dttm ?? $model->created_at);
                    } else {
                        $time = str_replace("[\"", '', $model->time_range);
                        $time = str_replace('","', ' — ', $time);
                        $time = str_replace('")', ' ', $time);
                        echo Html::encode($time);
                    } ?>
                </td>
            </tr>
            <tr>
                <th>
                    Владелец
                </th>
                <td>
                    <?php
                    // Владельца нет у типа визитов VISIT_VC_SHELTER
                    if (!empty($model->owner)) {
                        echo Html::a(
                            Html::encode($model->owner->fullname),
                            ['/adminv/owners/profile', 'id' => $model->owner->id],
                            ['target' => '_blank']
                        );
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Животные
                </th>
                <td>
                    <?php foreach ($model->pets as $pet): ?>
                        <?php echo Html::encode($pet->name . ', ') ?>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th>
                    Специалист
                </th>
                <td>
                    <?php echo ($model->specialists === null)
                        ? ''
                        : Html::a(
                            Html::encode($model->specialists->fullname),
                            ['/adminv/specialists/edit', 'id' => $model->specialists->id],
                            ['target' => '_blank']
                        ); ?>
                </td>
            </tr>
            <tr>
                <th>
                    Организация
                </th>
                <td>
                    <?php echo Html::encode($model->organization->short_name); ?>
                </td>
            </tr>
            <tr>
                <th>
                    Услуги
                </th>
                <td>
                    <?php echo isset($model->services) ? implode('<br>', ArrayHelper::getColumn($model->services, 'name')) : ''; ?>
                </td>
            </tr>
            <tr>
                <th>
                    Статус
                </th>
                <td>
                    <?php echo VisitStatusHelper::statusLabel($model->status) ?>
                </td>
            </tr>
            <?php if ($model->status == VisitStatus::CANCELED): ?>
            <tr>
                <th>
                    Автор отмены приема
                </th>
                <td>
                    <?php
                    // - имя специалиста гиперссылкой, если отменено специалистом руками (независимо от того, по чьей инициативе)
                    // - либо "Приём отменён владельцем" (отмена через мос.ру/мпгу)
                    if ($model->updated_by === null) {
                        if ($model->channel == $mosruChannel && $model->cancel_initiator == $model::INITIATOR_IS_OWNER) {
                            // прием с mos.ru, отменен владельцем
                            echo Html::encode('Прием отменен владельцем');
                        } else {
                            echo Html::encode('Не определен');
                        }
                    } else {
                        if ($model->updateAuthor === null) {
                            echo Html::encode('Не определен');
                        } else {
                            $id_specialist = null;
                            if ($model->updateAuthorSpec !== null) {
                                $id_specialist = $model->updateAuthorSpec->id;
                            } elseif ($model->updateAuthorSpecFirst !== null) {
                                $id_specialist = $model->updateAuthorSpecFirst->id;
                            }
                            echo ($id_specialist === null)
                                ? Html::encode($model->updateAuthor->fullname)
                                : Html::a(Html::encode($model->updateAuthor->fullname),
                                    ['specialists/edit', 'id' => $id_specialist],
                                    ['target' => '_blank']);
                        }
                    }
                    ?>
                </td>
            </tr>
                <tr>
                    <th>
                        Причина отмены приема
                    </th>
                    <td>
                        <?php
                        // "Отменено по инициативе организации + причина отмены" - отменённые руками с указанием "по инициативе организации"
                        // "Отменено по инициативе владельца + причина отмены"- отменённые руками с указанием "по инициативе владельца"
                        // "Отменено владельцем через mos.ru"
                        if ($model->cancel_initiator == $model::INITIATOR_IS_CLINIC) {
                            $reason = 'Отменено по инициативе организации.';
                        } else {
                            if ($model->cancelledByOwnerViaMosru === null) {
                                $reason = 'Отменено по инициативе владельца.';
                            } else {
                                $reason = 'Отменено владельцем через mos.ru';
                            }
                        }
                        if (!empty($model->change_reason)) {
                            $reason .= ' Причина: ';
                            $reason .= $model->change_reason;
                        }
                        echo Html::encode($reason);
                        ?>
                    </td>
                </tr>
            <?php endif;?>
            <?php if ($model->status == VisitStatus::TIMEOUT): ?>
                <tr>
                    <th>
                        Автор отмены приема
                    </th>
                    <td>
                        Система
                    </td>
                </tr>
                <tr>
                    <th>
                        Причина отмены приема
                    </th>
                    <td>
                        Неявка владельца
                    </td>
                </tr>
            <?php endif;?>
            <tr>
                <th>
                    Оплачен
                </th>
                <td>
                    <?php echo ($model->is_paid) ? 'Да' : 'Нет'; ?>
                </td>
            </tr>
            <tr>
                <th>
                    Прием с mos.ru
                </th>
                <td>
                    <?php echo ($model->channel == $mosruChannel) ? 'Да' : 'Нет'; ?>
                </td>
            </tr>
        </table>
    </div>
</div>
