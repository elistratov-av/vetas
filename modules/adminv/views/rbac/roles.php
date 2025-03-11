<?php

use yii\grid\GridView;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ArrayDataProvider */
/* @var $auth \app\common\components\rbac\DbManager */

$this->blocks['content-header'] = 'Роли';
?>
<div class="box">
    <div class="box-body">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'columns' => [
                //'id',
                [
                    'label' => 'Наименование',
                    'attribute' => 'name',
                    'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Описание',
                    'attribute' => 'description',
                    'headerOptions' => ['width' => '40%', 'class' => 'text-center'],
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \yii\rbac\Role */
                        return nl2br($model->description);
                    },
                ],
                [
                    'label' => 'Разрешения',
                    'headerOptions' => ['width' => '40%', 'class' => 'text-center'],
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $widget) use ($auth) {
                        /* @var $model \yii\rbac\Permission */
                        $inner = '';
                        $permissions = $auth->getPermissionsByRole($model->name);
                        $count = count($permissions);
                        $i = 0;
                        foreach ($permissions as $permission) {
                            $inner .= '<p class="small">';
                            $inner .= $permission->description;
                            $inner .= '</p>';
                            if ($count > 3 && $i == 2) {
                                $inner .= '<p class="small more">...</p>';
                            }
                            $i++;
                        }
                        $addCss = $count > 3 ? 'has-more' : '';
                        $html = '<div class="permissions-list-wrapper ' . $addCss . '">';
                        $html .= '<div class="permissions-list">';
                        $html .= $inner;
                        $html .= '</div>';
                        $html .= '</div>';

                        return $html;
                    },
                ],
            ],
        ]);
        ?>
    </div>
</div>
