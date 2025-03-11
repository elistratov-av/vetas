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

/* @var $this \yii\web\View */
/* @var $dataProvider \app\modules\admin\data\AdminDataProvider */
/* @var $searchModel \app\modules\adminv\models\search\OrganizationSearch */

$js = <<<JS
    $('body').on('click', 'a.delete-button', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $('#delete-reason-modal').find('form').attr('action', url);
        $('#delete-reason-modal').modal('show');
    });
    $('#delete-reason-modal').on('hidden.bs.modal', function() {
        var F = $(this).find('form');
        F.attr('action', '');
        F.find('select').val('');
        F.yiiActiveForm('resetForm');
    });
JS;

$this->registerJs($js);

$this->blocks['content-header'] = 'Организации';
?>
<div class="box">
    <div class="box-body">
        <?php
        /*if (Yii::$app->user->can('data.organizations.manage.W')) {*/
            echo Html::a(
                '<span class="glyphicon glyphicon-plus"></span> Добавить',
                ['/adminv/organizations/create'],
                ['class' => 'btn btn-sm btn-success btn-flat']
            );
        /*}*/
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
                    'attribute' => 'name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\Organizations */
                        return $model->short_name;
                    },
                ],
                [
                    'label' => 'Родительская организация',
                    'attribute' => 'parent_name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\Organizations */
                        return $model->parentOrganization === null ? '-' : $model->parentOrganization->short_name;
                    },
                ],
                [
                    'label' => 'Головная организация',
                    'attribute' => 'root_name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\Organizations */
                        return $model->rootOrganization === null ? '-' : $model->rootOrganization->short_name;
                    },
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{edit} {delete}',
                    'buttons' =>
                        [
                            'edit'   => function ($url, $model, $key) {
                                /* @var $model \app\models\db\Organizations */
                                return Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])
                                    ? Html::a(
                                        '<span class="glyphicon glyphicon-pencil"></span>',
                                        ['organizations/update', 'id' => $model->id],
                                        ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                                    )
                                    : '';
                            },
                            'delete' => function ($url, $model, $key) {
                                /* @var $model \app\models\db\Organizations */
                                return Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])
                                    ? Html::a(
                                        '<span class="glyphicon glyphicon-trash"></span>',
                                        ['organizations/delete', 'id' => $model->id],
                                        [
                                            'class'        => 'btn btn-xs btn-danger delete-button',
                                            'title'        => 'Удалить',
                                            // 'data-method'  => 'post',
                                            // 'data-confirm' => 'Вы уверены, что хотите удалить организацию?',
                                        ]
                                    )
                                    : '';
                            },
                        ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
<?php echo $this->render('delete_reason_modal');
