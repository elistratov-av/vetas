<?php

use yii\helpers\Html;

$this->blocks['content-header'] = 'Истроия обновлений справочника адресов ФИАС';
?>
<div class="box">
    <div class="box-body">
        <ul class="list-group">
            <?
            if(!$history)
                echo "<p>Ошибка подключения к ФИАС</p>";
            else{
                foreach ($history as $item) {
                    $text = $item['text'];
                    $ver = $item['version'];
                    echo "<li class=\"list-group-item\">Обновлено до версии: $ver $text</li>";
                }
            }
            ?>
        </ul>
    </div>
</div>


