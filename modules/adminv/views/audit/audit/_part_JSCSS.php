<?php

/**
 * @var View $this
 */

use yii\web\View;

$js = <<<JS
    $('.show-visit').click(function(){
        var link = $(this);
        $.get(link.attr('href'), function(html){
            $('#visitModal').html(html);
            $('#visitModal').modal('show');
            }, 'html');
        return false;
    });    
JS;
$css = <<<CSS
pre{
    white-space: pre-wrap;
}
CSS;
$this->registerCss($css);
$this->registerJs($js, View::POS_READY);

