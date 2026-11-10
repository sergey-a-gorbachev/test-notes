---
title: Начало работы
description: Документация REST API сервиса Notes
---

# Начало работы с Notes API

Notes API позволяет создавать, получать, изменять и удалять заметки. API построен по архитектуре REST и обменивается
данными в формате JSON.

{% note info %}

Для локальной разработки запустите сервис командой `docker compose up -d` из корня проекта.

{% endnote %}

## Базовый URL

При запуске через Docker Compose используйте адрес:

```text
http://localhost:8080
```

Все операции с заметками доступны по пути `/api/notes`.

## Формат данных

Тело запросов и ответов передаётся в JSON. В запросах `POST` и `PUT` передавайте заголовок:

```http
Content-Type: application/json
```

## Быстрая проверка

Получите список заметок:

```bash
curl --request GET \
  --url http://localhost:8080/api/notes
```

Проверьте состояние сервиса:

```bash
curl --request GET \
  --url http://localhost:8080/api/health
```

Сервис вернёт:

```json
{
  "status": "ok"
}
```

{% note tip %}

Описание методов, параметров, моделей данных и кодов ответа приведено в разделе **API-справочник**.

{% endnote %}
