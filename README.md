# ВетАС (API backend)
## Общее описание проекта
**Система предназначена для автоматизации ветеринарных организаций в части:**
- записи на прием;
- учета предоставленных услуг;
- противоэпизоотических мероприятий;
- мониторинга деятельности ветеринарных организаций и т.д.

Код в данном репозитории обеспечивает функциональность, необходимую преимущественно для REST API системы.

При разработке используется php-фреймворк **Yii2**.
Структура каталогов частично основана на Yii2 Basic Project Template.
Разработка ведется с использованием системы модулей Yii2 и версионирования API. Наименование каталога с кодом API соответствует версии API.

### Структура
      commands/           консольные контроллеры
      common/             общие компоненты, модели и т.д.
      config/             файлы конфигурации
      controllers/        (в данный момент не используется)
      keys/               ключи для авторизации
      mail/               шаблоны email (в данный момент не используется)
      migrations/         файлы миграций базы данных
      modules/            модули
      adminfstek/         Панель управления ВетАС
          v1/             модуль API v1
          v2/             модуль API v2
          v3/             модуль API v3          
      runtime/            временные файлы (кэш, логи и т.п.)
      tests/              тесты
      vendor/             зависимость
      web/                здесь находится endpoint для API-запросов

## Установка
## Ручное развертывание

### Требования

    php            7.3.33
    postgresql     10.x
    nginx          1.x

### Клонирование репозитория:

```bash
git clone https://gitlab.starlink-soft.ru/vetas/backend.git
cd backend
```
**Внимание:**
Разработка ведется в ветка (номер_задачи)_dev. Изменения напрямую залить в main не получится!
### Установка зависимостей
Для установки зависимостей распакуйте папку vendor (backend/cicd/vendor.tar.gz) в корневую папку проекта
### Генерация файлов ключей
Генерируем закрытый ключ, команды выполнять в папке keys
```bash
openssl genrsa -out jwt_rsa 2048
```
Создаем сертификат x.509:
```bash
openssl req -new -x509 -key jwt_rsa -out jwt_rsa.cer -days 365
```
Экспортируем публичный ключ в формате PEM:
```bash
openssl x509 -pubkey -noout -in jwt_rsa.cer  > jwt_rsa.pem
```
### Переменные окружения
В корне проекте создайте копию файла .env.example. Подключение к базе используется с тестового стенда, данные в файлу example актуальны на момент (07.06.2024)

    DB_HOST=10.133.100.160 - рабочие данные
    DB_PORT=5432 - рабочие данные
    DB_NAME=api_prod - рабочие данные
    DB_USER=api_prod - рабочие данные
    DB_PASSWORD=api_prod - рабочие данные
    API_URL='https://backend.local' - URL, на котором у Вас будет расположен бекенд локально
    LOCAL_API_URL='https://backend.local' - внутренее серверное API, важно только на стендах и продах, локально оставляйте без изменений
    PRIVATE_KEY_PATH='C:/OSPanel/home/backend-master/keys/jwt_rsa' - полный путь до местонахождения приватного ключа, сгенерированным на предыдущем шаге
    PUBLIC_KEY_PATH='C:/OSPanel/home/backend-master/keys/jwt_rsa.pem'  - полный путь до местонахождения публичного ключа, сгенерированным на предыдущем шаге
    YII_DEBUG=true  - ставим true
    YII_ENV=dev  - ставим dev
    FIAS_URL=https://api-vetas.mos.ru/efp6-proxy/fias - куда обращаться за получением адреса

## Развертывание с помощью Docker
***Раздел не актуален***
### Установка системы (необходимый минимум для разработки):

1. Установить зависимости:
 - Docker v18.09+ 
 - Docker-compose v1.23+
 - Git
 - Make

2. Авторизуйте машину в gitlab registry
```bash
$ docker login registry.docker.altarix.org:5001
```

3. Выполните команды в рабочей директории
```bash
$ git clone git@git2.altarix.org:vetas/api-common.git ./
Ознакомится с настройками докер в файле docker/.env файл. При необходимости изменить настройки и поместить их в файл docker/.env.override
Особое внимание стоит уделить переменной VOLUMES_DIR в которой будут храниться тома докера. Это папка должна быть создана, до запуска команды установки.
Пример команд, для создания места хранения томов докера
$sudo mkdir -p /data/volumes
$sudo chown user:user -R /data/volumes
$sudo chmod 755 -R /data/volumes

Развертывание проекта:
$ make install
$ sudo make hosts
При первой установке происходит инициализация БД (backend при этом отдает 502 ошибку), это может занять время. Просмотр статуса загрузки дампа:
$ make logs-postgres
```

4. Приложение готово к работе, но в базе отсутствуют данные. Для создания дампа, необходимо на нужном контуре запустить команду:
```bash   
$ make dump-create file=/tmp/dump.gz
```
Загрузить локально полученный дамп с помощью команды:
```bash  
$ make dump-load file=/data/dump.gz
```

5. Задать реквизиты коммитера для репозитория: имя `git config user.name "Иванов Иван"` и почту `git config user.email "XXX@altarix.ru"`

