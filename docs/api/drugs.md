FORMAT: 1A
HOST: http://dev-api.pet.altarix.org/v1/


# drugs

Ресурс "Препарат".

## Ресурс [/drugs]
<a name="def-drugs"></a>

### 'GET /drugs' [GET /drugs]
<a name="def-get-drugs"></a>

Возвращает коллекцию ресурсов "Препарат". Использует механизм [фильтрации](https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=91278335).

**Примеры:**

```

GET /drugs

Фильтрация текстового поля по частичному совпадению без учета регистра:
  GET /drugs?filter={"and":[{"name":{"like":"some_text"}}]}

Фильтрация текстового поля по частичному совпадению с учетом регистра:
  GET /drugs?filter={"and":[{"name":{"like!":"some_text"}}]}

Фильтрация текстового поля по точному совпадению:
  GET /drugs?filter={"and":[{"name":{"eq":"some_text"}}]}

Сортировка
  GET /drugs?sort=name //Sort order ASC.
  GET /drugs?sort=-name////Sort order DESC.

  Атрибуты: любые.

Пагинация:
  GET /drugs?page[offset]=14 //Default limit: 10.
  GET /drugs?page[limit]=1  //Default offset: 0.

```

+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 200 (application/json)

    + Attributes

        - meta
            - total: 300 (number)
            - limit: 1 (number)
            - offset: 14 (number)
        - data
            - data (array)
                - (Drugs Full)
                - (Drugs Full)
                - (Drugs Full)
        - links
            - self: `http://dev-api.pet.altarix.org/v1/drugs?page[offset]=14&page[limit]=1`
            - first: `http://dev-api.pet.altarix.org/v1/drugs?page[offset]=0&page[limit]=1`
            - prev: `http://dev-api.pet.altarix.org/v1/drugs?page[offset]=13&page[limit]=1`
            - next: `http://dev-api.pet.altarix.org/v1/drugs?page[offset]=15&page[limit]=1`
            - last: `http://dev-api.pet.altarix.org/v1/drugs?page[offset]=299&page[limit]=1`


### 'GET /drugs/{id}' [GET /drugs/{id}]
<a name="def-get-drugs-id"></a>

Возвращает экземпляр ресурса "Препарат".


**Пример:**

```
GET /drugs/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса


+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 200 (application/json)

    + Attributes

        - data (Drugs Full)


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Препарат с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }



### 'POST /drugs' [POST /drugs]
<a name="def-post-drugs"></a>

Создает экземпляр ресурса "Препарат".

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data

            - type: drug (string)
            - attributes (Drugs Attributes)



+ Response 200 (application/json)

    + Attributes

        - data (Drugs Full)


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
                      "code": "304",
                      "source": { "pointer": "/data/attributes/name" },
                      "title": "Препарат с таким названием уже существует.",
                      "detail": ""
                    },
                    {
                      "code": "305",
                      "source": { "pointer": "/data/attributes/id_tmc_type" },
                      "title": "Неизвестный тип ТМЦ.",
                      "detail": ""
                    },
                    {
                      "code": "321",
                      "source": { "pointer": "/data/attributes/id_registered" },
                      "title": "Неизвестный владелец регистрационного удостоверения.",
                      "detail": ""
                    },
                    {
                      "code": "306",
                      "source": { "pointer": "/data/attributes/id_manufactured" },
                      "title": "Неизвестный Производитель.",
                      "detail": ""
                    },
                    {
                      "code": "307",
                      "source": { "pointer": "/data/attributes/id_representation" },
                      "title": "Неизвестный Представитель.",
                      "detail": ""
                    },
                    {
                      "code": "308",
                      "source": { "pointer": "/data/attributes/id_measure" },
                      "title": "Неизвестная Единица Измерения.",
                      "detail": ""
                    },
                    {
                      "code": "309",
                      "source": { "pointer": "/data/attributes/id_active_substance" },
                      "title": "Неизвестное активное вещество.",
                      "detail": ""
                    },
                    {
                      "code": "308",
                      "source": { "pointer": "/data/attributes/id_active_substance_measure" },
                      "title": "Неизвестная Единица Измерения.",
                      "detail": ""
                    },
                    {
                      "code": "310",
                      "source": { "pointer": "/data/attributes/id_file_packaging_image" },
                      "title": "Неизвестный файл.",
                      "detail": ""
                    }
                ]
            }


+ Response 422

    + Body

            {
                "errors": [
                    {
                        "code": "311",
                        "title": "Отсутствует обязательный параметр \"Название\".",
                        "detail": ""
                    },
                    {
                        "code": "322",
                        "title": "Отсутствует обязательный параметр \"Владелец регистрационного удостоверения\".",
                        "detail": ""
                    },
                    {
                        "code": "312",
                        "title": "Отсутствует обязательный параметр \"Производитель\".",
                        "detail": ""
                    },
                    {
                        "code": "313",
                        "title": "Отсутствует обязательный параметр \"Представитель\".",
                        "detail": ""
                    },
                    {
                        "code": "314",
                        "title": "Отсутствует обязательный параметр \"Лекарственная форма\".",
                        "detail": ""
                    },
                    {
                        "code": "317",
                        "title": "Отсутствует обязательный параметр \"Тип ТМС\".",
                        "detail": ""
                    }
                ]
            }



### 'PUT /drugs/{id}' [PUT /drugs/{id}]
<a name="def-put-drugs-id"></a>

Изменяет экземпляр ресурса "Препарат".

**Пример:**

```
PUT /drugs/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data

            - type: drug (string)
            - attributes (Drugs Attributes)


