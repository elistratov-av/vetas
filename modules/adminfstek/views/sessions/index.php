<?php

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\SessionSearch */

$this->blocks['content-header'] = 'Активные сессии';
?>
<div class="box">
    <div class="box-body">
        <?php echo $this->render('_index_inner', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]); ?>
    </div>
</div>
