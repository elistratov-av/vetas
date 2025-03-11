<?php

/* @var $this \yii\web\View */

$js = <<<JS
$('body').on('click', 'a.show-diff', function (e) {
    e.preventDefault();
    var html = $(this).closest('td').find('div.changes-diff').html();
    $('#diff-modal').find('.modal-body > .row').html(html);
    $('#diff-modal').modal('show');
});
$('#diff-modal').on('hidden.bs.modal', function (e) {
    $(this).find('.modal-body > .row').html('');
});
JS;

$this->registerJs($js);
?>
<div class="modal fade" id="diff-modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Просмотр изменений</h4>
            </div>
            <div class="modal-body">
                <div class="row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
