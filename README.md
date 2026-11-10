# Notes

Приложение для работы с заметками. Состоит из REST API на Symfony и интерфейса на React.

Поддерживаются пять операций:

- получение списка заметок;
- создание заметки;
- получение заметки по идентификатору;
- изменение заметки;
- удаление заметки.

## Запуск

Требования:

- Docker Engine или Docker Desktop;
- Docker Compose v2.

```bash
git clone https://github.com/sergey-a-gorbachev/test-notes.git
cd test-notes
docker compose up -d --build
```

Для окружения, доступного не только локально, задайте случайный секрет через `APP_SECRET` (значение по умолчанию
предназначено только для демонстрационного запуска на `127.0.0.1`):

```bash
APP_SECRET="$(openssl rand -hex 32)" docker compose up -d --build
```

После запуска доступны:

| Сервис             | Адрес                              |
|--------------------|------------------------------------|
| Интерфейс          | http://localhost:8080              |
| REST API           | http://localhost:8080/api/notes    |
| Документация API   | http://localhost:8080/docs/        |
| OpenAPI YAML       | http://localhost:8080/openapi.yaml |
| Проверка состояния | http://localhost:8080/api/health   |

Полезные команды:

```bash
docker compose ps
docker compose logs -f api
docker compose logs -f web
docker compose down
docker compose down -v
```

`docker compose down -v` удаляет SQLite-базу вместе с Docker volume.

## API

| Метод    | Путь              | Успешный ответ |
|----------|-------------------|----------------|
| `GET`    | `/api/notes`      | `200`          |
| `POST`   | `/api/notes`      | `201`          |
| `GET`    | `/api/notes/{id}` | `200`          |
| `PUT`    | `/api/notes/{id}` | `200`          |
| `DELETE` | `/api/notes/{id}` | `204`          |

Коды ошибок:

- `400` — некорректный JSON;
- `404` — заметка не найдена;
- `415` — требуется `Content-Type: application/json`;
- `409` — передана устаревшая версия заметки;
- `422` — ошибка проверки полей.

Ограничения полей:

- `title` — обязательная строка, не более 160 символов;
- `content` — строка, не более 10 000 символов.
- `version` — положительное целое число для защиты от одновременного изменения заметки.

`POST` не принимает `version`. Для `PUT` обязательна версия из последнего GET. Для `DELETE` эта версия
передаётся целым положительным числом в заголовке `X-Note-Version`. При конфликте API отвечает `409`, не изменяя
сохранённые данные. После конфликта интерфейс показывает обе редакции и требует явно выбрать серверную версию либо
объединить изменения вручную.

## Тесты

```bash
cd backend
composer install
vendor/bin/phpunit
composer validate --strict
```

Сборка интерфейса:

```bash
cd frontend
npm ci
npm run build
npm test
```

## Структура проекта

```text
backend/
  src/Application/          DTO и сценарии использования
  src/Domain/               модель, исключения и интерфейсы репозиториев
  src/Infrastructure/       реализация репозитория на Doctrine ORM
  src/Controller/           HTTP-контроллеры и PHP Attributes OpenAPI
  src/EventSubscriber/      обработка ошибок API
  migrations/               миграции схемы базы данных
  tests/                    функциональные тесты API

frontend/
  src/api/                  HTTP-клиент и методы Notes API
  src/components/           React-компоненты
  src/hooks/                хуки для работы с данными
  src/utils/                вспомогательные функции
  nginx.conf                проксирование запросов к API

docs/
  .yfm                      настройки Diplodoc
  toc.yaml                  оглавление
  index.md                  главная страница документации
  package.json              зависимости и команда сборки

Dockerfile                  сборка API, интерфейса, OpenAPI и Diplodoc
compose.yaml                сервисы api и web
```

OpenAPI генерируется из PHP Attributes и ограничений Symfony Validator во время Docker-сборки. Полученный файл
используется для сборки документации Diplodoc.
