<?php

return [
    // Журнал цитологических исследований
    [
        'label' => '№ экспертизы',
        'prop_path' => 'P2_SerialServiceNum',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Дата поступления материала',
        'prop_path' => 'P3_Visitstartdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Владелец животного, хозяйство',
        'prop_path' => 'P4_Ownername',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Адрес владельца',
        'prop_path' => 'P5_Owneraddres',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Вид животного',
        'prop_path' => 'P6_Speciesname',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Анамнез',
        'prop_path' => 'visit_description_anamnesis',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Микроскопическая картинка',
        'prop_path' => 'P15_Analysisresult',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Заключение',
        'prop_path' => 'visit_description_conclusion',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Подпись врача',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
    [
        'label' => 'Дата отправки ответа',
        'prop_path' => 'P23_Cytologicscreeninganswerdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_service_param_value',
    ],
];
