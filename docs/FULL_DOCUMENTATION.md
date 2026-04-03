# automateCRM — Full Documentation

> Complete technical documentation of the automateCRM system, covering architecture, application features, Docker infrastructure, CI/CD pipeline, cloud deployment, and the live Excel integration.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Tech Stack](#2-tech-stack)
3. [Architecture Diagram](#3-architecture-diagram)
4. [Application Features](#4-application-features)
5. [Database & Models](#5-database--models)
6. [Routing & API Endpoints](#6-routing--api-endpoints)
7. [Transaction Dataset — Import, Export & Live Excel](#7-transaction-dataset--import-export--live-excel)
8. [Docker Infrastructure](#8-docker-infrastructure)
9. [Container Startup Flow](#9-container-startup-flow)
10. [Jenkins CI/CD Pipeline](#10-jenkins-cicd-pipeline)
11. [SonarCloud Code Quality](#11-sonarcloud-code-quality)
12. [Terraform AWS Infrastructure](#12-terraform-aws-infrastructure)
13. [Deployment Scripts & Makefile](#13-deployment-scripts--makefile)
14. [Local Development Setup](#14-local-development-setup)
15. [End-to-End Workflow](#15-end-to-end-workflow)

---

## 1. Project Overview

**automateCRM** is a Customer Relationship Management system built with Laravel 9 and Vue.js 3. It manages customers, services, payments, transactions, and service subscriptions. The project includes a full DevOps pipeline with Docker, Jenkins CI/CD, SonarCloud analysis, Trivy security scanning, and Terraform-managed AWS infrastructure.

A key feature is the **live Excel integration**: transaction data in the CRM is exposed via a JSON API endpoint that Excel can query in real-time using Power Query, so the spreadsheet always shows up-to-date data without manual export.

---

## 2. Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.2, Laravel 9 |
| **Frontend** | Vue.js 3, Vite, Bootstrap 5 |
| **Database** | MySQL 8.0 |
| **Cache / Queue / Session** | Redis 7 |
| **Web Server** | Nginx (reverse proxy to PHP-FPM) |
| **Process Manager** | Supervisor (manages Nginx + PHP-FPM) |
| **Containerization** | Docker, Docker Compose |
| **CI/CD** | Jenkins (Declarative Pipeline) |
| **Code Quality** | SonarCloud, Laravel Pint (PSR-12 linter) |
| **Security Scanning** | Trivy (Docker image CVE scanner) |
| **Testing** | PHPUnit with PCOV code coverage |
| **Cloud Infrastructure** | AWS (ECS Fargate, RDS, ElastiCache, ALB, VPC) |
| **Infrastructure-as-Code** | Terraform (>= 1.5.0, AWS Provider ~> 5.0) |
| **Excel Integration** | Maatwebsite/Excel 3.x, PhpSpreadsheet, Power Query API |

---

## 3. Architecture Diagram

```
┌─────────────┐     git push      ┌──────────────┐
│  Developer   │ ────────────────> │    GitHub     │
└─────────────┘                    └──────┬───────┘
                                          │ webhook
                                          ▼
                                   ┌──────────────┐
                                   │   Jenkins     │
                                   │   Pipeline    │
                                   └──────┬───────┘
                                          │
                          ┌───────────────┼───────────────┐
                          ▼               ▼               ▼
                   ┌────────────┐  ┌────────────┐  ┌────────────┐
                   │ SonarCloud │  │   PHPUnit   │  │   Trivy    │
                   │  Analysis  │  │   Tests     │  │   Scan     │
                   └────────────┘  └────────────┘  └────────────┘
                                          │
                                          ▼
                                   ┌──────────────┐
                                   │ Docker Build  │
                                   │ & Deploy      │
                                   └──────┬───────┘
                                          │
                          ┌───────────────┼───────────────┐
                          ▼               ▼               ▼
                   ┌────────────┐  ┌────────────┐  ┌────────────┐
                   │  ECS       │  │   RDS      │  │ ElastiCache│
                   │  Fargate   │  │  MySQL 8.0 │  │  Redis 7   │
                   └────────────┘  └────────────┘  └────────────┘
                          │
                          ▼
                   ┌────────────┐       ┌────────────────────┐
                   │   ALB      │ <──── │   End Users        │
                   │ (public)   │       │ + Excel Power Query│
                   └────────────┘       └────────────────────┘
```

---

## 4. Application Features

### 4.1 Customers
- View all customers (paginated)
- Add new customers
- Edit customer details
- Send emails to customers
- Import/export customers via Excel

### 4.2 Services
- CRUD operations for service types
- List, create, edit, and delete services

### 4.3 Service-to-Customer (Subscriptions)
- Assign services to customers
- Renewal workflow
- Reminder status tracking
- View service details per customer

### 4.4 Payments
- Record payments
- View payment history

### 4.5 Transactions (Dataset)
- Import transactions from Excel file (`Dataset.xlsx`, sheet "POS TRX")
- Export transactions to downloadable Excel file
- Add individual transactions via web form
- Search by sales number, brand, or payment method
- **Live API** for Excel Power Query auto-refresh (see Section 7)

### 4.6 Activity Log
- Track and view application activity history

### 4.7 Admin Tools
- Import/export customers via tools page
- Manage datasets

---

## 5. Database & Models

### Models

| Model | Table | Description |
|-------|-------|-------------|
| `User` | `users` | Authentication accounts |
| `Customer` | `customers` | Customer records |
| `Service` | `services` | Service definitions |
| `ServicetoCustomer` | `servicetocustomers` | Service-customer assignments |
| `ServicetoCustomerRecord` | `servicetocustomer_records` | Subscription history |
| `Payment` | `payments` | Payment records |
| `Transaction` | `transactions` | POS transaction dataset (30+ fields) |
| `ActivityLog` | `activity_logs` | Application audit trail |

### Transaction Model (Key Fields)

```
sales_number, bill_number, sales_date_in, sales_date_out,
brand, area, city, branch, visit_purpose,
reguler_member_code, reguler_member_name,
loyalty_member_code, loyalty_member_name, loyalty_member_type,
employee_code, employee_name,
external_employee_code, external_employee_name,
payment_method, parent_payment_method,
trace_number, approval_code, edc_terminal_id,
bank_name, card_number, additional_info, notes,
mdr, payment_amount, nett_after_mdr
```

Date fields (`sales_date_in`, `sales_date_out`) are cast to `datetime`. Numeric fields (`mdr`, `payment_amount`, `nett_after_mdr`) are cast to `decimal:2`.

---

## 6. Routing & API Endpoints

### 6.1 Web Routes (Auth Required)

| Method | URL | Controller | Action |
|--------|-----|-----------|--------|
| GET | `/` | HomeController | Dashboard |
| GET | `/transactions` | TransactionController | Paginated list |
| GET | `/transactions/create` | TransactionController | Create form |
| POST | `/transactions` | TransactionController | Store new |
| GET | `/import-transactions` | TransactionController | Import from Excel |
| GET | `/export-transactions` | TransactionController | Export to Excel |
| GET | `/customers` | CustomersController | List customers |
| POST | `/customer_add` | CustomersController | Add customer |
| GET | `/customer_edit/{id}` | CustomersController | Edit customer |
| GET | `/services` | ServicesController | List services |
| POST | `/service_add` | ServicesController | Add service |
| PUT | `/services/{service}` | ServicesController | Update service |
| DELETE | `/services/{service}` | ServicesController | Delete service |
| GET | `/payments` | PaymentsController | List payments |
| POST | `/addpayment` | PaymentsController | Add payment |
| POST | `/addservicetocustomer` | ServicetoCustomerController | Assign service |
| POST | `/service/{id}/renew` | ServicetoCustomerController | Renew service |
| POST | `/sendmessage` | CustomersController | Send email |
| GET | `/tools` | CustomersController | Tools page |
| POST | `/export-customers` | CustomersController | Export customers |
| POST | `/import-customers` | CustomersController | Import customers |
| GET | `/activity-log` | ActivityLogController | View logs |

### 6.2 API Routes (No Auth)

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/health` | Health check (Docker/ALB probes) |
| GET | `/api/transactions` | **Live transaction data** (JSON, for Excel Power Query) |
| GET | `/api/customers` | All customers |
| GET | `/api/customers/{id}` | Single customer |
| GET | `/api/services` | All services |
| GET | `/api/services/{id}` | Single service |
| GET | `/api/servicetocustomer` | All service-customer records |
| GET | `/api/customers-service/{id}` | Customer's services |

The `/api/health` endpoint checks database connectivity and returns `{status, database, timestamp}`. Returns HTTP 503 if the database is unreachable.

---

## 7. Transaction Dataset — Import, Export & Live Excel

This is the core feature that connects the CRM to Excel for the team's POS transaction workflow.

### 7.1 The Problem

The team works with a POS transaction Excel file (`Dataset.xlsx`) containing hundreds of transaction records. They wanted:
1. Import the dataset into the CRM for management
2. Add new transactions through the CRM
3. See all data reflected back in Excel **automatically** — no manual copy-paste

### 7.2 Import Flow

```
Dataset.xlsx (POS TRX sheet)
    │
    ▼
TransactionsImport (WithMultipleSheets)
    │  ── reads only "POS TRX" sheet
    │  ── skips "RAW" sheet
    │  ── uses updateOrCreate (sales_number as key)
    │  ── parses Excel date serials → datetime
    │  ── batch size: 500, chunk size: 1000
    ▼
MySQL transactions table
```

**How to trigger**: Navigate to `/import-transactions` in the CRM (requires login). This truncates the existing table and re-imports all rows from `Dataset.xlsx`.

The import uses `Transaction::withoutEvents()` to prevent any observers from firing during bulk import (avoids circular writes).

### 7.3 Export Flow

**Web Export** (button in CRM): Navigate to `/export-transactions` — downloads a fresh `transactions.xlsx` file with all 30 columns, styled with blue header row and auto-sized columns.

### 7.4 Live Excel via API (Power Query)

Instead of writing to a static `.xlsx` file, the CRM exposes a **live JSON API**:

```
GET http://localhost:8080/api/transactions
```

**Response** (JSON array):
```json
[
  {
    "No": 1,
    "Sales Number": "SIKC169354817878",
    "Bill Number": "IKC202309010002",
    "Sales Date In": "2023-09-01 13:02:58",
    "Sales Date Out": "2023-09-01 13:03:08",
    "Brand": "iKitchen",
    "City": "Tangerang Selatan",
    ...
    "Payment Amount": "159000.00",
    "Nett After MDR": "159000.00"
  },
  ...
]
```

### 7.5 Setting Up Excel Power Query

To connect Excel to the live API:

1. Open Excel → **Data** tab → **From Web**
2. Enter the URL: `http://localhost:8080/api/transactions`
3. Click **OK** → Power Query Editor opens
4. The JSON array is parsed into a table automatically
5. Click **Close & Load** to insert data into a worksheet

**Auto-refresh** (minimum 1 minute via Excel UI):
1. Right-click the table → **Properties**
2. Check **"Refresh every"** → set to **1 minute**

**Auto-refresh** (5-second interval via VBA macro):
```vba
Sub AutoRefresh()
    ActiveWorkbook.Connections(1).Refresh
    Application.OnTime Now + TimeValue("00:00:05"), "AutoRefresh"
End Sub
```
Run `AutoRefresh` once to start the 5-second refresh loop. Close the workbook or press `Ctrl+Break` to stop.

### 7.6 How It All Connects

```
┌──────────────────────┐        ┌─────────────────┐
│   CRM Web Interface  │        │   Excel Desktop  │
│                      │        │                  │
│  ┌────────────────┐  │        │   Power Query    │
│  │ Add Transaction │──┼──┐    │   (auto-refresh) │
│  └────────────────┘  │  │    │        │          │
│                      │  │    │        ▼          │
│  ┌────────────────┐  │  │    │  GET /api/        │
│  │ Import Dataset  │──┼──┤    │  transactions     │
│  └────────────────┘  │  │    │        │          │
│                      │  │    └────────┼──────────┘
└──────────────────────┘  │             │
                          ▼             ▼
                   ┌─────────────────────────┐
                   │    MySQL Database        │
                   │    (transactions table)  │
                   └─────────────────────────┘
                          ▲
                          │
                   ┌──────┴──────┐
                   │ /api/       │
                   │ transactions│ ──→ JSON array
                   └─────────────┘
```

When a transaction is added in the CRM (via the web form), it goes straight to the database. Excel's Power Query fetches the latest data from `/api/transactions` on its refresh interval. The data appears in Excel automatically — even while the file is open.

---

## 8. Docker Infrastructure

### 8.1 Multi-Stage Dockerfile

The Docker image is built in 3 stages for minimal image size:

| Stage | Base Image | Purpose |
|-------|-----------|---------|
| **1 — frontend** | `node:18-alpine` | `npm ci` → `npm run build` (Vite compiles Vue/JS/CSS → `public/build/`) |
| **2 — vendor** | `composer:2` | `composer install --no-dev --optimize-autoloader` (PHP dependencies) |
| **3 — production** | `php:8.2-fpm-alpine` | Final runtime image with Nginx + PHP-FPM + Supervisor |

**PHP Extensions installed**: pdo_mysql, redis, mbstring, gd, zip, intl, opcache, bcmath, pcntl, exif

**OPcache config**: 128MB memory, 4000 max files, fast shutdown, CLI enabled.

**PHP-FPM pool**: Dynamic, max 20 children, start 4, min spare 2, max spare 6, max 500 requests/worker.

### 8.2 Docker Compose Services

```yaml
# docker-compose.yml — 7 services
```

| Service | Image | Port | Description |
|---------|-------|------|-------------|
| **app** | `automatecrm-app` (built from Dockerfile) | `8080:80` | Laravel app (Nginx + PHP-FPM via Supervisor) |
| **db** | `mysql:8.0` | `3306:3306` | MySQL database with persistent volume |
| **redis** | `redis:7-alpine` | `6379:6379` | Cache, session, and queue backend |
| **queue** | `automatecrm-app` | — | `php artisan queue:work redis` (background job processor) |
| **scheduler** | `automatecrm-app` | — | `php artisan schedule:run` every 60s (cron replacement) |
| **phpmyadmin** | `phpmyadmin/phpmyadmin` | `8081:80` | Database GUI |
| **redis-commander** | `rediscommander/redis-commander` | `8082:8081` | Redis GUI |

**Volumes**:
- `db-data` — MySQL persistent storage
- `redis-data` — Redis AOF persistence
- `app-storage` — Laravel storage (logs, cache, uploaded files)

**Network**: All services on `automatecrm-network` (bridge driver).

**Health checks**:
- MySQL: `mysqladmin ping` (10s interval, 30s start period)
- Redis: `redis-cli ping` (10s interval)
- App: `curl -f http://localhost/api/health` (30s interval, 60s start period)

### 8.3 Nginx Configuration

- Listens on port 80 inside the container
- Root: `/var/www/html/public`
- Security headers: X-Frame-Options SAMEORIGIN, X-Content-Type-Options nosniff, X-XSS-Protection
- Gzip compression for CSS, JS, JSON, XML, SVG, images
- Static assets: 30-day cache (CSS, JS, images, fonts)
- Max upload: 50MB
- PHP-FPM upstream at `127.0.0.1:9000`
- Blocks access to hidden files, `.env`, `.log`, `.sql`
- `/api/health` endpoint excluded from access logs

---

## 9. Container Startup Flow

When the `app` container starts, the entrypoint script (`docker/entrypoint.sh`) runs these steps in order:

```
Container Start
    │
    ▼
Step 1: Create .env from .env.example (if missing)
    │
    ▼
Step 2: Set APP_KEY
    │   ── from APP_KEY env var (docker-compose), or
    │   ── keep existing, or
    │   ── generate new with php artisan key:generate
    │
    ▼
Step 3: Apply docker-compose env vars to .env
    │   ── DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
    │   ── REDIS_HOST, REDIS_PORT
    │   ── CACHE_DRIVER, SESSION_DRIVER, QUEUE_CONNECTION
    │   ── MAIL_MAILER, MAIL_HOST, MAIL_PORT
    │   ── APP_ENV, APP_DEBUG, APP_URL
    │
    ▼
Step 4: Wait for MySQL (up to 30 retries × 2s = 60s max)
    │   ── tests connection via PHP PDO
    │
    ▼
Step 5: Run migrations + seeders
    │   ── php artisan migrate --seed --force
    │
    ▼
Step 6: Cache configuration
    │   ── php artisan config:cache
    │   ── php artisan route:cache
    │   ── php artisan view:cache
    │
    ▼
Step 7: Fix permissions
    │   ── chown www-data:www-data storage bootstrap/cache
    │   ── chmod 775
    │
    ▼
Step 8: Start Supervisor (PID 1)
    │   ── launches Nginx + PHP-FPM
    │
    ▼
App is ready to serve requests on port 80
```

---

## 10. Jenkins CI/CD Pipeline

The Jenkinsfile defines a declarative pipeline with 9 stages:

### Pipeline Options
- **Timeout**: 30 minutes
- **No concurrent builds**: One build at a time
- **Build retention**: Keep last 10 builds
- **Timestamps**: Enabled on all log output

### Pipeline Stages

```
┌───────────────────────────────────────────────────────────┐
│  Stage 1: Checkout                                        │
│  ── git clone + capture short commit hash                 │
├───────────────────────────────────────────────────────────┤
│  Stage 2: Install Dependencies (PARALLEL)                 │
│  ├── Composer (composer:2.7 container)                    │
│  │   └── composer install --no-dev --optimize-autoloader  │
│  └── NPM (node:18-alpine container)                      │
│      └── npm ci --no-audit                                │
├───────────────────────────────────────────────────────────┤
│  Stage 3: Code Quality (PARALLEL)                         │
│  ├── Laravel Pint (php:8.2-cli container)                 │
│  │   └── vendor/bin/pint --test (PSR-12 lint check)       │
│  └── Build Assets (node:18-alpine container)              │
│      └── npm run build (Vite production build)            │
├───────────────────────────────────────────────────────────┤
│  Stage 4: Test (php:8.2-cli container)                    │
│  ── pecl install pcov → enable                            │
│  ── cp .env.example .env → key:generate → config:clear    │
│  ── phpunit with:                                         │
│     ├── --coverage-clover=coverage.xml  (for SonarCloud)  │
│     ├── --coverage-html=coverage        (HTML report)     │
│     └── --log-junit=tests/results/junit.xml               │
│  ── Publishes: JUnit results + HTML coverage report       │
├───────────────────────────────────────────────────────────┤
│  Stage 5: SonarCloud Analysis                             │
│  ── sonar-scanner-cli:11 container                        │
│  ── Sends code + coverage.xml to SonarCloud               │
│  ── Uses sonarqube-token credential                       │
├───────────────────────────────────────────────────────────┤
│  Stage 6: Quality Gate                                    │
│  ── Waits up to 5 minutes for SonarCloud result           │
│  ── Aborts pipeline if quality gate FAILS                 │
├───────────────────────────────────────────────────────────┤
│  Stage 7: Build Docker Image (master branch only)         │
│  ── docker compose build                                  │
│  ── docker image prune -f                                 │
├───────────────────────────────────────────────────────────┤
│  Stage 8: Security Scan (master branch only)              │
│  ── Trivy scans automatecrm-app image                     │
│  ── Reports HIGH and CRITICAL CVEs                        │
│  ── exit-code 0 (report only, does not fail build)        │
├───────────────────────────────────────────────────────────┤
│  Stage 9: Deploy to Staging (master branch only)          │
│  ── docker compose up -d --remove-orphans                 │
│  ── docker system prune -f (cleanup old images)           │
│  ── Migrations handled by entrypoint.sh at container boot │
└───────────────────────────────────────────────────────────┘
```

**Branch Logic**: Stages 7-9 (Build, Security Scan, Deploy) only run on the `master` branch. All other stages run on every branch/PR.

---

## 11. SonarCloud Code Quality

### Configuration (`sonar-project.properties`)

```properties
sonar.projectKey=rafiimafif_automateCRM
sonar.organization=rafiimafif
sonar.sources=app
sonar.tests=tests
sonar.language=php
sonar.php.coverage.reportPaths=coverage.xml
sonar.php.tests.reportPath=tests/results/junit.xml
```

### Exclusions
- **Source exclusions**: vendor, node_modules, public/build, storage, bootstrap/cache, database/migrations, docker, terraform, minified files
- **Coverage exclusions**: config, database, resources, routes, bootstrap, Kernel.php, Handler.php, Providers
- **Duplicate exclusions**: Same as source exclusions

This means SonarCloud analyzes only the `app/` directory for bugs, vulnerabilities, code smells, and coverage — focusing on business logic.

---

## 12. Terraform AWS Infrastructure

The Terraform configuration provisions a production-ready AWS environment.

### 12.1 State Management

```hcl
backend "s3" {
  bucket         = "automatecrm-terraform-state"
  key            = "infrastructure/terraform.tfstate"
  region         = "ap-southeast-1"
  dynamodb_table = "automatecrm-terraform-lock"
  encrypt        = true
}
```

State is stored in S3 with DynamoDB locking for team collaboration.

### 12.2 Network Architecture

```
┌─────────────────────────────────────────────────────────┐
│  VPC: 10.0.0.0/16                                       │
│                                                         │
│  ┌─────────────────────────────────────────────────┐    │
│  │ Public Subnets                                   │    │
│  │  10.0.0.0/24 (AZ-a)  │  10.0.1.0/24 (AZ-b)    │    │
│  │         │                       │                │    │
│  │         └───────┐  ┌───────────┘                │    │
│  │                 ▼  ▼                             │    │
│  │          ┌──────────────┐                        │    │
│  │          │     ALB      │ ← Internet (via IGW)   │    │
│  │          └──────┬───────┘                        │    │
│  └─────────────────┼───────────────────────────────┘    │
│                    │                                     │
│  ┌─────────────────┼───────────────────────────────┐    │
│  │ Private Subnets │                                │    │
│  │  10.0.10.0/24 (AZ-a)  │  10.0.11.0/24 (AZ-b)  │    │
│  │                                                  │    │
│  │   ┌──────────┐  ┌──────────┐  ┌──────────┐     │    │
│  │   │ECS Tasks │  │   RDS    │  │  Redis   │     │    │
│  │   │(Fargate) │  │ (MySQL)  │  │(ElastiC.)│     │    │
│  │   └──────────┘  └──────────┘  └──────────┘     │    │
│  │                                                  │    │
│  │   Outbound: NAT Gateway → IGW                    │    │
│  └──────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
```

### 12.3 Security Groups

| Security Group | Inbound | Source |
|---------------|---------|--------|
| ALB-SG | Port 80, 443 | `0.0.0.0/0` (internet) |
| ECS-SG | Port 80 | ALB-SG only |
| RDS-SG | Port 3306 | ECS-SG only |

This ensures only the ALB is publicly accessible. ECS tasks and the database are in private subnets.

### 12.4 Compute (ECS Fargate)

- **Cluster**: Serverless Fargate (no EC2 instances to manage)
- **Task Definition**: 256 CPU / 512 MiB memory (staging), configurable for production
- **Container Image**: `ghcr.io/rafiimafif/automateCRM:latest`
- **Desired Count**: 1 (staging), configurable for production
- **Health Check**: `curl -f http://localhost/api/health`
- **Logging**: CloudWatch Logs, 30-day retention

### 12.5 Database (RDS MySQL)

- MySQL 8.0
- Instance: `db.t3.micro` (staging), configurable
- Storage: 20GB auto-scales to 100GB, encrypted
- Multi-AZ: Enabled in production only
- Backup: 7-day retention, daily window 03:00-04:00 UTC

### 12.6 Cache (ElastiCache Redis)

- Redis 7, `cache.t3.micro`, single node (staging)
- Used for cache, sessions, and queue

### 12.7 Secrets Management

Sensitive values stored in AWS SSM Parameter Store:

| Parameter | Path |
|-----------|------|
| APP_KEY | `/{app_name}/{environment}/app-key` |
| DB_USERNAME | `/{app_name}/{environment}/db-username` |
| DB_PASSWORD | `/{app_name}/{environment}/db-password` |

ECS pulls these at container launch time — never hardcoded in task definitions.

### 12.8 Staging vs Production

| Setting | Staging | Production |
|---------|---------|-----------|
| ECS CPU/Memory | 256/512 | 512/1024+ |
| Desired Count | 1 | 2+ |
| RDS Multi-AZ | No | Yes |
| RDS Final Snapshot | Skip | Required |
| Instance Classes | t3.micro | Configurable |

---

## 13. Deployment Scripts & Makefile

### 13.1 Shell Scripts

**`scripts/docker-setup.sh`** — First-time setup:
```bash
bash scripts/docker-setup.sh       # Production mode
bash scripts/docker-setup.sh dev   # Development mode
```
Checks Docker installation, creates `.env`, builds and starts all containers, runs migrations, seeds database, caches config, and prints login credentials.

**`scripts/deploy.sh`** — Remote deployment:
```bash
bash scripts/deploy.sh staging v1.0.0
bash scripts/deploy.sh production v1.0.0   # Requires confirmation
```
SSHs into the target server, pulls the Docker image, restarts containers, runs migrations, caches config, and performs a health check.

**`scripts/backup-db.sh`** — Database backup:
```bash
bash scripts/backup-db.sh
```
Creates a gzipped MySQL dump with timestamp. Optionally uploads to S3. Auto-deletes backups older than 30 days.

### 13.2 Makefile Commands

| Category | Command | Description |
|----------|---------|-------------|
| **Dev** | `make install` | Composer + NPM install |
| | `make dev` | Artisan serve + Vite dev server |
| | `make build` | Vite production build |
| **Database** | `make migrate` | Run migrations |
| | `make seed` | Run seeders |
| | `make fresh` | Drop all tables + re-migrate + seed |
| | `make db-dump` | Backup database |
| | `make db-restore file=X` | Restore from backup |
| **Testing** | `make test` | Run PHPUnit (parallel) |
| | `make test-coverage` | PHPUnit + code coverage (min 60%) |
| | `make lint` | Laravel Pint (auto-fix) |
| | `make lint-check` | Laravel Pint (check only) |
| **Security** | `make security-scan` | Composer + NPM audit |
| | `make trivy-scan` | Trivy Docker image scan |
| **Docker** | `make docker-up` | `docker-compose up -d` |
| | `make docker-down` | Stop containers |
| | `make docker-down-clean` | Stop + remove volumes |
| | `make docker-build` | Build Docker image |
| | `make docker-logs` | Tail container logs |
| | `make docker-shell` | Shell into app container |
| | `make docker-artisan cmd="..."` | Run artisan command |
| **Terraform** | `make terraform-init` | `terraform init` |
| | `make terraform-plan` | Plan changes |
| | `make terraform-apply` | Apply changes |
| | `make terraform-destroy` | Destroy infrastructure |
| **Deploy** | `make deploy-staging` | Build + push + deploy to staging |
| | `make deploy-prod` | Deploy to production (with confirmation) |

---

## 14. Local Development Setup

### Prerequisites
- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Git

### Quick Start (Docker)

```bash
# 1. Clone the repository
git clone <repository-url>
cd automatecrm-cicd-pipeline

# 2. Run the setup script
bash scripts/docker-setup.sh

# 3. Access the application
#    App:             http://localhost:8080
#    phpMyAdmin:      http://localhost:8081
#    Redis Commander: http://localhost:8082
```

### Default Login Credentials

```
Email:    admin@admin.com
Password: password
```

### Manual Setup (Without Docker)

```bash
# Prerequisites: PHP 8.0+, Composer, Node 16+, MySQL 5.7+

# 1. Install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate
# Edit .env with your DB credentials

# 3. Setup database
php artisan migrate --seed

# 4. Build frontend
npm run build

# 5. Start server
php artisan serve    # http://localhost:8000
npm run dev          # Vite dev server (HMR)
```

### Useful Docker Commands

```bash
# View logs
docker-compose logs -f app

# Shell into container
docker exec -it app sh

# Run artisan commands
docker exec app php artisan tinker
docker exec app php artisan migrate:status

# Restart services
docker-compose restart app

# Full rebuild
docker-compose build --no-cache app
docker-compose up -d
```

---

## 15. End-to-End Workflow

This section describes the complete flow from development to production, including how transactions reach Excel.

### 15.1 Development Cycle

```
Developer writes code
    │
    ▼
git commit + git push (to GitHub)
    │
    ▼
GitHub webhook triggers Jenkins
    │
    ▼
Jenkins Pipeline:
    ├── Install Composer + NPM dependencies
    ├── Run Laravel Pint (code style check)
    ├── Build Vite assets
    ├── Run PHPUnit tests with coverage
    ├── SonarCloud analysis + quality gate
    │
    ├── (master branch only):
    │   ├── Build Docker image
    │   ├── Trivy security scan
    │   └── Deploy to staging (docker-compose up)
    │
    ▼
Staging environment is live at http://localhost:8080
```

### 15.2 Transaction Data Flow

```
1. INITIAL IMPORT
   ──────────────
   Dataset.xlsx (POS TRX sheet, 275+ rows)
       │
       ▼  User clicks "Import" in CRM
   TransactionsImport → MySQL (transactions table)


2. ADD NEW DATA
   ────────────
   CRM Web Form (/transactions/create)
       │
       ▼  User fills form + submits
   TransactionController::store() → MySQL


3. LIVE EXCEL VIEW
   ───────────────
   Excel Power Query
       │
       ▼  Auto-refresh every N seconds/minutes
   GET /api/transactions → JSON → Excel table

   The data in Excel is ALWAYS current.
   No file writing, no sync scripts, no file locking issues.


4. MANUAL EXPORT
   ─────────────
   CRM "Export" button
       │
       ▼  Downloads transactions.xlsx
   TransactionsExport → Browser download
```

### 15.3 Production Deployment (AWS)

```
1. Terraform provisions AWS infrastructure:
   ── VPC + subnets + NAT
   ── ALB (public-facing)
   ── ECS Fargate cluster
   ── RDS MySQL
   ── ElastiCache Redis
   ── SSM Parameter Store (secrets)

2. Docker image pushed to GHCR:
   ── ghcr.io/rafiimafif/automateCRM:latest

3. ECS pulls the image and starts containers:
   ── entrypoint.sh runs (wait for DB, migrate, cache)
   ── Nginx + PHP-FPM start via Supervisor

4. ALB health check passes (/api/health returns 200)

5. Traffic flows:
   User → ALB → ECS Task (port 80) → Nginx → PHP-FPM → Laravel
```

---

## File Structure Reference

```
automatecrm-cicd-pipeline/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Exceptions/Handler.php     # Error handling
│   ├── Exports/                   # Excel exports
│   │   ├── CustomersExport.php
│   │   └── TransactionsExport.php
│   ├── Http/
│   │   ├── Controllers/           # Request handlers
│   │   │   ├── TransactionController.php
│   │   │   ├── CustomersController.php
│   │   │   ├── ServicesController.php
│   │   │   ├── PaymentsController.php
│   │   │   ├── ServicetoCustomerController.php
│   │   │   ├── ActivityLogController.php
│   │   │   └── HomeController.php
│   │   ├── Kernel.php             # Middleware registration
│   │   └── Middleware/            # HTTP middleware
│   ├── Imports/                   # Excel imports
│   │   ├── CustomersImport.php
│   │   └── TransactionsImport.php
│   ├── Mail/                      # Mailables
│   │   ├── ExpirationReminder.php
│   │   └── SendEmail.php
│   ├── Models/                    # Eloquent models
│   │   ├── Transaction.php
│   │   ├── Customer.php
│   │   ├── Service.php
│   │   ├── Payment.php
│   │   ├── User.php
│   │   ├── ServicetoCustomer.php
│   │   ├── ServicetoCustomerRecord.php
│   │   └── ActivityLog.php
│   ├── Providers/                 # Service providers
│   └── Services/                  # Business logic services
├── config/                        # Laravel configuration
├── database/
│   ├── factories/                 # Model factories (testing)
│   ├── migrations/                # Database schema
│   └── seeders/                   # Seed data
├── docker/
│   ├── entrypoint.sh              # Container init script
│   ├── nginx/default.conf         # Nginx config
│   └── supervisor/supervisord.conf
├── docs/
│   ├── DEVOPS.md                  # DevOps guide
│   ├── SETUP.md                   # Setup guide
│   └── FULL_DOCUMENTATION.md      # This file
├── public/
│   ├── Dataset.xlsx               # POS transaction dataset
│   └── index.php                  # Laravel entry point
├── resources/
│   ├── js/                        # Vue.js components
│   ├── views/                     # Blade templates
│   └── scss/                      # Stylesheets
├── routes/
│   ├── web.php                    # Web routes (auth required)
│   └── api.php                    # API routes (public)
├── scripts/
│   ├── backup-db.sh               # Database backup
│   ├── deploy.sh                  # Remote deployment
│   └── docker-setup.sh            # First-time Docker setup
├── terraform/
│   ├── main.tf                    # Provider + backend
│   ├── variables.tf               # Input variables
│   ├── networking.tf              # VPC, subnets, NAT
│   ├── ecs.tf                     # ECS, ALB, RDS, Redis, IAM
│   ├── outputs.tf                 # Output values
│   └── staging.tfvars             # Staging variable values
├── tests/
│   ├── Feature/                   # Feature/integration tests
│   └── Unit/                      # Unit tests
├── docker-compose.yml             # Production Docker stack
├── docker-compose.jenkins.yml     # Jenkins CI/CD stack
├── Dockerfile                     # Multi-stage build
├── Jenkinsfile                    # CI/CD pipeline
├── Makefile                       # Developer shortcuts
├── sonar-project.properties       # SonarCloud config
└── README.md                      # Project overview
```
