
#Доступность полей у разных типов ТМЦ

| drugs             | vaccines           | exp_materials         | equipments          |
| --- | --- |--- | --- |
| id                | id                 | id                    | id                  |
| type              | type               | type                  | type                |
| name              | name               | name                  | name                |
| basis             | -                  | -                     | -                   |
| -                 | -                  | description           | description         |
| dealer            | dealer             | -                     | -                   |
| excipients        | excipients         | -                     | -                   |
| form_description  | form_description   | -                     | -                   |
| id_measure (req)  | id_measure (req)   | id_measure (req)      | -                   |
| packaging         | packaging          | -                     | -                   |
| produced (req)    | produced (req)     | -                     | -                   |
| registered (req)  | registered (req)   | -                     | -                   |
| unit              | unit               | -                     | -                   |
| -                 | -                  | is_uncountable        | -                   |
|                   |                    |                       |                     |
| is_deleted        | is_deleted         | is_deleted            | is_deleted          | 
| updated_at        | updated_at         | updated_at            | updated_at          |
| updated_by        | updated_by         | updated_by            | updated_by          |
| created_at        | created_at         | created_at            | created_at          |
| created_by        | created_by         | created_by            | created_by          |

#Связи

| drugs             | vaccines           | exp_materials         | equipments          |
| --- | --- |--- | --- |
| category_to_tmc   | category_to_tmc    | category_to_tmc       | -                   |
| dosages           | dosages            | -                     | -                   |
| measures          | measures           | measures              | -                   |
| tmc_to_species    | tmc_to_species     | -                     | -                   |
| tmc_to_diseases   | tmc_to_diseases    | -                     | -                   |
| production_form   | production_form    | production_form                     | -                   |

#Баланс

| drugs             | vaccines          | exp_materials     | equipments        |
| --- | --- |--- | --- |
| id                | id                | id                | id                |
| id_tmc            | id_tmc            | id_tmc            | id_tmc            |
| type_tmc          | type_tmc          | type_tmc          | type_tmc          |
| id_organization   | id_organization   | id_organization   | id_organization   |
| id_specialist     | id_specialist     | id_specialist     | id_specialist     |
| id_production_form|id_production_form | id_production_form| -                 |
| count (ro)        | count (ro)        | count (ro)        | -                 |
| expiration_date   | expiration_date   | expiration_date   | -                 |
| registration_date | registration_date | registration_date | registration_date |
| inventory_number  | inventory_number  | inventory_number  | inventory_number  |
| price             | price             | price             | -                 |
| -                 | -                 | -                 |equipment_condition|
| -                 | -                 | -                 |manufactured_number|
| count_in_production_form (ro) |count_in_production_form (ro)|count_in_production_form (ro)| - |
| created_at        | created_at        | created_at        | created_at        |
| created_by        | created_by        | created_by        | created_by        |
| updated_at        | updated_at        | updated_at        | updated_at        |
| updated_by        | updated_by        | updated_by        | updated_by        |