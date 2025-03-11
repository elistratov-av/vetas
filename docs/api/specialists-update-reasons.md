FORMAT: 1A
HOST: http://dev-api.pet.altarix.org/v1/


# specialists-update-reasons

Ресурс "Основание внесения изменений".

## Ресурс [/specialists-update-reasons]
<a name="def-specialists-update-reasons"></a>

### 'GET /specialists-update-reasons' [GET /specialists-update-reasons]
<a name="def-get-specialists-update-reasons"></a>

Возвращает коллекцию ресурсов "Основание внесения изменений".

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
                - (Specialists-Update-Reasons Full)
                - (Specialists-Update-Reasons Full)
                - (Specialists-Update-Reasons Full)
        - links
            - self: `http://dev-api.pet.altarix.org/v1/specialists-update-reasons?page[offset]=14&page[limit]=1`
            - first: `http://dev-api.pet.altarix.org/v1/specialists-update-reasons?page[offset]=0&page[limit]=1`
            - prev: `http://dev-api.pet.altarix.org/v1/specialists-update-reasons?page[offset]=13&page[limit]=1`
            - next: `http://dev-api.pet.altarix.org/v1/specialists-update-reasons?page[offset]=15&page[limit]=1`
            - last: `http://dev-api.pet.altarix.org/v1/specialists-update-reasons?page[offset]=299&page[limit]=1`


### 'GET /specialists-update-reasons/{id}' [GET /specialists-update-reasons/{id}]
<a name="def-get-specialists-update-reasons-id"></a>

Возвращает экземпляр ресурса "Основание внесения изменений".


**Пример:**

```
GET /specialists-update-reasons/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса


+ Request

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

+ Response 200 (application/json)

    + Attributes

        - data (Specialists-Update-Reasons Full)


+ Response 404 (application/json)

    + Body

            {
                "errors": [
                    {
                        "title": "Документ с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }



### 'POST /specialists-update-reasons' [POST /specialists-update-reasons]
<a name="def-post-specialists-update-reasons"></a>

Создает экземпляр ресурса "Основание внесения изменений".

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data
            - type: specialists_update_reason (string)
            - attributes (Specialists-Update-Reasons Attributes Post)



+ Response 200 (application/json)

    + Attributes

        - data (Specialists-Update-Reasons Full)


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
                      "code": "311",
                      "source": { "pointer": "/data/attributes/id_specialist" },
                      "title": "Неизвестный специалист.",
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
                        "title": "Отсутствует обязательный параметр \"Специалист\".",
                        "detail": ""
                    },
                    {
                        "code": "322",
                        "title": "Отсутствует обязательный параметр \"Причина\".",
                        "detail": ""
                    }
                ]
            }



### 'PUT /specialists-update-reasons/{id}' [PUT /specialists-update-reasons/{id}]
<a name="def-put-specialists-update-reasons-id"></a>

Изменяет экземпляр ресурса "Основание внесения изменений".

**Пример:**

```
PUT /specialists-update-reasons/15
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса

+ Request (application/json)

    + Headers

            Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

    + Attributes

        - data
            - type: specialists_update_reason (string)
            - attributes (Specialists-Update-Reasons Attributes Post)


+ Response 200 (application/json)

    + Attributes

        - data (Specialists-Update-Reasons Full)

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
                        "title": "Документ с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }

+ Response 409 (application/json)

    + Body

            {
                "errors": [
                     {
                      "code": "311",
                      "source": { "pointer": "/data/attributes/id_specialist" },
                      "title": "Неизвестный специалист.",
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
                        "title": "Отсутствует обязательный параметр \"Специалист\".",
                        "detail": ""
                    },
                    {
                        "code": "322",
                        "title": "Отсутствует обязательный параметр \"Причина\".",
                        "detail": ""
                    }
                ]
            }



