# Framework X Guestbook

A simple guestbook REST API built with [Framework X](https://framework-x.org/) (clue/framework-x), ReactPHP, and MySQL. Entries can be listed and created via HTTP.

## Requirements

- [Docker](https://docs.docker.com/get-docker/) and Docker Compose

## Setup

1. Copy the example environment file and fill in credentials:

```bash
cp .env.example .env
```

Edit `.env` and set values for all four variables:

```env
MYSQL_DATABASE=guestbook
MYSQL_USER=guestbook
MYSQL_PASSWORD=secret
MYSQL_ROOT_PASSWORD=rootsecret
```

2. Start the Docker stack:

```bash
docker compose up --build --watch
```


### List entries

```
GET /
```

Returns all guestbook entries.

**Example:**

```bash
curl http://localhost:8080/
```

```json
[
  {
    "id": 1,
    "name": "Alice",
    "message": "Hello from the guestbook!",
    "created_at": "2026-06-07 10:00:00"
  }
]
```

### Create an entry

```
POST /entries
Content-Type: application/json
```

**Body fields:**

| Field     | Type   | Required |
|-----------|--------|----------|
| `name`    | string | yes      |
| `message` | string | yes      |

**Example:**

```bash
curl -X POST http://localhost:8080/entries \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice","message":"Hello from the guestbook!"}'
```

Returns `201 Created` on success.

## Running tests
Tests run inside the app container, so they share the same PHP version and Composer dependencies.

```bash
docker compose exec app vendor/bin/phpunit
```

**Run with text coverage report:**

```bash
docker compose exec app vendor/bin/phpunit --coverage-text
```

## Project structure

```
public/index.php          # Entry point - wires the DI container and routes
src/
  Handler/
    EntryListHandler.php  # GET /
    EntryCreateHandler.php# POST /entries
  Repository/
    EntryRepository.php   # Query logic
  Database/
    DatabaseInterface.php
    MysqlDatabase.php     # ReactPHP MySQL adapter
  Model/
    Entry.php             # Value object
tests/
  Unit/                   # PHPUnit unit tests (handlers + repository)
schema.sql                # MySQL table definition
```
