<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 30.11.18
 * Time: 17:48
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\grid\ActionColumn;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $dataProvider \app\modules\admin\data\AdminDataProvider */
/* @var $searchModel \app\modules\adminv\models\search\RefsSearch */
/* @var $crud_id string */
/* @var $title string */

$js = <<<JS
    $('body').on('click', 'a.delete-button', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $('#delete-reason-modal').find('form').attr('action', url).modal('show');
    });
    $('#delete-reason-modal').on('hidden.bs.modal', function() {
        var F = $(this).find('form');
        F.attr('action', '');
        F.find('select').val('');
        F.yiiActiveForm('resetForm');
    });
JS;

$this->registerJs($js);

$this->blocks['content-header'] = $title;
?>
<div class="box">
    <div class="box-body">
        <?php
            echo Html::a(
                '<span class="glyphicon glyphicon-plus"></span> Добавить',
                ['/adminv/refs/create', 'crud_id' => $crud_id],
                ['class' => 'btn btn-sm btn-success btn-flat']
            );
        ?>
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Название',
                    'attribute' => 'title',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{edit} {delete}',
                    'buttons' =>
                        [
                            'edit' => function ($url, $model, $key) use ($crud_id)
                            {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-pencil"></span>',
                                    Url::to(['/adminv/refs/edit', 'id' => $model->id, 'crud_id' => $crud_id]),
                                    ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                                );
                            },
                            'delete' => function ($url, $model, $key) use ($crud_id)
                            {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-trash"></span>',
                                    Url::to(['/adminv/refs/delete', 'id' => $model->id, 'crud_id' => $crud_id]),
                                    [
                                        'class' => 'btn btn-xs btn-danger',
                                        'title' => 'Удалить',
                                        'data-confirm' => 'Вы действительно хотите данную запись? Действие нельзя отменить.',
                                    ]
                                );
                            },
                        ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
