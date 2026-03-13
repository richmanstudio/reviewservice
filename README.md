# Review Service

Тестовое задание для компании **РАСА**.

Сервис сбора отзывов о качестве обслуживания. Клиент переходит по персональной ссылке, выставляет оценку от 1 до 5 и при желании оставляет текстовый комментарий.

---

## Стек

| Категория | Технология |
|---|---|
| Язык | PHP 8.0+ |
| База данных | MySQL 5.7+ / 8.x |
| ORM / доступ к БД | PDO |
| Тесты | PHPUnit 10 |
| Автозагрузка | Composer (PSR-4) |

---

## Структура проекта

```
review_service/
├── config/
│   └── database.php          # Настройки подключения к БД
├── migrations/
│   └── 001_create_tables.sql # SQL-схема + тестовые данные
├── public/
│   └── index.php             # Точка входа (веб-страница)
├── src/
│   ├── Database.php          # Singleton-обёртка над PDO
│   ├── ClientRepository.php  # Работа с таблицей clients
│   ├── ReviewRepository.php  # Работа с таблицей reviews
│   ├── Validator.php         # Валидация входных данных
│   └── ReviewService.php     # Бизнес-логика сервиса
├── tests/
│   ├── ReviewServiceTest.php
│   ├── ReviewRepositoryTest.php
│   ├── ClientRepositoryTest.php
│   └── ValidatorTest.php
├── composer.json
└── phpunit.xml
```

---

## Быстрый старт

### 1. Клонировать репозиторий

```bash
git clone <url-репозитория>
cd review_service
```

### 2. Установить зависимости

```bash
composer install
```

### 3. Создать базу данных

Подключитесь к MySQL и выполните:

```sql
CREATE DATABASE review_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Затем примените миграцию:

```bash
mysql -u root -p review_service < migrations/001_create_tables.sql
```

Миграция создаёт таблицы `clients` и `reviews`, а также добавляет трёх тестовых клиентов с `id = 1, 2, 3`.

### 4. Настроить подключение к БД

Откройте `config/database.php` и укажите свои данные:

```php
return [
    'driver'   => 'mysql',
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'dbname'   => 'review_service',
    'username' => 'root',   // ваш логин
    'password' => 'root',   // ваш пароль
    'charset'  => 'utf8mb4',
];
```

### 5. Запустить веб-сервер

```bash
php -S localhost:8000 -t public
```

### 6. Открыть в браузере

```
http://localhost:8000/?client_id=1
```

Доступные тестовые клиенты: `client_id=1`, `client_id=2`, `client_id=3`.

---

## Запуск тестов

Тесты полностью изолированы — используют SQLite in-memory и моки, MySQL для запуска **не нужен**.

```bash
./vendor/bin/phpunit
```

Ожидаемый результат:

```
OK (N tests, N assertions)
```

---

## Как работает сервис

### Сценарий 1 — корректная ссылка

1. Клиент открывает `/?client_id=1`
2. `ReviewService::resolveClient()` валидирует `client_id` и ищет клиента в БД
3. Отображается форма с выбором оценки (1–5) и необязательным комментарием
4. После отправки `ReviewService::submitReview()` проверяет данные и сохраняет отзыв
5. Клиент видит экран благодарности

### Сценарий 2 — невалидная или чужая ссылка

- `/?client_id=abc`, `/?client_id=0`, `/?client_id=999` → отображается заглушка «Ссылка на голосование недоступна»

### Правила валидации

| Поле | Правила |
|---|---|
| `client_id` | Обязателен, целое положительное число, клиент должен существовать в БД |
| `rating` | Обязателен, целое число от 1 до 5 |
| `comment` | Необязателен, не более 2000 символов |

---

## Архитектурные решения

- **Разделение ответственности** — `Validator`, `ClientRepository`, `ReviewRepository` и `ReviewService` — отдельные классы с чёткими зонами ответственности.
- **Dependency Injection** — `ReviewService` получает зависимости через конструктор, что делает его легко тестируемым через моки.
- **Database Singleton** — `Database::getInstance()` гарантирует единственное соединение с БД в рамках запроса.
- **Защита от XSS** — весь вывод в HTML экранируется через `htmlspecialchars()`.
- **Защита от SQL-инъекций** — все запросы используют подготовленные выражения PDO.
