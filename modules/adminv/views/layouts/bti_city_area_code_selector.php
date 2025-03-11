<?php

/** @var $this \yii\web\View */
/** @var $bti_city_area_codes array */
use roboapp\multiselect\MultiSelect;

/*
 * Фикс CSS
 */
$this->registerCss('
.multiselect-container.dropdown-menu > .multiselect-item.multiselect-group {
    margin-left: -10px;
}
.multiselect-container.dropdown-menu > .multiselect-item.multiselect-group .caret-container {
    right: 10px;
    position: absolute;
}
');

echo MultiSelect::widget([
    'name' => 'bti_cac',
    'value' => Yii::$app->request->get('bti_cac'), // bti_city_area_codes
    'data' => $bti_city_area_codes,
    'options' => [
        'multiple' => true,
    ],
    'clientOptions' => [
        'enableClickableOptGroups' => true,
        'enableCollapsibleOptGroups' => true,
        'collapseOptGroupsByDefault' =>  true,
        'nonSelectedText' => 'Не выбрано',
        'enableCaseInsensitiveFiltering' => true,
        'includeResetOption' => true,
        'resetText' => 'Сбросить',
        'maxHeight' => 450,
    ]
]);