<?php

return [
    // Журнал регистрации платных ветеринарных услуг животным
    [
        'label' => '№ п/п',
        'prop_path' => 'NPP',
        'prop_type' => 'string',
    ],
    [
        'label' => 'Число, месяц',
        'prop_path' => 'P3_Visitstartdate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
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
        'label' => 'Регистрационный № животного',
        'prop_path' => 'P0_Petregnum',
        'prop_type' => 'string',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Отметка вакцинации против бешенства',
        'prop_path' => 'P0_PetRabiesVaccinationDate',
        'prop_type' => 'datetime',
        'param_type' => 'visit_param_value',
    ],
    [
        'label' => 'Дата заболевания',
        'prop_path' => 'visit_description_date_disease',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Анамнез',
        'prop_path' => 'visit_description_anamnesis',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Предварительный диагноз',
        'prop_path' => 'visit_description_pre_diagnosis',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Клинические признаки',
        'prop_path' => 'visit_description_clinical_signs',
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
        'label' => 'Лечебная помощь',
        'prop_path' => 'medicalAssistance',
        'prop_type' => 'string',
        'param_type' => 'gov_services_com_class_journal',
    ],
    [
        'label' => 'Рекомендации',
        'prop_path' => 'visit_description_recommendations',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Заключительный диагноз',
        'prop_path' => 'visit_description_final_diagnosis',
        'prop_type' => 'string',
        'param_type' => 'visit_description',
    ],
    [
        'label' => 'Номер квитанции',
        'prop_path' => 'bill_num',
        'prop_type' => 'string',
        'param_type' => 'visit_price_bill_num',
    ],
    [
        'label' => 'Услуги приема и их стоимость',
        'prop_path' => 'services_with_discount',
        'prop_type' => 'string',
        'param_type' => 'visit_services_with_discount',
    ],
    [
        'label' => 'Общая стоимость оказанных ветеринарных услуг',
        'prop_path' => 'price_with_discount',
        'prop_type' => 'string',
        'param_type' => 'visit_price_price_with_discount',
    ],
    [
        'label' => 'При оказании вет.услуг применяемые лекарственные средства (иммунобиологические препараты), представленные владельцем животного',
        'prop_path' => 'owner_drugs_list',
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
];
