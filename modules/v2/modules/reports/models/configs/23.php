<?php

return [
    // Журнал регистрации и вакцинации животных
    [
        'label' => '№ п/п',
        'prop_path' => 'NPP',
        'prop_type' => 'string',
    ],
    [
        'label' => 'Дата вакцинации',
        'prop_path' => 'P3_Visitstartdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Регистрационный № животного',
        'prop_path' => 'P0_Petregnum',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'ФИО владельца',
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
        'label' => 'Телефон владельца',
        'prop_path' => 'P5_Ownercontact',
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
        'label' => 'Пол животного',
        'prop_path' => 'P8_Petsex',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Порода животного',
        'prop_path' => 'P7_Breedname',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Кличка животного',
        'prop_path' => 'P9_Petname',
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
        'label' => 'Окрас животного',
        'prop_path' => 'P17_Petcolor',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Особые приметы животного',
        'prop_path' => 'P18_Petspecialtrait',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Номер микрочипа животного',
        'prop_path' => 'P0_Petchpidentificationcode',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Содержание татуировочного клейма',
        'prop_path' => 'P0_Petstampidentificationcode',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Наименование вакцины',
        'prop_path' => 'P0_Vaccinename',
        'prop_type' => 'string',
        'param_type' => 'visit_service_tmc',
    ],
    [
        'label' => 'Серия вакцины',
        'prop_path' => 'P0_Inventorynumber',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Срок годности вакцины',
        'prop_path' => 'P15_Vacexpirationdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Доза',
        'prop_path' => 'P0_VisitServiceTMCcount',
        'prop_type' => 'string',
        'param_type' => 'visit_service_tmc',
    ],
    [
        'label' => 'Подпись владельца',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
    [
        'label' => 'Подпись ветеринарного специалиста',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
    [
        'label' => 'Информация о снятии животного с регистрации',
        'prop_path' => 'P19_Petregexpiredate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Примечание',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
];
