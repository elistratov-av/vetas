<?php

return [
    // Журнал общих исследований фекалий
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
    // [
    //     'label' => 'Владелец животного',
    //     'prop_path' => 'P4_Ownername',
    //     'prop_type' => 'string',
    //     'param_type' => 'visit_param_value',
    // ],
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
        'label' => 'Возраст животного',
        'prop_path' => 'P10_Petbirthday',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Кличка животного',
        'prop_path' => 'P9_Petname',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Общие свойства: цвет',
        'prop_path' => 'P16_Coprcolorvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Общие свойства: реакция',
        'prop_path' => 'P18_Coprodorvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Общие свойства: консистенция',
        'prop_path' => 'P14_Coprformvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Общие свойства: примеси',
        'prop_path' => 'P20_Acidityvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Химические свойства: Реакция на скрытую кровь',
        'prop_path' => 'P26_Bloodvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Химические свойства: билирубин',
        'prop_path' => 'P24_Bilirubinvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Химические свойства: стеркобилин',
        'prop_path' => 'P22_Stercobilinvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Микроскопические исследования (мышечные волокна)',
        'prop_path' => 'P28_Muscledfibersvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Микроскопические исследования (соединительно-тканные)',
        'prop_path' => 'P30_Contissuefibersvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Микроскопические исследования (волокна)',
        'prop_path' => null,
        'prop_type' => 'string',
        // 'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Микроскопические исследования (нейтральный жир)',
        'prop_path' => 'P32_Neutralfatvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Микроскопические исследования (жирные кислоты)',
        'prop_path' => 'P34_Fattyacidsvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Микроскопические исследования (мыла)',
        'prop_path' => 'P36_Soapvalue',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Дата ответа',
        'prop_path' => 'P39_Coproanswerdate',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Подпись врача',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
];
