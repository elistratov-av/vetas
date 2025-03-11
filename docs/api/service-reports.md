FORMAT: 1A
HOST: http://dev-api.pet.altarix.org/v1/


# service-reports

Методы по работе с отчетами об оказанных в рамках визита услугах.


## service-reports [/service-reports]


## Получение полей отчета по оказанной услуге [GET /service-reports/{id}]
<a name="def-get-service-reports-id"></a>

Возвращает поля отчета по оказанной в рамках визита услуге:
- в случае, если отчет был создан, возвращается набор полей с ранее заполненными ранее значениями
- в случае создания нового отчета, возвращается набор полей, на основе которых можно создать новый отчет


Поля типа "visit_param_value" не являются редактируемыми (в документации по отчетам в confluence имеют тип label).

Поля типа "visit_service_param_value" являются редактируемыми, их значения должны отправляться методами POST или PUT, в зависимости от сценария.


**Пример:**

```
GET /service-reports/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор услуги в рамках визита (id_visitservice)


+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...


+ Response 200 (application/json)

    + Attributes

        - data
            - id_visitservice: 1 (number)
            - id_service: 164 (number)
            - id_visit: 1 (number)
            - data (array)
                - (Fields Full VisitParam)
                - (Fields Full VisitParam)
                - (Fields Full)
                - (Fields Full)
        - links
            - self
                - href: http://api.pet.altarix.org/v1/service-reports/15


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Услуга с указанным ID не найдена.",
                        "detail": ""
                    }
                ]
            }



## Сохранение данных для отчета [POST /service-reports/{id}]
<a name="def-post-service-reports"></a>

Создает отчет (происходит сохранение полей отчета в виде сущностей VisitParamValue).


**Пример:**

```
POST /service-reports/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор услуги в рамках визита (id_visitservice)

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data (array)
            - (Fields Short Post)
            - (Fields Short Post)
            - (Fields Short Post)



+ Response 200 (application/json)

    + Attributes

        - data (array)
            - (Fields Full)
            - (Fields Full)
            - (Fields Full)
        - links
            - self
                - href: http://api.pet.altarix.org/v1/service-reports/15


+ Response 400 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Формат запроса не соответствует спецификации JSON API",
                        "detail": ""
                    }
                ]
            }


+ Response 409 (application/json)

    + Body

            {
                "errors": [
                    {
                      "code": "number",
                      "title": "Текст ошибки в соответствии с правилами валидации конкретного поля.",
                      "detail": ""
                    }
                ]
            }


+ Response 422

    + Body

            {
                "errors": [
                    {
                      "code": "number",
                      "title": "Текст ошибки в соответствии с правилами валидации конкретного поля.",
                      "detail": ""
                    }
                ]
            }



## Редактирование данных по отчету [PUT /service-reports/{id}]
<a name="def-put-service-reports-id"></a>

Изменяет отчет (происходит обновление полей отчета в виде сущностей VisitParamValue).

**Пример:**

```
PUT /service-reports/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор услуги в рамках визита (id_visitservice)

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data (array)
            - (Fields Short)
            - (Fields Short)
            - (Fields Short)


+ Response 200 (application/json)

    + Attributes

        - data (array)
            - (Fields Full)
            - (Fields Full)
            - (Fields Full)
        - links
            - self
                - href: http://api.pet.altarix.org/v1/service-reports/15

+ Response 400 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Формат запроса не соответствует спецификации JSON API",
                        "detail": ""
                    }
                ]
            }

+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Услуга с указанным ID не найдена.",
                        "detail": ""
                    }
                ]
            }

+ Response 409 (application/json)

    + Body

            {
                "errors": [
                    {
                      "code": "number",
                      "title": "Текст ошибки в соответствии с правилами валидации конкретного поля.",
                      "detail": ""
                    }
                ]
            }


+ Response 422

    + Body

            {
                "errors": [
                    {
                      "code": "number",
                      "title": "Текст ошибки в соответствии с правилами валидации конкретного поля.",
                      "detail": ""
                    }
                ]
            }


## Получение отчета по оказанной услуге в формате PDF [GET /service-reports/{id}/files]
<a name="def-get-service-reports-id-files"></a>

Возвращает FileResource для PDF-отчета по оказанной в рамках визита услуге.
В случае, если файл отчета не был ранее сгенерирован, он генерируется.


**Пример:**

```
GET /service-reports/15/files
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор услуги в рамках визита (id_visitservice)


+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...