+ Response 200 (application/json)

    + Attributes

        - data (Drugs Full)

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
                        "title": "Препарат с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }

+ Response 409 (application/json)

    + Body

            {
                "errors": [
                    {
                      "code": "304",
                      "source": { "pointer": "/data/attributes/name" },
                      "title": "Препарат с таким названием уже существует.",
                      "detail": ""
                    },
                    {
                      "code": "305",
                      "source": { "pointer": "/data/attributes/id_tmc_type" },
                      "title": "Неизвестный тип ТМЦ.",
                      "detail": ""
                    },
                    {
                      "code": "321",
                      "source": { "pointer": "/data/attributes/id_registered" },
                      "title": "Неизвестный владелец регистрационного удостоверения.",
                      "detail": ""
                    },
                    {
                      "code": "306",
                      "source": { "pointer": "/data/attributes/id_manufactured" },
                      "title": "Неизвестный Производитель.",
                      "detail": ""
                    },
                    {
                      "code": "307",
                      "source": { "pointer": "/data/attributes/id_representation" },
                      "title": "Неизвестный Представитель.",
                      "detail": ""
                    },
                    {
                      "code": "308",
                      "source": { "pointer": "/data/attributes/id_measure" },
                      "title": "Неизвестная Единица Измерения.",
                      "detail": ""
                    },
                    {
                      "code": "309",
                      "source": { "pointer": "/data/attributes/id_active_substance" },
                      "title": "Неизвестное активное вещество.",
                      "detail": ""
                    },
                    {
                      "code": "308",
                      "source": { "pointer": "/data/attributes/id_active_substance_measure" },
                      "title": "Неизвестная Единица Измерения.",
                      "detail": ""
                    },
                    {
                      "code": "310",
                      "source": { "pointer": "/data/attributes/id_file_packaging_image" },
                      "title": "Неизвестный файл.",
                      "detail": ""
                    }
                ]
            }



### 'DELETE /drugs/{id}' [DELETE /drugs/{id}]
<a name="def-delete-drugs-id"></a>

Удаляет экземпляр ресурса "Препарат".

**Пример:**