### 'DELETE /specialists-update-reasons/{id}' [DELETE /specialists-update-reasons/{id}]
<a name="def-delete-specialists-update-reasons-id"></a>

Удаляет экземпляр ресурса "Основание внесения изменений".

**Пример:**

```
DELETE /specialists-update-reasons/15
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
                        "title": "Документ с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }




# Group Связанные ресурсы
<a name="def-specialists-update-reasons-relationships"></a>


## specialists [/specialists-update-reasons/{id}/relationships/specialists]
<a name="def-specialists-update-reasons-relationships-specialists"></a>


### 'GET /specialists-update-reasons/{id}/relationships/specialists' [GET /specialists-update-reasons/{id}/relationships/specialists]
<a name="def-get-specialists-update-reasons-id-relationships-specialists"></a>

Возвращает связанный ресурс "Специалист" для конкретного документа "Основание внесения изменений".

[См. specialists](specialists.md#def_get_specialists)


**Пример:**

```
GET /specialists-update-reasons/15/relationships/specialists
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
                        "title": "Документ с указанным ID не найден.",
                        "detail": ""
                    }
                ]
            }


## files [/specialists-update-reasons/{id}/files]
<a name="def-specialists-update-reasons-files"></a>

Файл, прикрепляемый к документу "Основание внесения изменений", загружается с использованием стандартного механизма, описанного для ресурса

[files](https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=91274209#id-/files-POST/files)

```
POST /files
```

В результате загрузки файла в ответе будет получен ресурс типа 'file', который нужно будет связать с документом после его сохранения.


### 'POST /specialists-update-reasons/{id}/files/{id_file}' [POST /specialists-update-reasons/{id}/files/{id_file}]
<a name="def-post-specialists-update-reasons-id-files"></a>

Создает связь ранее загруженного файла с конкретным ресурсом.
Сервер должен переместить файл в директорию для данного препарата, обновить путь к файлу в ресурсе "Файл" и создать запись в таблице использования файлов.

[См. files](files.md#def_get_files)

**Пример:**

```
POST /specialists-update-reasons/15/files/111
```


+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса
    + id_file: `111` (integer, required) - Уникальный идентификатор файла

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


### 'DELETE /specialists-update-reasons/{id}/files/{id_file}' [DELETE /specialists-update-reasons/{id}/files/{id_file}]
<a name="def-delete-specialists-update-reasons-id-files"></a>

Удаляет связь файла с конкретным ресурсом. Сервер должен удалить запись из таблицы использования файлов.

**Пример:**

```
DELETE /specialists-update-reasons/15/files/111
```

+ Parameters

    + id: `15` (integer, required) - Уникальный идентификатор ресурса
    + id_file: `111` (integer, required) - Уникальный идентификатор файла

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


## `Specialists-Update-Reasons Attributes Post` (object)

- id_specialist: 55 (number) - Ссылка на справочник специалистов.
- description: Длинный текст... (string) - Причина внесения изменений


## `Specialists-Update-Reasons Attributes Get` (object)

- id_specialist: 55 (number) - Ссылка на экземпляр ресурса Специалист
- description: Длинный текст... (string) - Причина внесения изменений
- created_date: `2018-07-31` (string) - Дата внесения изменений
- created_user: 12 (number) - Ссылка на пользователя, внесшего изменения
- created_username: Иванов Иван Иванович (string) - ФИО пользователя, внесшего изменения


## `Specialists-Update-Reasons Full` (object)

- type: specialists_update_reason (string)
- id: 15 (string)
- attributes (Specialists-Update-Reasons Attributes Get)
- relationships (Specialists-Update-Reasons Relationships)
- links
    - self
        - href: `http://dev-api.pet.altarix.org/v1/specialists-update-reasons/15`


## `Specialists-Update-Reasons Relationships` (object)

- specialist
    - data
        - type: specialist (string)
        - id: 55 (number)
- file
    - data
        - type: file (string)
        - id: 111 (number)

