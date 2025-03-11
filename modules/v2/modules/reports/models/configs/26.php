<?php

return [
    // Журнал по оказанию ветеринарных услуг бригадами ветеринарная помощь на дому
    [
        'label' => 'Дата',
        'prop_path' => 'P3_Visitstartdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => '№ по журналу приема и регистрации вызовов',
        'prop_path' => null,
        'prop_type' => 'string',
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
        'label' => 'Повод вызова',
        'prop_path' => null,
        'prop_type' => 'string',
    ],
    [
        'label' => 'Категория льгот',
        'prop_path' => 'discount_name',
        'prop_type' => 'string',
        'param_type' => 'visit_price_discount_name',
    ],
    [
        'label' => 'Время приема вызова',
        'prop_path' => 'created_at',
        'prop_type' => 'datetime',
        'param_type' => 'visit_created_at',
    ],
    [
        'label' => 'Время прибытия',
        'prop_path' => 'fact_start_dttm',
        'prop_type' => 'datetime',
        'param_type' => 'visit_fact_start_dttm',
    ],
    [
        'label' => 'Время окончания',
        'prop_path' => 'fact_end_dttm',
        'prop_type' => 'datetime',
        'param_type' => 'visit_fact_end_dttm',
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
        'label' => 'Номер чипа животного',
        'prop_path' => 'P0_Petchpidentificationcode',
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
        'label' => 'Осмотр',
        'prop_path' => 'visit_description_anamnesis',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Дополнительные исследования',
        'prop_path' => 'additional',
        'prop_type' => 'string',
        'param_type' => 'gov_services_com_class_journal',
    ],
    [
        'label' => 'Клинические признаки',
        'prop_path' => 'visit_description_clinical_signs',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Оказание ветеринарных услуг',
        'prop_path' => 'visit_services_list',
        'prop_type' => 'string',
        'param_type' => 'visit_services_list',
    ],
    [
        'label' => 'Рекомендации',
        'prop_path' => 'visit_description_recommendations',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Особые отметки',
        'prop_path' => 'P18_Petspecialtrait',
        'prop_type' => 'string',
        'param_type' => 'visit_service_param_value',
    ],
    [
        'label' => 'Номер квитанции',
        'prop_path' => 'bill_num',
        'prop_type' => 'string',
        'param_type' => 'visit_price_bill_num',
    ],
    [
        'label' => 'Общая стоимость оказанных ветеринарных услуг',
        'prop_path' => 'price_with_discount',
        'prop_type' => 'string',
        'param_type' => 'visit_price_price_with_discount',
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
];
