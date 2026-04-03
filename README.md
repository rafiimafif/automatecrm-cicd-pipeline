<div align="center" id="readme-top">

[![LinkedIn][linkedin-shield]][linkedin-url]

<br />

<a href="https://github.com/rafiimafif/automatecrm-cicd-pipeline">
  <img src="/public/img/automateCRM.png" alt="automateCRM Logo" width="80" height="80">
</a>

<h3>automateCRM</h3>

<p>
  A production-ready Laravel CRM platform with a full DevOps pipeline —<br/>
  containerized with Docker, automated with Jenkins, quality-gated with SonarCloud,<br/>
  and infrastructure-provisioned with Terraform on AWS.
</p>

<a href="https://github.com/rafiimafif/automatecrm-cicd-pipeline/issues">Report Bug</a>
·
<a href="https://github.com/rafiimafif/automatecrm-cicd-pipeline/pulls">Request Feature</a>

</div>

---

## Table of Contents

- [About The Project](#about-the-project)
- [Application Stack](#application-stack)
- [DevOps Toolchain](#devops-toolchain)
- [Architecture Overview](#architecture-overview)
- [Docker](#docker)
  - [Multi-Stage Dockerfile](#multi-stage-dockerfile)
  - [Docker Compose Services](#docker-compose-services)
  - [Accessing Dev Tools](#accessing-dev-tools)
- [Jenkins CI/CD Pipeline](#jenkins-cicd-pipeline)
  - [Pipeline Stages](#pipeline-stages)
  - [Jenkins Setup](#jenkins-setup)
  - [Required Credentials](#required-credentials)
- [SonarCloud — Code Quality](#sonarcloud--code-quality)
- [Trivy — Security Scanning](#trivy--security-scanning)
- [Terraform — AWS Infrastructure](#terraform--aws-infrastructure)
  - [AWS Resources](#aws-resources)
  - [Terraform Usage](#terraform-usage)
- [Getting Started Locally](#getting-started-locally)
  - [Quick Start with Docker](#quick-start-with-docker)
  - [Manual Setup](#manual-setup)
- [Makefile Reference](#makefile-reference)
- [Shell Scripts](#shell-scripts)
- [Environment Variables](#environment-variables)
- [Project Structure](#project-structure)
- [Contact](#contact)

---

## About The Project

automateCRM is a streamlined Customer Relationship Management platform built with **Laravel 9** and **Vue.js 3**. This repository showcases a complete **CI/CD pipeline** and **DevOps workflow** wrapping the application — from local development through automated testing, security scanning, code quality gates, Docker image building, and infrastructure provisioning on AWS.

### Key Application Features

| Feature | Description |
|---|---|
| **Customer Management** | Add, edit, delete, import/export customers via Excel |
| **Service Tracking** | Assign services to customers, track expiration dates |
| **Payment Management** | Record and view payment history per customer |
| **Transaction Dataset** | Import POS transactions from Excel, add new ones via CRM |
| **Live Excel Integration** | Real-time API endpoint (`/api/transactions`) for Excel Power Query — spreadsheet auto-refreshes with live data, no manual export needed |
| **Activity Logging** | Full audit trail of all system actions |
| **Email Notifications** | Automated service expiration reminder emails |
| **Dashboard Analytics** | Visual overview of business metrics |
| **REST API** | JSON API endpoints for transactions, customers, services, and health checks |
| **Queue Workers** | Background job processing via Redis queues |
| **Task Scheduler** | Cron-like scheduler running inside Docker |

---

## Application Stack

| Layer | Technology | Version |
|---|---|---|
| **Backend** | Laravel | 9.x |
| **Language** | PHP | 8.2 |
| **Frontend** | Vue.js + Vite | 3.x |
| **CSS Framework** | Bootstrap | 5.x |
| **Database** | MySQL | 8.0 |
| **Cache / Queue / Session** | Redis | 7-alpine |
| **Auth** | Laravel UI + Sanctum | — |
| **Excel I/O** | Maatwebsite/Excel | 3.x |
| **Web Server** | Nginx | alpine |
| **PHP Process Manager** | PHP-FPM | 8.2 |
| **Process Supervisor** | Supervisor | — |

---

## DevOps Toolchain

| Tool | Role |
|---|---|
| **Docker** | Multi-stage image build (Node → Composer → PHP-FPM+Nginx+Supervisor) |
| **Docker Compose** | Full local stack orchestration — 7 services |
| **Jenkins** | Self-hosted CI/CD server with declarative pipeline |
| **SonarCloud** | Continuous code quality & security analysis with Quality Gate |
| **Trivy** | Docker image vulnerability scanning (HIGH/CRITICAL CVEs) |
| **Terraform** | Infrastructure as Code — AWS ECS Fargate, RDS, ElastiCache, ALB, VPC |
| **PHPUnit** | Automated test suite with code coverage (PCOV) |
| **Laravel Pint** | PHP code style linting (PSR-12) |
| **phpMyAdmin** | Web-based MySQL administration GUI (port 8081) |
| **Redis Commander** | Web-based Redis data browser GUI (port 8082) |
| **Make** | Developer command shortcuts |

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     Developer Workstation                       │
│                                                                 │
│   git push → GitHub → Jenkins (polls / webhook)                 │
└─────────────────────────┬───────────────────────────────────────┘
                          │
         ┌────────────────▼────────────────────────────┐
         │              Jenkins Pipeline                │
         │                                             │
         │  Checkout → Install Deps (parallel)          │
         │    → Code Quality + Build Assets (parallel)  │
         │    → Test + Coverage (PHPUnit + PCOV)        │
         │    → SonarCloud Analysis                     │
         │    → Quality Gate (blocks on fail)           │
         │    → Build Docker Image (master only)        │
         │    → Trivy Security Scan (master only)       │
         │    → Deploy to Staging (master only)         │
         └──────┬──────────────────┬────────────────────┘
                │                  │
     ┌──────────▼──────┐  ┌────────▼──────────────────────┐
     │   SonarCloud    │  │   Local Docker Stack           │
     │  (Quality Gate) │  │                               │
     └─────────────────┘  │  app             :8080 (Nginx) │
                          │  mysql           :3306         │
                          │  redis           :6379         │
                          │  queue           (worker)      │
                          │  scheduler       (cron)        │
                          │  phpmyadmin      :8081         │
                          │  redis-commander :8082         │
                          └────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                   AWS (via Terraform)                            │
│                                                                  │
│   VPC (10.0.0.0/16)                                              │
│   ├── Public Subnets (2 AZs)  → Application Load Balancer :80   │
│   └── Private Subnets (2 AZs) → ECS Fargate Task (PHP+Nginx)   │
│                                  RDS MySQL 8.0                   │
│                                  ElastiCache Redis               │
│                                  IAM Roles + Security Groups     │
│                                  SSM Parameter Store (secrets)   │
└──────────────────────────────────────────────────────────────────┘
```

---

## Docker

### Multi-Stage Dockerfile

The `Dockerfile` uses a **3-stage build** to produce a lean, optimized production image:

```
Stage 1 — frontend  (node:18-alpine)
  ├── Installs Node dependencies (npm ci)
  └── Builds Vite assets → public/build/

Stage 2 — vendor  (composer:2)
  ├── Installs PHP dependencies
  └── --no-dev --optimize-autoloader (production only)

Stage 3 — production  (php:8.2-fpm-alpine)
  ├── Installs system packages: Nginx, Supervisor, libpng, libzip, icu, oniguruma
  ├── Installs PHP extensions: pdo_mysql, redis, mbstring, gd, zip, intl, opcache, bcmath, pcntl, exif
  ├── Configures OPcache for production performance
  ├── Configures PHP-FPM pool (dynamic, max 20 workers)
  ├── Copies application code + sets www-data ownership
  ├── Copies built assets from Stage 1
  ├── Copies vendor from Stage 2
  ├── Loads Nginx config (docker/nginx/default.conf)
  ├── Loads Supervisor config (docker/supervisor/supervisord.conf)
  └── EXPOSES port 80 (Nginx → PHP-FPM :9000)
```

**Result:** A single image that runs Nginx, PHP-FPM, and Supervisor — all managed by Supervisor as PID 1. No separate web server container needed.

```bash
# Build the image manually
docker compose build

# Confirm image size
docker images automatecrm-app
```

### Docker Compose Services

**`docker-compose.yml`** — Full stack (7 services):

| Container | Image | Port | Role |
|---|---|---|---|
| `app` | Built from `Dockerfile` | `8080:80` | Laravel app (Nginx + PHP-FPM) |
| `mysql` | `mysql:8.0` | `3306:3306` | Primary database with persistent volume |
| `redis` | `redis:7-alpine` | `6379:6379` | Cache, sessions, and queue backend |
| `queue` | Built from `Dockerfile` | — | `php artisan queue:work redis --tries=3` |
| `scheduler` | Built from `Dockerfile` | — | Runs `php artisan schedule:run` every 60 seconds |
| `phpmyadmin` | `phpmyadmin/phpmyadmin` | `8081:80` | Web-based MySQL administration UI |
| `redis-commander` | `rediscommander/redis-commander` | `8082:8081` | Redis data browser UI |

**Networking:** All services share the `automatecrm-network` bridge network. Inter-service communication uses container names (e.g. `DB_HOST=db`, `REDIS_HOST=redis`).

**Volumes:**
- `db-data` — MySQL data directory (persistent across restarts)
- `redis-data` — Redis AOF persistence
- `app-storage` — Laravel `storage/` directory (logs, uploads, cache)

**Healthchecks:**
- `mysql` — `mysqladmin ping` every 10s, app waits for healthy before starting
- `redis` — `redis-cli ping` every 10s
- `queue` / `scheduler` — `php -r "echo 'ok';"` every 30s

### Accessing Dev Tools

phpMyAdmin and Redis Commander are included in the main `docker-compose.yml` — no separate dev overlay needed:

| Tool | URL | Description |
|---|---|---|
| **App** | `http://localhost:8080` | CRM web interface |
| **phpMyAdmin** | `http://localhost:8081` | MySQL database browser |
| **Redis Commander** | `http://localhost:8082` | Redis data browser |
| **API (Transactions)** | `http://localhost:8080/api/transactions` | Live JSON for Excel Power Query |
| **API (Health)** | `http://localhost:8080/api/health` | Container health check |

---

## Jenkins CI/CD Pipeline

### Pipeline Stages

The `Jenkinsfile` defines a **declarative pipeline** with the following stages:

```
Checkout
    │
    ▼
Install Dependencies ──────────────────────────── (parallel)
    ├── Composer (composer:2.7 Docker agent)
    │     └── composer install --no-interaction --optimize-autoloader
    └── NPM (node:18-alpine Docker agent)
          └── npm ci --no-audit
    │
    ▼
Code Quality ──────────────────────────────────── (parallel)
    ├── Laravel Pint (php:8.2-cli Docker agent)
    │     └── vendor/bin/pint --test  ← fails build on style violations
    └── Build Assets (node:18-alpine Docker agent)
          └── npm run build
    │
    ▼
Test (php:8.2-cli Docker agent)
    ├── Installs PCOV extension for code coverage
    ├── Copies .env.example → .env, generates app key
    ├── Runs: vendor/bin/phpunit
    │       --coverage-clover=coverage.xml
    │       --coverage-html=coverage/
    │       --log-junit=tests/results/junit.xml
    ├── Publishes JUnit XML results to Jenkins
    └── Publishes HTML coverage report to Jenkins
    │
    ▼
SonarCloud Analysis (sonarsource/sonar-scanner-cli:11 Docker agent)
    ├── Runs sonar-scanner with org, project key, coverage path
    └── Submits results to SonarCloud
    │
    ▼
Quality Gate
    └── waitForQualityGate — aborts pipeline if SonarCloud gate fails (5 min timeout)
    │
    ▼ (master branch only)
Build Docker Image
    ├── docker compose build   ← builds production image from Dockerfile
    └── docker image prune -f  ← removes dangling <none> images
    │
    ▼ (master branch only)
Security Scan (Trivy)
    └── Scans automatecrm-app image for HIGH and CRITICAL CVEs
    │
    ▼ (master branch only)
Deploy to Staging
    ├── docker compose up -d --remove-orphans
    ├── docker system prune -f (cleanup old images)
    └── Migrations & config caching handled by entrypoint.sh at container boot
```

**Pipeline options:**
- Timeout: 30 minutes (entire pipeline)
- Concurrent builds disabled (`disableConcurrentBuilds`)
- Keeps last 10 build records
- Timestamps enabled on all log output
- Workspace cleaned after every build (`deleteDir()`)

**Branch logic:** The Build, Security Scan, and Deploy stages only run on `master` (checked via `env.GIT_BRANCH == 'master' || env.GIT_BRANCH == 'origin/master'`). All other stages run on every branch/PR.

### Jenkins Setup

Jenkins itself runs as a Docker container defined in `docker-compose.jenkins.yml`:

```bash
# Build the custom Jenkins image and start the container
docker compose -f docker-compose.jenkins.yml up -d --build

# Access Jenkins UI
open http://localhost:8090

# Get initial admin password (first-time setup only)
docker exec jenkins cat /var/jenkins_home/secrets/initialAdminPassword
```

**`docker/jenkins/Dockerfile`** — Custom Jenkins image based on `jenkins/jenkins:lts-jdk21`:
- Installs `docker-ce-cli` — allows Jenkins to run Docker commands using the host socket
- Installs `docker-compose-plugin` — provides `docker compose` (v2) inside Jenkins
- Mounts `/var/run/docker.sock` from the host — Docker-in-Docker without a nested daemon

**Data persistence:** Jenkins configuration, jobs, and plugins are stored in a named Docker volume (`automatecrm-jenkins_jenkins-home`). This volume is explicitly referenced as `external: true` in the compose file to survive container recreations.

**Required Jenkins plugins:**
1. **Pipeline** — Declarative pipeline support
2. **Docker Pipeline** — Runs pipeline stages inside Docker containers
3. **SonarQube Scanner** — Integrates with SonarCloud Quality Gate (`waitForQualityGate`)
4. **JUnit** — Publishes test result XML reports
5. **HTML Publisher** — Publishes code coverage HTML reports

### Required Credentials

Configure these in **Manage Jenkins → Credentials → System → Global credentials**:

| ID | Type | Description |
|---|---|---|
| `sonarqube-token` | Secret text | SonarCloud authentication token |

Configure SonarCloud server in **Manage Jenkins → Configure System → SonarQube servers**:
- Name: `SonarCloud`
- URL: `https://sonarcloud.io`
- Authentication token: `sonarqube-token` credential

---

## SonarCloud — Code Quality

**File:** `sonar-project.properties`

SonarCloud provides continuous static analysis with every pipeline run:

| Setting | Value |
|---|---|
| Organization | `rafiimafif` |
| Project Key | `rafiimafif_automateCRM` |
| Language | PHP |
| Sources | `app/` |
| Tests | `tests/` |
| Coverage report | `coverage.xml` (Clover format, generated by PCOV) |
| Test report | `tests/results/junit.xml` (JUnit XML) |

**What SonarCloud analyzes:**
- Code smells and maintainability issues
- Potential bugs and reliability issues
- Security vulnerabilities and hotspots
- Duplicated code blocks
- Test coverage percentage

**Exclusions (not analyzed):**
`vendor/`, `node_modules/`, `public/build/`, `storage/`, `bootstrap/cache/`, `database/migrations/`, `docker/`, `terraform/`, minified JS/CSS

**Coverage exclusions** (no coverage required for):
`config/`, `database/`, `resources/`, `routes/`, `bootstrap/`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php`, `app/Providers/`

**Quality Gate** — The pipeline `waitForQualityGate` step blocks the build until SonarCloud finishes analysis. If the default Quality Gate fails (e.g. coverage too low, new bugs introduced), the pipeline aborts immediately before deploy.

---

## Trivy — Security Scanning

**Tool:** `ghcr.io/aquasecurity/trivy:latest`

Trivy scans the built Docker image for known CVEs in OS packages and PHP/Node dependencies:

```bash
docker run --rm \
  -v /var/run/docker.sock:/var/run/docker.sock \
  ghcr.io/aquasecurity/trivy:latest image \
  --severity HIGH,CRITICAL \
  --exit-code 0 \
  automatecrm-app
```

| Option | Meaning |
|---|---|
| `--severity HIGH,CRITICAL` | Only report HIGH and CRITICAL vulnerabilities |
| `--exit-code 0` | Report findings but do not fail the pipeline (informational) |
| `automatecrm-app` | The local Docker image built by `docker compose build` |

**Run locally:**
```bash
make trivy-scan
```

---

## Terraform — AWS Infrastructure

**Directory:** `terraform/`

Provisions a complete AWS environment using **ECS Fargate** (serverless containers). State is stored remotely in S3 with DynamoDB locking to support team collaboration.

### AWS Resources

| Resource | Details |
|---|---|
| **VPC** | Custom VPC `10.0.0.0/16` with public & private subnets across 2 Availability Zones |
| **Internet Gateway** | Allows public subnet egress |
| **Application Load Balancer** | HTTP :80 → ECS Service, spread across both public subnets |
| **ECS Cluster** | Fargate (serverless) — no EC2 instances to manage |
| **ECS Task Definition** | CPU: 512 units, Memory: 1024 MiB, PHP+Nginx container |
| **ECS Service** | Desired count: 2 replicas, rolling deployment |
| **RDS MySQL 8.0** | `db.t3.micro`, Multi-AZ in private subnets |
| **ElastiCache Redis** | Managed Redis cluster for cache/session/queue |
| **IAM Task Execution Role** | Allows ECS to pull images and write CloudWatch logs |
| **IAM Task Role** | Application-level AWS permissions (least-privilege) |
| **SSM Parameter Store** | Secure storage for DB passwords and app secrets |
| **Security Groups** | ALB (public :80), ECS tasks (from ALB only), RDS (from ECS only), ElastiCache (from ECS only) |

**Remote State Backend:**
```hcl
backend "s3" {
  bucket         = "automatecrm-terraform-state"
  key            = "infrastructure/terraform.tfstate"
  region         = "ap-southeast-1"
  dynamodb_table = "automatecrm-terraform-lock"
  encrypt        = true
}
```

**Default region:** `ap-southeast-1` (Singapore)

### Terraform Usage

```bash
# 1. Initialize — downloads providers, configures S3 backend
make terraform-init
# or: cd terraform && terraform init -var-file=staging.tfvars

# 2. Preview changes (dry run)
make terraform-plan
# or: cd terraform && terraform plan -var-file=staging.tfvars

# 3. Apply infrastructure
make terraform-apply
# or: cd terraform && terraform apply -var-file=staging.tfvars

# 4. View outputs (ALB DNS, RDS endpoint, etc.)
make terraform-output

# 5. Destroy all resources (CAUTION — irreversible)
make terraform-destroy
```

**Environment files:**
- `terraform/staging.tfvars` — Staging environment (smaller instances, lower cost)
- Create `terraform/production.tfvars` for production with larger instance sizes

**Required variables (must be supplied or in `.tfvars`):**

| Variable | Description | Default |
|---|---|---|
| `aws_region` | AWS region | `ap-southeast-1` |
| `environment` | `staging` or `production` | `staging` |
| `app_name` | Application name | `automatecrm` |
| `vpc_cidr` | VPC IP range | `10.0.0.0/16` |
| `db_instance_class` | RDS instance class | `db.t3.micro` |
| `db_password` | RDS master password | *(required, no default)* |
| `ecs_task_cpu` | ECS task CPU units | `512` |
| `ecs_task_memory` | ECS task memory (MiB) | `1024` |
| `desired_count` | Number of ECS replicas | `2` |

---

## Getting Started Locally

### Prerequisites

| Tool | Version | Check |
|---|---|---|
| Docker Desktop | 24+ | `docker -v` |
| Docker Compose v2 | Built-in | `docker compose version` |
| Git | Latest | `git -v` |
| PHP (optional) | >= 8.2 | `php -v` |
| Composer (optional) | 2.x | `composer -V` |
| Node.js (optional) | >= 18 | `node -v` |

### Quick Start with Docker

```bash
# 1. Clone the repository
git clone https://github.com/rafiimafif/automatecrm-cicd-pipeline.git
cd automatecrm-cicd-pipeline

# 2. Build and start the full stack (production)
docker compose up -d --build

# 3. Run database migrations
docker compose exec app php artisan migrate --seed

# 4. Warm application caches
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache

# App:             http://localhost:8080
# phpMyAdmin:      http://localhost:8081
# Redis Commander: http://localhost:8082
# Login:           admin@admin.com / password
```

### Manual Setup (without Docker)

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Set your database credentials in .env, then migrate
php artisan migrate --seed

# 5. Build frontend assets
npm run build

# 6. Start the dev server
php artisan serve
# App: http://localhost:8000
```

---

## Makefile Reference

Run `make help` to list all commands. Key groups:

### Development

| Command | Description |
|---|---|
| `make install` | Install PHP + Node dependencies |
| `make dev` | Start development server with Vite HMR |
| `make build` | Build frontend assets for production |
| `make fresh` | Drop all tables, re-migrate, re-seed |

### Testing & Quality

| Command | Description |
|---|---|
| `make test` | Run PHPUnit test suite |
| `make test-coverage` | Run tests with HTML coverage report |
| `make lint` | Fix code style with Laravel Pint |
| `make lint-check` | Check code style without fixing |
| `make sonar` | Run SonarQube scanner locally |
| `make security-scan` | Run `composer audit` + `npm audit` |
| `make trivy-scan` | Scan Docker image for CVEs with Trivy |

### Docker

| Command | Description |
|---|---|
| `make docker-build` | Build Docker production image |
| `make docker-up` | Start production containers |
| `make docker-up-dev` | Start containers with dev tools |
| `make docker-down` | Stop and remove all containers |
| `make docker-logs` | Tail all container logs |
| `make docker-shell` | Open bash shell in `app` container |
| `make docker-test` | Run tests inside the container |
| `make docker-push` | Build and push image to registry |

### Infrastructure

| Command | Description |
|---|---|
| `make terraform-init` | Initialize Terraform (downloads providers) |
| `make terraform-plan` | Preview infrastructure changes |
| `make terraform-apply` | Create/update AWS infrastructure |
| `make terraform-output` | Show provisioned resource endpoints |
| `make terraform-destroy` | Destroy all AWS resources |

### Database

| Command | Description |
|---|---|
| `make db-dump` | Dump database to timestamped `.sql.gz` |
| `make db-restore file=backup.sql` | Restore database from backup file |

---

## Shell Scripts

Located in `scripts/`:

### `docker-setup.sh`

First-time automated setup:
1. Checks Docker and Docker Compose are installed
2. Creates `.env` from template with Docker-specific defaults
3. Builds and starts containers (`docker compose up -d --build`)
4. Waits for MySQL to become healthy (retries with backoff)
5. Generates application key (`php artisan key:generate`)
6. Runs database migrations (`php artisan migrate --seed`)
7. Warms Laravel caches (config, route)

```bash
bash scripts/docker-setup.sh dev    # Start with phpMyAdmin + Redis Commander
bash scripts/docker-setup.sh prod   # Start production stack only
```

### `deploy.sh`

SSH-based remote deployment:
1. Connects to staging server via SSH
2. Pulls updated `docker-compose.yml` via `scp`
3. Pulls new Docker image on the remote host
4. Restarts containers with zero-downtime (`--no-deps`)
5. Runs `php artisan migrate --force`
6. Rebuilds config and route caches
7. Performs HTTP health check against `/api/health`

```bash
bash scripts/deploy.sh staging v1.2.3
bash scripts/deploy.sh production latest
```

### `backup-db.sh`

Automated database backup:
1. Auto-detects Docker or local MySQL
2. Creates compressed `.sql.gz` with timestamp
3. Optionally uploads to S3 (`BACKUP_S3_BUCKET` env var)
4. Prunes backups older than 30 days

```bash
bash scripts/backup-db.sh
```

---

## Environment Variables

Key environment variables used across the Docker stack:

| Variable | Default | Description |
|---|---|---|
| `APP_NAME` | `automateCRM` | Application display name |
| `APP_ENV` | `production` | Environment (`local`, `staging`, `production`) |
| `APP_KEY` | *(generated)* | Laravel 32-byte encryption key |
| `APP_DEBUG` | `false` | Enable/disable debug mode |
| `APP_URL` | `http://localhost` | Public application URL |
| `APP_PORT` | `8080` | Host port for the app container |
| `DB_HOST` | `db` | Database hostname (Docker service name) |
| `DB_PORT` | `3306` | Database port |
| `DB_DATABASE` | `automatecrm` | Database name |
| `DB_USERNAME` | `automatecrm` | Database user |
| `DB_PASSWORD` | `secret` | Database password |
| `DB_ROOT_PASSWORD` | `rootsecret` | MySQL root password |
| `REDIS_HOST` | `redis` | Redis hostname (Docker service name) |
| `REDIS_PORT` | `6379` | Redis port |
| `CACHE_DRIVER` | `redis` | Cache backend |
| `SESSION_DRIVER` | `redis` | Session backend |
| `QUEUE_CONNECTION` | `redis` | Queue backend |
| `MAIL_MAILER` | `smtp` | Mail driver |
| `MAIL_HOST` | `smtp.mailtrap.io` | SMTP server |
| `MAIL_PORT` | `2525` | SMTP port |
| `SONAR_TOKEN` | — | SonarCloud API token (Jenkins credential) |

---

## Project Structure

```
automatecrm-cicd-pipeline/
├── app/                          # Laravel application code
│   ├── Console/Commands/         # Artisan commands (email reminders)
│   ├── Exports/                  # Excel export classes (Maatwebsite)
│   ├── Http/Controllers/         # Route controllers
│   ├── Imports/                  # Excel import classes
│   ├── Mail/                     # Mailable classes (email templates)
│   ├── Models/                   # Eloquent models
│   ├── Providers/                # Service providers
│   └── Services/                 # Business logic (RenewalService)
│
├── docker/
│   ├── jenkins/
│   │   └── Dockerfile            # Jenkins LTS + Docker CLI + Compose plugin
│   ├── nginx/
│   │   └── default.conf          # Nginx → PHP-FPM reverse proxy config
│   └── supervisor/
│       └── supervisord.conf      # Supervisor: manages Nginx + PHP-FPM
│
├── scripts/
│   ├── docker-setup.sh           # First-time Docker setup automation
│   ├── deploy.sh                 # SSH deployment to remote server
│   └── backup-db.sh              # Database backup + S3 upload
│
├── terraform/
│   ├── main.tf                   # Provider config + S3 backend
│   ├── variables.tf              # Input variable definitions
│   ├── networking.tf             # VPC, subnets, IGW, route tables
│   ├── ecs.tf                    # ECS, ALB, RDS, ElastiCache, IAM, SGs
│   ├── outputs.tf                # ALB DNS, RDS endpoint outputs
│   └── staging.tfvars            # Staging environment variable values
│
├── tests/
│   ├── Feature/                  # HTTP & integration tests
│   └── Unit/                     # Unit tests
│
├── docs/
│   ├── DEVOPS.md                 # Extended DevOps & infrastructure guide
│   ├── SETUP.md                  # Local setup guide
│   └── FULL_DOCUMENTATION.md     # Complete end-to-end documentation
│
├── Dockerfile                    # 3-stage production image build
├── docker-compose.yml            # Full stack (7 services incl. dev tools)
├── docker-compose.jenkins.yml    # Jenkins CI server
├── Jenkinsfile                   # Declarative Jenkins pipeline
├── Makefile                      # Developer command shortcuts
├── sonar-project.properties      # SonarCloud project configuration
└── phpunit.xml                   # PHPUnit test suite configuration
```

---

## Contact

**Rafii Muhammad Afif**

- LinkedIn: [rafii-muhammad-afif](https://www.linkedin.com/in/rafii-muhammad-afif/)
- Email: rafii.afif@gmail.com
- Portfolio: [rafii-afif.vercel.app](https://rafii-afif.vercel.app)
- GitHub: [github.com/rafiimafif](https://github.com/rafiimafif)

Project: [github.com/rafiimafif/automatecrm-cicd-pipeline](https://github.com/rafiimafif/automatecrm-cicd-pipeline)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

<!-- MARKDOWN LINKS & IMAGES -->
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://www.linkedin.com/in/rafii-muhammad-afif/
[product-screenshot]: public/img/demo.png
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com
