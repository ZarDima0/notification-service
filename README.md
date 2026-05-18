# Notification Service - Тестовое задание для ООО "Умная Логистика"

Микросервис уведомлений для массовой отправки SMS/Email сообщений с поддержкой приоритетов, идемпотентности и отслеживания статусов доставки.

---
## 🧱 Технологический стек

- PHP 8.4
- Laravel 12
- PostgreSQL
- Redis
- RabbitMQ
- Docker / Docker Compose

---

## 📦 Установка и запуск

### 1. Клонировать проект

```bash
 git clone https://github.com/ZarDima0/notification-service.git
 cd notification-service
```
### 2. Запустить
```bash
    make setup
```
### 2. Запустить c тестами
```bash
  make setup && make test
```
📡 API
📤 Массовая отправка уведомлений
POST /api/notifications/bulk

{

"channel": "sms",

"message": "Your code: 1234",

"priority": "high",

"recipients": [1, 2, 3],

"idempotency_key": "unique-key-123"

}

{

"batch_id": "uuid",

"status": "queued"

}

📥 История уведомлений получателя

GET /api/notifications/recipient/{recipientId}?per_page=20

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

      "created_at": "2026-01-01T09:59:59Z"

    }

],

"meta": {

    "current_page": 1,

    "last_page": 1,

    "per_page": 10,

    "total": 1

}

}