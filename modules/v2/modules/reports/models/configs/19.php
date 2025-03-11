<?php

return [
    // Журнал учета лабораторных исследований на паразитарные болезни животных
    [
        'label' => '№ п/п',
        'prop_path' => 'NPP',
        'prop_type' => 'string',
    ],
    [
        'label' => '№ пробы крови из капилляра',
        'prop_path' => 'P12_Analysisnum',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Дата поступления',
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
        'label' => 'Возраст',
        'prop_path' => 'P10_Petbirthday',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Количество животных в гурте, отаре, группе',
        'prop_path' => 'P19_Animalcount',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Дата взятия материала',
        'prop_path' => 'P18_Analysisdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Название материала',
        'prop_path' => 'P12_Analysisnum',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Количество поступивших проб',
        'prop_path' => 'P14_Analysiscount',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'На что исследовалось',
        'prop_path' => 'P20_Analysisobject',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Метод исследования',
        'prop_path' => 'P21_Diagnostictechnique',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Ход и результат исследования',
        'prop_path' => 'P15_Analysisresult',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Название выделенного возбудителя и его характеристика',
        'prop_path' => 'P15_Activator',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Дата отправки ответа',
        'prop_path' => 'P22_Parasdiseasesanswerdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => '№ экспертизы',
        'prop_path' => 'P2_SerialServiceNum',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Подпись врача, проводившего исследование',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
];