```
DELETE /drugs/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 204


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Препарат с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }




# Group Связанные ресурсы
<a name="def-drugs-relationships"></a>


## content-of-active-substances [/drugs/{id}/relationships/content-of-active-substances]
<a name="def-drugs-relationships-content-of-active-substances"></a>


### 'GET /drugs/{id}/relationships/content-of-active-substances' [GET /drugs/{id}/relationships/content-of-active-substances]
<a name="def-get-drugs-id-relationships-content-of-active-substances"></a>

Возвращает коллекцию ресурсов "Содержание активных веществ" для конкретного препарата.

[См. content_of_active_substances](content_of_active_substances.md#def_get_content_of_active_substances)


**Пример:**

```
GET /drugs/15/relationships/content-of-active-substance
```

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса



+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 200 (application/json)

        {...}



+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Препарат с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }



### 'POST /drugs/{id}/content-of-active-substances' [POST /drugs/{id}/content-of-active-substances]
<a name="def-post-drugs-id-content-of-active-substances"></a>

Создает экземпляр ресурса "Содержание активных веществ" связанного с конкретным препаратом.

[См. content_of_active_substances](content_of_active_substances.md#def_get_content_of_active_substances)

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data

            - type: content_of_active_substance (string)
            - attributes (Content Of Active Substance Attributes)

+ Response 200 (application/json)

        {...}


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
                        "code": "504",
                        "source": { "pointer": "/data/attributes/id_active_substance" },
                        "title": "Неизвестное активное вещество.",
                        "detail": ""
                    },
                    {
                        "code": "505",
                        "source": { "pointer": "/data/attributes/id_measure" },
                        "title": "Неизвестная Единица Измерения.",
                        "detail": ""
                    }
                ]
            }

+ Response 422 (application/json)

    + Body

            {
                "errors": [
                    {
                        "code": "501",
                        "title": "Отсутствует обязательный параметр \"Активное вещество\".",
                        "detail": ""
                    },
                    {
                        "code": "502",
                        "title": "Отсутствует обязательный параметр \"Единица измерения\".",
                        "detail": ""
                    },
                    {
                        "code": "503",
                        "title": "Отсутствует обязательный параметр \"Количество\".",
                        "detail": ""
                    }
                ]
            }



### 'DELETE /drugs/{id}/content-of-active-substances/{id}' [DELETE /drugs/{id}/content-of-active-substances/{id}]
<a name="def-delete-drugs-id-content-of-active-substances"></a>

Удаляет экземпляр ресурса "Содержание активных веществ" связанного с конкретным препаратом.

**Пример:**

```
DELETE /drugs/15/content-of-active-substances/36
```

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 204


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Препарат с указанным ID не найден.",
                        "detail": ""
                    },
                    {
                        "title": "Содержимое активного вещества с указанным ID не найдено.",
                        "detail": ""
                    }
                ]
            }




## descriptions [/drugs/{id}/relationships/descriptions]
<a name="def-drugs-relationships-descriptions"></a>


### 'GET /drugs/{id}/relationships/descriptions' [GET /drugs/{id}/relationships/descriptions]
<a name="def-get-drugs-id-relationships-descriptions"></a>

Возвращает коллекцию ресурсов "Описание" для конкретного препарата.

Необходимо выбирать из модели descriptions описания для entity_type  drug.

[См. descriptions](descriptions.md#def_get_descriptions)

Пример:

```
GET /drugs/15/relationships/descriptions
```

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса


+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 200 (application/json)

    + Body

            {...}

+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Препарат с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }



### 'POST /drugs/{id}/descriptions' [POST /drugs/{id}/descriptions]
<a name="def-post-drugs-id-descriptions"></a>

Создает экземпляр ресурса "Описание" связанного с конкретным препаратом. Атрибуту entity_type устанавливается значение drug.

[См. descriptions](descriptions.md#def_get_descriptions)

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data

            - type: description (string)
            - attributes (Description Attributes)

+ Response 200 (application/json)

        {...}


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
                        "code": "318",
                        "source": { "pointer": "/data/attributes/id_description_type" },
                        "title": "Неверный тип описания для препарата.",
                        "detail": ""
                    }
                ]
            }

+ Response 422 (application/json)

    + Body

            {
                "errors": [
                    {
                        "code": "319",
                        "title": "Отсутствует обязательный параметр \"Тип описания\".",
                        "detail": ""
                    },
                    {
                        "code": "320",
                        "title": "Отсутствует обязательный параметр \"Описание\".",
                        "detail": ""
                    }
                ]
            }


### 'DELETE /drugs/{id}/descriptions/{id}' [DELETE /drugs/{id}/descriptions/{id}]
<a name="def-delete-drugs-id-descriptions"></a>

Удаляет экземпляр ресурса "Описание" связанного с конкретным препаратом.

**Пример:**

```
DELETE /drugs/15/descriptions/36
```

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 204


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Препарат с указанным ID не найден.",
                        "detail": ""
                    },
                    {
                        "title": "Описание с указанным ID не найдено.",
                        "detail": ""
                    }
                ]
            }




## files [/drugs/{id}/files]
<a name="def-drugs-files"></a>

### 'POST /drugs/{id}/files/{file_id}' [POST /drugs/{id}/files/{file_id}]
<a name="def-post-drugs-id-files"></a>

Создает связь ранее загруженного файла с конкретным препаратом.
Сервер должен переместить файл в директорию для данного препарата, обновить путь к файлу в ресурсе "Файл" и создать запись в таблице использования файлов.

[См. files](files.md#def_get_files)

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса
    + file_id: `38` (integer, required) - Уникальный идентификатор файла

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...


+ Response 200 (application/json)

        {...}


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
                        "title": "Файл с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }


### 'DELETE /drugs/{id}/files/{file_id}' [DELETE /drugs/{id}/files/{file_id}]
<a name="def-delete-drugs-id-files"></a>

Удаляет связь файла с конкретным препаратом. Сервер должен удалить запись из таблицы использования файлов.

**Пример:**

```
DELETE /drugs/15/files/36
```

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса
    + file_id: `38` (integer, required) - Уникальный идентификатор файла

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 204


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Файл с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }


# Data Structures


## Drugs Attributes (object)

- name: Название препарата (string)
- id_tmc_type: 55 (number) - Ссылка на справочник Типов ТМЦ.
- id_registered: 67 (number) - Ссылка на владельца регистрационного удостоверения. Справочник фармакологических организаций.
- id_manufactured: 23 (number) - Ссылка на производителя. Справочник фармакологических организаций.
- id_representation: 31 (number) - Ссылка на представителя. Справочник фармакологических организаций.
- form: Лекарственная форма (string),
- form_description: Описание лекарственной формы (string),
- unit: 547 (number),
- id_measure: 68 (number) - Ссылка на справочник единиц измерений.
- id_active_substance: 19 (number) - Ссылка на справочник Активных веществ.
- active_substance_unit: 63 (number),
- id_active_substance_measure: 36 (number) - Ссылка на справочник единиц измерений.
- excipients: Вспомогательные вещества. (string),
- packaging: Описание упаковки (string),
- basis: Основание описания препарата (string).


## Drugs Relationships (object)

- tmc_type
    - data
        - type: tmc_type (string)
        - id: 55 (number)
- drug_org
    - data (array)
        - (object)
            - type: drug_org (string)
            - id: 67 (number)
        - (object)
            - type: drug_org (string)
            - id: 23 (number)
        - (object)
            - type: drug_org (string)
            - id: 31 (number)
- measure
    - data
        - type: measure (string)
        - id: 68 (number)
- content_of_active_substance
    - data (array)
        - (object)
            - type: content_of_active_substance (string)
            - id: 301 (number)
        - (object)
            - type: content_of_active_substance (string)
            - id: 302 (number)
        - (object)
            - type: content_of_active_substance (string)
            - id: 303 (number)
    - links
        - self: http://dev-api.pet.altarix.org/v1/drugs/15/relationships/content-of-active-substances (string)
        - related: http://dev-api.pet.altarix.org/v1/drugs/15/content-of-active-substances (string)
- description
    - data (array)
        - (object)
            - type: description (string)
            - id: 301 (number)
        - (object)
            - type: description (string)
            - id: 302 (number)
        - (object)
            - type: description (string)
            - id: 303 (number)
- file
    - data
        - type: file (string)
        - id: 111 (number)



## Drugs Full (object)

- type: drug (string)
- id: 15 (string)
- attributes (Drugs Attributes)
- relationships (Drugs Relationships)
- links
    - self
        - href: `http://dev-api.pet.altarix.org/v1/drugs/15`


## Content Of Active Substance Attributes

- id_active_substance: 19 (number, required) - Ссылка на справочник Активных веществ.
- id_measure: 68 (number, required) - Ссылка на справочник единиц измерений.
- unit: 547 (number, required) - Количество активного вещества.

## Description Attributes

- id_description_type: 20 (number, required) - Ссылка на тип описания
- description: Текст описания... (string, required)
