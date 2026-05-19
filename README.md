# 🚀 Notification Service

Тестовое задание для ООО «Умная Логистика»

Микросервис для массовой отправки SMS и Email уведомлений  
с поддержкой:
---

# 🧱 Технологический стек

| Технология | Версия |
|---|---|
| PHP | 8.4 |
| Laravel | 12 |
| PostgreSQL | Latest |
| Redis | Latest |
| RabbitMQ | Latest |
| Docker | Compose |

---

Для удобного тестирования API в репозитории находится готовая Postman коллекция:

```text
Notification-service.postman_collection.json
```

# 📦 Установка и запуск

## 1. Клонирование проекта

```bash
git clone https://github.com/ZarDima0/notification-service.git

cd notification-service
```

---

## 2. Запуск проекта

```bash
make setup
```

После запуска API будет доступен по адресу:

```text
http://localhost:8000
```

---

## 3. Запуск с тестами

```bash
make setup && make test
```

---

# 📡 API

# 📤 Массовая отправка уведомлений

## Endpoint

```http
POST /api/notifications/bulk
```

---

## Request

```json
{
  "channel": "sms",
  "message": "Your code: 1234",
  "priority": "high",
  "recipients": [1, 2, 3],
  "idempotency_key": "unique-key-123"
}
```

---

## Response

```json
{
  "batch_id": "uuid",
  "status": "queued"
}
```

---

# 📥 История уведомлений получателя

## Endpoint

```http
GET /api/notifications/recipient/{recipientId}?per_page=20
```

---

## Response

```json
{
  "data": [
    {
      "id": "uuid",
      "recipient_id": 1,
      "status": "delivered",
      "channel": "sms",
      "message": "Your code: 1234",
      "priority": "high",
      "sent_at": "2026-01-01T10:00:00Z",
      "delivered_at": "2026-01-01T10:00:02Z",
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

# 📌 Статусы уведомлений

| Статус | Описание |
|---|---|
| queued | Уведомление поставлено в очередь |
| processing | В обработке |
| sent | Отправлено |
| delivered | Доставлено |
| failed | Ошибка отправки |

---

# 🔁 Идемпотентность

Для предотвращения повторной отправки используется поле:

```json
"idempotency_key": "unique-key-123"
```

Если запрос с таким ключом уже был обработан, сервис вернет ранее созданный результат.

---

# 📨 Приоритеты уведомлений

| Приоритет | Описание |
|---|---|
| low | Низкий |
| high | Высокий |

Высокоприоритетные уведомления обрабатываются раньше остальных.

---

# 🧪 Тестирование

## Запуск тестов

```bash
make test
```
---

## Остановить контейнеры

```bash
docker compose down
```