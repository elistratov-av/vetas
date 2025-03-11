<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 06.12.18
 * Time: 13:59
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\modules\admin\helpers\VisitStatusHelper;
use yii\helpers\Url;

/* @var $model \app\modules\admin\models\Visits */

$this->blocks['content-header'] = 'Визит № ' . Html::encode($model->id);
?>

<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
            <tr>
                <th>
                    Номер талона
                </th>
                <td>
                    <?= Html::encode($model->ticket_number) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Время
                </th>
                <td>
                    <?php if ($model->channel == 4) {
                        echo Html::encode($model->fact_start_dttm  ?? $model->created_at);
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
                    <?= Html::a(Html::encode($model->owner->fullname),
                        Url::to(['owners/profile', 'id' => $model->owner->id]), [
                            'target' => '_blank',
                        ])
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Животное
                </th>
                <td>
                    <?= Html::encode($model->pet->name) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Специалист
                </th>
                <td>
                    <?php if ($model->specialist !== null) {
                        echo Html::a(Html::encode($model->specialist->fullname),
                            Url::to(['specialists/profile', 'id' => $model->specialist->id]), [
                                'target' => '_blank',
                            ]);
                    } else {
                        echo "";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Организация
                </th>
                <td>
                    <?= Html::encode($model->organization->short_name) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Услуги
                </th>
                <td>
                    <?php if(isset($model->services)) {
                        implode("<br>", ArrayHelper::getColumn($model->services, 'name'));
                    } else {
                        echo "";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Статус
                </th>
                <td>
                    <?=VisitStatusHelper::statusLabel($model->status)?>
                </td>
            </tr>
            <tr>
                <th>
                    Оплачен
                </th>
                <td>
                    <?php if ($model->is_paid) {
                        echo "Да";
                    } else {
                        echo 'Нет';
                    } ?>
                </td>
            </tr>
            <?php if($model->shiftType->type == \app\models\db\ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT): ?>
                <tr>
                    <th>
                        Сервисный номер (только для приемов mos.ru)
                    </th>
                    <td>
                        <?php echo $model->message->service_number ?>
                    </td>
                </tr>
                <?php echo Html::a('Удалить', ['/admin/visit/delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-large',
                    'data' => [
                        'confirm' => 'Сейчас будут удалены записи о данном визите из таблиц visits, visits_specialists, visits_gov_services. Данное действие необратимо!',
                        'method' => 'post',
                    ]
                ]); ?>
            <?endif;?>
        </table>
    </div>
</div>