+ Response 200 (application/json)

    + Body

            {
                "data": {
                    "id": "75",
                    "type": "file",
                    "attributes": {
                        "path": "/upload/visits_gov_service/7/7af9124c506b4b73e058d37d5f2f7f8246b67798.pdf",
                        "name": "7af9124c506b4b73e058d37d5f2f7f8246b67798.pdf",
                        "created": "2018-08-01 14:34:22",
                        "entity-id": 7,
                        "entity-type": "visits_gov_service"
                    },
                    "links": {
                        "self": {
                            "href": "http://vetais-api.lo/v1/files/75"
                        },
                        "related": {
                            "href": "http://vetais-api.lo/upload/visits_gov_service/7/7af9124c506b4b73e058d37d5f2f7f8246b67798.pdf"
                        }
                    }
                }
            }


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Услуга с указанным ID не найдена.",
                        "detail": ""
                    }
                ]
            }


## Получение списка услуг для визита, для которых доступно формирование отчета [GET /service-reports/available/{id}]
<a name="def-get-service-reports-available-id"></a>

Возвращает список услуг для визита, для которых доступно формирование отчета.


**Пример:**

```
GET /service-reports/available/1
```


+ Parameters

    + id: `1` (integer, required) - Уникальный идентификатор **визита**


+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...


+ Response 200 (application/json)

    + Attributes

        - data (array)
            - (object)
                - id_visitservice: 1 (number)
                - id_service: 164 (number)
            - (object)
                - id_visitservice: 2 (number)
                - id_service: 117 (number)
            - (object)
                - id_visitservice: 3 (number)
                - id_service: 162 (number)



## Получение списка услуг для визита, для которых доступно формирование отчета в PDF [GET /service-reports/available-pdf/{id}]
<a name="def-get-service-reports-available-pdf-id"></a>

Возвращает список услуг для визита, для которых доступно формирование отчета в PDF.


**Пример:**

```
GET /service-reports/available-pdf/1
```


+ Parameters

    + id: `1` (integer, required) - Уникальный идентификатор **визита**


+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...


+ Response 200 (application/json)

    + Attributes

        - data (array)
            - (object)
                - id_visitservice: 1 (number)
                - id_service: 164 (number)
            - (object)
                - id_visitservice: 2 (number)
                - id_service: 117 (number)
            - (object)
                - id_visitservice: 3 (number)
                - id_service: 162 (number)


# Data Structures


## Fields Attributes (object)

- id_param: 5 (number) - ID параметра - сущности Param
- tech_name: P60_SpecialistFIO (string) - Служебное наименование
- name: Ф.И.О. врача (string) - Наименование поля для отчета
- value: Иванов Иван Иванович - Значение поля - предзаполнено в случае, если поле является редактируемым или если выводится ранее сделанный отчет
- datatype: text (string) - Тип данных  -numeric, text, dttm, dict
- config (object) - дополнительная конфигурация, условия для рендеринга поля и т.п.
    - editable: true (boolean) - Является ли поле редактируемым
    - active: false (boolean) - Является ли поле активным в текущем сценарии - для редактируемых полей
    - format: d-m-Y (string) - формат
    - min: 100 (number) - инимальная длина
    - max: 255 (number) - максимальная длина
    - link: organizations (string) - ссылка для получения коллекции, если поле является справочником


## Fields Attributes VisitParam (object)

- id_param: 5 (number) - ID параметра - сущности Param
- tech_name: P60_SpecialistFIO (string) - Служебное наименование
- name: Ф.И.О. врача (string) - Наименование поля для отчета
- value: Иванов Иван Иванович - Значение поля - предзаполнено в случае, если поле является редактируемым или если выводится ранее сделанный отчет
- datatype: text (string) - Тип данных  -numeric, text, dttm, dict
- config (object) - дополнительная конфигурация, условия для рендеринга поля и т.п.
    - editable: false (boolean) - Является ли поле редактируемым


## Fields Full VisitParam (object)

- type: visit_param_value (string)
- id: 15 (number) - ID значения параметра - сущности VisitParamValue - возвращается в случае, если выводится ранее сделанный отчет
- attributes (Fields Attributes VisitParam)


## Fields Full (object)

- type: visit_service_param_value (string)
- id: 15 (number) - ID значения параметра - сущности VisitServiceParamValue - возвращается в случае, если выводится ранее сделанный отчет
- attributes (Fields Attributes)


## Fields Short Post (object)

- type: visit_service_param_value (string)
- attributes
    - id: null - имеет значение null или может быть опущено при создании нового отчета
    - id_param: 5 (number) - ID параметра - сущности Param
    - value: Иванов Иван Иванович - Значение поля - предзаполнено в случае, если поле является редактируемым или если выводится ранее сделанный отчет

## Fields Short (object)

- type: visit_service_param_value (string)
- attributes
    - id: 15 (number) - ID значения параметра - сущности VisitServiceParamValue - возвращается в случае, если выводится ранее сделанный отчет
    - id_param: 5 (number) - ID параметра - сущности Param
    - value: Иванов Иван Иванович - Значение поля - предзаполнено в случае, если поле является редактируемым или если выводится ранее сделанный отчет
