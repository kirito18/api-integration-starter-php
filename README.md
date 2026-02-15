# API Integration Starter (PHP)

Production-ready starter template for **API integrations in PHP**: clean
structure, `.env` config, cURL HTTP client, logging, error handling, and
demo endpoints.

> Built for real-world use cases: connecting external APIs, handling
> retries/errors, and keeping a clear audit trail (logs).

------------------------------------------------------------------------

## ✨ Features

-   ✅ Simple, clean project structure (`public/`, `src/`, `examples/`)
-   ✅ `.env` configuration via `.env.example`
-   ✅ cURL-based HTTP client with consistent response shape
-   ✅ File logging (`storage/logs/app.log`) in JSON lines
-   ✅ Demo endpoints:
    -   `GET /health`
    -   `POST /proxy` → forwards payload to an external API (default:
        httpbin)

------------------------------------------------------------------------

## 🧭 Architecture (High Level)

~~~mermaid
flowchart LR
  A["Client / Postman"] -->|HTTP| B["public/index.php Router"]
  B --> C["Config - .env"]
  B --> D["Logger - JSON logs"]
  B --> E["HttpClient - cURL"]
  E --> F["External API"]
  D --> G["storage/logs/app.log"]

~~~
------------------------------------------------------------------------

## 🚀 Quick Start

### 1) Requirements

-   PHP 8.0+ (recommended)

------------------------------------------------------------------------

### 2) Setup environment

``` bash
cp .env.example .env
```

Edit `.env` if needed:

-   `API_BASE_URL`
-   `API_KEY`
-   `TIMEOUT`
-   `LOG_PATH`

------------------------------------------------------------------------

### 3) Run local server

``` bash
php -S 127.0.0.1:8000 -t public
```

------------------------------------------------------------------------

## 🧪 Test Endpoints

### ✅ Healthcheck

``` bash
curl http://127.0.0.1:8000/health
```

Expected response:

``` json
{
  "ok": true,
  "service": "api-integration-starter-php",
  "ts": "2026-02-15T00:00:00Z"
}
```

------------------------------------------------------------------------

### ✅ Proxy (Forward Request)

``` bash
curl -X POST http://127.0.0.1:8000/proxy \
  -H "Content-Type: application/json" \
  -d '{"demo": true, "message": "Hello"}'
```

This will forward your JSON payload to:

`${API_BASE_URL}/post`\
(default: `https://httpbin.org/post`)

------------------------------------------------------------------------

## 📁 Project Structure

    api-integration-starter-php/
    ├─ public/
    │  └─ index.php
    ├─ src/
    │  ├─ Config.php
    │  ├─ Logger.php
    │  └─ Http/
    │     └─ HttpClient.php
    ├─ examples/
    │  └─ request-example.php
    ├─ .env.example
    └─ README.md

------------------------------------------------------------------------

## 🪵 Logging

Logs are written as **JSON lines** to:

`storage/logs/app.log` (default)

Example log entry:

``` json
{
  "ts": "2026-02-15T00:00:00Z",
  "level": "INFO",
  "message": "HTTP request",
  "context": {
    "method": "POST",
    "url": "https://httpbin.org/post",
    "has_body": true
  }
}
```

------------------------------------------------------------------------

## 🔒 Notes on Security

This repo is intentionally safe by design:

-   ✅ No real keys stored in code
-   ✅ `.env` is ignored (keep secrets local)
-   ✅ Demo flows use placeholder endpoints
-   ✅ Clear separation between configuration and runtime logic

------------------------------------------------------------------------

## 🗺️ Roadmap

-   [ ] Add retry with exponential backoff (idempotent-safe calls)
-   [ ] Add correlation ID for request tracing
-   [ ] Add webhook signature validation helper
-   [ ] Add simple response caching example

------------------------------------------------------------------------

## 📬 Author

**Rober Lopez**\
Backend & API Integration Specialist · Payments · Automation ·
UX/UI-minded Engineer

-   🌐 Website: https://roberlopez.com
-   💻 GitHub: https://github.com/kirito18
-   🔗 LinkedIn: https://www.linkedin.com/in/web-rober-lopez/
