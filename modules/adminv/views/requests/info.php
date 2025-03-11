<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 31.07.19
 * Time: 16:04
 */

/**
 * @var $model \app\models\db\ChangeRequest
 */

use app\modules\v2\modules\changeRequest\helpers\RequestEntitiesHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Запрос № ' . Html::encode($model->id);
?>

    <div class="box">

        <div class="box-body">
            <table class="table table-bordered table-hover">
                <tr>
                    <th>
                        Справочник
                    </th>
                    <td>
                        <?= RequestEntitiesHelper::entityLabel($model->entity_name) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Состояние запроса изменений
                    </th>
                    <td>
                        <?= RequestEntitiesHelper::stateLabel($model->state) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Тип запроса изменений
                    </th>
                    <td>
                        <?= RequestEntitiesHelper::typeLabel($model->type) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Описание запроса изменений
                    </th>
                    <td>
                        <?= Html::encode($model->description) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Дата подачи запроса
                    </th>
                    <td>
                        <?= Html::encode($model->created_at) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Автор запроса
                    </th>
                    <td>
                        <?= Html::encode($model->requestAuthor->fullname) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Место работы автора запроса
                    </th>
                    <td>
                        <?= Html::encode($model->organization->short_name) ?>
                    </td>
                </tr>
                <?php if ($model->state !== 'N'): ?>
                    <tr>
                        <th>
                            Дата обработки запроса
                        </th>
                        <td>
                            <?= Html::encode($model->updated_at) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Обработал запрос
                        </th>
                        <td>
                            <?= $model->processedAdmin === null ? '' : Html::encode($model->processedAdmin->fullname) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

<?php
if ($model->state === 'N') {
    echo Html::a(
        'Принять',
        Url::to(['requests/accept', 'id' => $model->id]),
        [
            'data-method' => 'POST',
            'id' => 'grid-custom-button',
            'class' => 'btn btn-success ',
        ]
    );
    echo '&nbsp;&nbsp;&nbsp;';
    echo Html::a(
        'Отклонить',
        Url::to(['requests/reject', 'id' => $model->id]),
        [
            'data-method' => 'POST',
            'id' => 'grid-custom-button',
            'class' => 'btn btn-danger ',
        ]
    );
}