После успешной установки будут доступны следующие сервисы:
 - http://backend.vetas:1190 - разрабатываемый сервис
 - http://adminer.backend.vetas:1190 - web gui для postgres
 - https://rabbitmq.backend.vetas:1190 - web gui для rabbitmq

Реквизиты для доступа к локальной postgres из других приложений:
```
host: backend.vetas
port: 1190
user: root
pass: test
db: vetas
```

## Авторизация при запросах к API
Для авторизации в заголовках запроса передается токен:
```
Authorization: Bearer zI1XhbGciOiJeAiOiJKV1...
```
Для получения токена, который будет использоваться в дальнейшем, необходимо отправить POST-запрос с логином и паролем пользователя:

```
POST /v2/user/user/token
Content-Type: application/json
{
    "login": "username",
    "password": "password"
}
```

## Деплой

### Общая информация
Процесс деплоя разбит на 2 этапа. Первый этап происходит непосредственно из папки cicd проекта. Происходит билд проекта, и передаче его на cicd проекта DevOps. 
 - **Сборка контейнера**<p>
    1. Копируется image с репозитория (стандартный, либо мос.ру, в запвисимости от переменной окружения)
    2. Авторизация в местном докер-репозитории
    3. Создание файла env, который будет использован в дальнейшем в бекенд
    4. Сборка Docker-image<p>
        4.1. php:7.3.33-buster - базовый образ из указанного репозитория<br>
        4.2. Копируются файлы проекта, в тч созданный .env<br>
        4.3. Распаковка архива vendor<br>
        4.4. Создание .htaccess для роутинга<br>
        4.5. Установка PHP-расширений<br>
        4.6. Настройка сервера<br>
    5. Передача сборки в backend<br>
 - **Развертывание контейнера**

### Таблица перменных серды для тестового стенда, предпрода, прода

| Стенд | Стенд | Предпрод | Прод | Комментарий |
| ---- | ---- | ---- | ---- | ---- |
| DOCKERHUB_URL | docker.io | dockerhub.mos.ru | dockerhub.mos.ru | Репозиторий, откуда качать образы |
| CI_REGISTRY_PASSWORD | yyWxxL9mvf1EyenxwKTk | hzNtRmQWrsbrDu5gEibp | NULL | (User) Preferences -> Access Tokens |
| DB_HOST | 10.133.100.160 | VETAS-DBS1T | NULL | Хост БД |
| DB_PORT | 5432 | 5432 | 5432 | Порт БД |
| DB_NAME | api_prod | api_prod | api_prod | Имя БД |
| DB_USER | api_prod | api_prod | api_prod | Юзер БД |
| DB_PASSWORD | api_prod | api_prod | NULL | Пароль БД |
| API_URL | https://api-vetas-prod.starlink-soft.ru | https://api-vetas-predprod.mos.ru/ | NULL | Адрес для запросов с фронта |
| LOCAL_API_URL | http://localhost | http://localhost | NULL | Адрес для запросов с бека |
| PUBLIC_KEY_PATH | /opt/keys/jwt_rsa.pem | /opt/keys/jwt_rsa.pem | NULL | Открытый ключ шифрования паролей |
| PRIVATE_KEY_PATH | /opt/keys/jwt_rsa | /opt/keys/jwt_rsa | NULL | Закрытый ключ шифрования паролей |
| YII_DEBUG | true | true | false | Флаг включения детального вывода ошибок |
| YII_ENV | dev | dev | prod | Окуржение для фреймворка |
| DEVOPS_TRIGGER | https://gitlab.starlink-soft.ru/api/v4/projects/12/trigger/pipeline | https://git.mos.ru/api/v4/projects/2720/trigger/pipeline | NULL | URL триггера для деплоя |
| DEPLOY_ENV | prod | uat | NULL | env в деплой |
| DEPLOY_REF | prod | master | NULL | Ветка деплоя |
| SUDIR_URL | https://sudir-test.mos.ru | https://sudir-test.mos.ru | https://sudir.mos.ru | URL Судира |
| SUDIR_CLIENT_ID | vetas-prod.starlink-soft.ru | vetas-prod.starlink-soft.ru | vetas.mos.ru |  идентификатор, присвоенный приложению в СУДИР |
| SUDIR_SECRET | mckoueNCR58dmdX | mckoueNCR58dmdX | ujc0GRI7gb8i3bI |  идентификатор, присвоенный приложению в СУДИР |
| SUDIR_REDIRECT_URI | https://vetas-prod.starlink-soft.ru/auth | https://vetas-preprod.mos.ru/auth | https://vetas.mos.ru/auth |  Куда будет отправлен юзер после авторизации на стороне СУДИРА |
| RUNNER | prod | prod | prod | RUNNER на гите |
| APT_MIRROR | http://deb.debian.org/debian/ | http://repo-mirror.mos.ru/repository/apt-debian/ | http://repo-mirror.mos.ru/repository/apt-debian/ | Ветка деплоя |
| FIAS_URL | https://api-vetas.mos.ru/efp6-proxy/fias | https://api-vetas.mos.ru/efp6-proxy/fias | https://api.mos.ru/api/fias/13.5 | Откуда получаем адреса по фиасу |