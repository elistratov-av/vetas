<?php

return [
    // Журнал общих клинических исследований мочи
    [
        'label' => '№ п/п',
        'prop_path' => 'NPP',
        'prop_type' => 'string',
    ],
    [
        'label' => 'Дата',
        'prop_path' => 'P3_Visitstartdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => '№ экспертизы',
        'prop_path' => 'P2_SerialServiceNum',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Кем направлен',
        'prop_path' => 'P33_SpecialistFIO',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Владелец животного',
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
        'label' => 'Кличка',
        'prop_path' => 'P9_Petname',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Цвет',
        'prop_path' => 'P14_Colorurinevalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Прозрачность',
        'prop_path' => 'P16_Transparencyvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Консистенция',
        'prop_path' => 'P67_Urinconsistency',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Удельный вес',
        'prop_path' => 'P61_Urinunitweightvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Реакция',
        'prop_path' => 'P62_Urinreactionvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Лейкоциты',
        'prop_path' => 'P34_Leucocytvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Нитраты',
        'prop_path' => 'P63_Nitratvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Белок',
        'prop_path' => 'P20_Proteinvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Глюкоза',
        'prop_path' => 'P22_Glukozavalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Кетоновые тела',
        'prop_path' => 'P24_Ketonbodvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Уробилиноген',
        'prop_path' => null,
        'prop_type' => 'string',
        // 'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Билирубин',
        'prop_path' => 'P28_Bilirubinvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Эритроциты',
        'prop_path' => 'P32_Erythrocytvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Организованный осадок',
        'prop_path' => 'P64_Urinorddepositionvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Неорганизованный осадок: соли',
        'prop_path' => 'P58_Saltvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Дата ответа',
        'prop_path' => 'P66_UrinAnswerdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Подпись врача',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
];
