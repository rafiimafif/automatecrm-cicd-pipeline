# automateCRM — DevOps & Infrastructure Guide

> Complete DevOps toolchain documentation for containerization, CI/CD, infrastructure provisioning, and operational scripts.

---

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Docker](#docker)
  - [Local Development](#local-development)
  - [Production Build](#production-build)
  - [Docker Compose Services](#docker-compose-services)
- [CI/CD Pipelines](#cicd-pipelines)
  - [GitHub Actions](#github-actions)
  - [Jenkins](#jenkins)
- [Code Quality — SonarQube](#code-quality--sonarqube)
- [Infrastructure as Code — Terraform](#infrastructure-as-code--terraform)
  - [AWS Architecture](#aws-architecture)
  - [Usage](#terraform-usage)
- [Makefile Commands](#makefile-commands)
- [Shell Scripts](#shell-scripts)
- [Health Check Endpoint](#health-check-endpoint)
- [Security Scanning](#security-scanning)
- [Environment Variables](#environment-variables)

---

## Architecture Overview

```
┌──────────────────────────────────────────────────────────────┐
│                        GitHub / Git                          │
│  Push → GitHub Actions CI/CD → Build Docker → Deploy         │
└──────────────┬───────────────────────────────┬───────────────┘
               │                               │
       ┌───────▼───────┐              ┌────────▼────────┐
       │  SonarCloud    │              │  GitHub GHCR    │
       │  Code Quality  │              │  Container Reg  │
       └───────────────┘              └────────┬────────┘
                                               │
                            ┌──────────────────▼──────────────┐
                            │       AWS ECS Fargate            │
                            │  ┌─────────┐  ┌──────────────┐  │
                            │  │ ALB     │→ │ ECS Service   │  │
                            │  │ :443    │  │ (PHP+Nginx)   │  │
                            │  └─────────┘  └──────┬───────┘  │
                            │                      │           │
                            │  ┌──────────┐ ┌──────▼───────┐  │
                            │  │ ElastiCache│ │  RDS MySQL   │  │
                            │  │ (Redis)   │ │  8.0         │  │
                            │  └──────────┘ └──────────────┘  │
                            └──────────────────────────────────┘
```

---

## Docker

### Local Development

Start the full stack with development tools:

```bash
# First-time setup (creates .env, builds, migrates, seeds)
bash scripts/docker-setup.sh dev

# Or use Make
make docker-up-dev
```

This starts:

| Service      | URL                     | Description                |
|-------------|-------------------------|----------------------------|
| App          | http://localhost:8000   | automateCRM application       |
| phpMyAdmin   | http://localhost:8081   | Database management UI     |
| Redis Cmd    | http://localhost:8082   | Redis data browser         |
| Mailhog      | http://localhost:8025   | Email testing (catches all) |

Default login: `admin@admin.com` / `password`

### Production Build

The Dockerfile uses a **multi-stage build** for optimized images:

```
Stage 1: node:18-alpine      → Builds frontend (Vite + Vue.js)
Stage 2: composer:2           → Installs PHP dependencies
Stage 3: php:8.2-fpm-alpine   → Production runtime (~150MB)
          ├── Nginx (reverse proxy)
          ├── PHP-FPM (app server)
          └── Supervisor (process manager)
```

```bash
# Build image
make docker-build

# Build & push to GHCR
make docker-push
```

### Docker Compose Services

**docker-compose.yml** (production):
- `app` — Laravel + Nginx + PHP-FPM (port 8000)
- `mysql` — MySQL 8.0 with persistent volume
- `redis` — Redis 7-alpine with persistent volume
- `queue` — Laravel queue worker
- `scheduler` — Laravel task scheduler (cron)
- `mailhog` — Email trap for testing

**docker-compose.dev.yml** (development overlay):
- `phpmyadmin` — Database admin UI (port 8081)
- `redis-commander` — Redis GUI (port 8082)
- Source code mounted for live reload

---

## CI/CD Pipelines

### GitHub Actions

**File:** `.github/workflows/ci-cd.yml`

6-stage pipeline triggered on push to `main`/`develop` and PRs:

```
┌───────┐    ┌──────┐    ┌──────────┐    ┌───────────┐    ┌───────────────┐    ┌────────┐
│ Lint  │ →  │ Test │ →  │ Security │ →  │ SonarCloud│ →  │ Build & Push  │ →  │ Deploy │
│ Pint  │    │ PHPUnit│  │ Audit    │    │ Analysis  │    │ Docker → GHCR │    │ SSH    │
└───────┘    └──────┘    └──────────┘    └───────────┘    └───────────────┘    └────────┘
```

| Stage          | Description                                                |
|---------------|------------------------------------------------------------|
| **Lint**       | Laravel Pint code style check                             |
| **Test**       | PHPUnit on PHP 8.1 & 8.2 matrix, MySQL + Redis services   |
| **Security**   | `composer audit` + `npm audit`                             |
| **SonarCloud** | Code quality & coverage analysis                           |
| **Build**      | Multi-stage Docker build → push to GitHub Container Registry + Trivy vulnerability scan |
| **Deploy**     | SSH deploy to staging server (main branch only)            |

**Required GitHub Secrets:**

| Secret               | Description                          |
|---------------------|--------------------------------------|
| `SONAR_TOKEN`        | SonarCloud authentication token      |
| `STAGING_HOST`       | Staging server hostname/IP           |
| `STAGING_USER`       | SSH username for staging             |
| `STAGING_SSH_KEY`    | SSH private key for deployment       |
| `STAGING_DEPLOY_PATH`| Application path on staging server  |

### Jenkins

**File:** `Jenkinsfile`

Declarative pipeline with parallel stages for environments with Jenkins:

- Checkout → Parallel(Lint, Test, Security Audit) → SonarQube Analysis → Quality Gate → Docker Build → Trivy Scan → Deploy to Staging
- Uses Jenkins shared credentials for SonarQube and Docker registry

---

## Code Quality — SonarQube

**File:** `sonar-project.properties`

Configured for **SonarCloud** analysis:

```properties
sonar.organization=rafiimafif
sonar.projectKey=rafiimafif_automateCRM
sonar.sources=app,resources,routes,config,database
sonar.tests=tests
sonar.php.coverage.reportPaths=coverage/clover.xml
```

**Run locally:**

```bash
make sonar
```

**Exclusions:** vendor, node_modules, public/vendor, storage, bootstrap/cache, compiled assets

---

## Infrastructure as Code — Terraform

**Directory:** `terraform/`

### AWS Architecture

Provisions a complete AWS environment using ECS Fargate:

| Resource             | Description                                    |
|---------------------|------------------------------------------------|
| **VPC**              | Custom VPC with public/private subnets in 2 AZs |
| **ALB**              | Application Load Balancer (HTTP → ECS)         |
| **ECS Fargate**      | Serverless container orchestration              |
| **RDS MySQL 8.0**    | Managed database in private subnet             |
| **ElastiCache Redis**| Managed Redis for cache/session/queue          |
| **IAM Roles**        | Task execution + task roles with least privilege|
| **SSM Parameters**   | Secure storage for secrets                      |
| **Security Groups**  | Network-level access control                    |

### Terraform Usage

```bash
# Initialize (downloads providers, configures S3 backend)
make terraform-init

# Preview changes
make terraform-plan

# Apply infrastructure
make terraform-apply

# View outputs (ALB DNS, RDS endpoint, etc.)
make terraform-output

# Destroy (use with caution)
make terraform-destroy
```

**Environment files:**
- `terraform/staging.tfvars` — Staging (smaller instances, lower cost)
- Create `terraform/production.tfvars` for production settings

**State management:** Stored in S3 bucket with DynamoDB locking.

---

## Makefile Commands

Run `make help` to see all available commands. Key commands:

### Development
| Command             | Description                           |
|--------------------|---------------------------------------|
| `make install`      | Install PHP + Node dependencies       |
| `make dev`          | Start local dev server + Vite HMR     |
| `make build`        | Build frontend assets for production  |
| `make fresh`        | Fresh migrate & seed database         |

### Testing & Quality
| Command             | Description                           |
|--------------------|---------------------------------------|
| `make test`         | Run PHPUnit test suite                |
| `make test-coverage`| Run tests with coverage report       |
| `make lint`         | Fix code style with Laravel Pint      |
| `make lint-check`   | Check code style (no fixes)           |

### Docker
| Command             | Description                           |
|--------------------|---------------------------------------|
| `make docker-up`    | Start production containers           |
| `make docker-up-dev`| Start dev containers (with debug tools)|
| `make docker-down`  | Stop all containers                   |
| `make docker-build` | Build Docker image                    |
| `make docker-logs`  | Tail container logs                   |
| `make docker-shell` | Shell into app container              |
| `make docker-test`  | Run tests inside container            |

### Infrastructure
| Command               | Description                         |
|----------------------|-------------------------------------|
| `make terraform-init`  | Initialize Terraform               |
| `make terraform-plan`  | Preview infrastructure changes     |
| `make terraform-apply` | Apply infrastructure               |
| `make security-scan`   | Run dependency security audits     |
| `make trivy-scan`      | Scan Docker image vulnerabilities  |

### Database
| Command             | Description                           |
|--------------------|---------------------------------------|
| `make db-dump`      | Dump database to timestamped file     |
| `make db-restore file=backup.sql` | Restore from backup    |

---

## Shell Scripts

Located in `scripts/`:

| Script              | Description                                    | Usage                          |
|--------------------|------------------------------------------------|--------------------------------|
| `docker-setup.sh`   | First-time Docker environment setup            | `bash scripts/docker-setup.sh [dev\|prod]` |
| `deploy.sh`         | Deploy to remote server via SSH                | `bash scripts/deploy.sh [staging\|production] [tag]` |
| `backup-db.sh`      | Database backup with optional S3 upload        | `bash scripts/backup-db.sh`    |

### docker-setup.sh

Automates first-time setup:
1. Checks Docker prerequisites
2. Creates `.env` from template with Docker-specific values
3. Builds and starts containers
4. Waits for MySQL readiness
5. Generates app key, runs migrations, seeds database
6. Warms Laravel caches

### deploy.sh

SSH-based deployment:
1. Pulls the Docker image on the remote server
2. Syncs docker-compose.yml
3. Restarts containers with new image
4. Runs migrations
5. Rebuilds caches
6. Performs health check

### backup-db.sh

Database backup utility:
- Detects Docker or local MySQL automatically
- Creates compressed `.sql.gz` backups
- Optionally uploads to S3 (`BACKUP_S3_BUCKET` env)
- Cleans backups older than retention period (default: 30 days)

---

## Health Check Endpoint

**Route:** `GET /api/health`

Returns application and database status. Used by Docker, ALB, and CI/CD for readiness probes.

```json
// Healthy (200)
{
  "status": "ok",
  "timestamp": "2024-01-15T10:30:00+00:00",
  "database": "connected"
}

// Unhealthy (503)
{
  "status": "ok",
  "timestamp": "2024-01-15T10:30:00+00:00",
  "database": "disconnected"
}
```

---

## Security Scanning

Multiple layers of security scanning are integrated:

| Tool              | What it scans                    | Where it runs         |
|------------------|----------------------------------|-----------------------|
| `composer audit`  | PHP dependency vulnerabilities   | CI + local (`make security-scan`) |
| `npm audit`       | Node dependency vulnerabilities  | CI + local            |
| **Trivy**         | Docker image CVEs                | CI (GitHub Actions)   |
| **SonarCloud**    | Code smells, bugs, vulnerabilities| CI + local (`make sonar`) |

---

## Environment Variables

Key environment variables for Docker deployment:

| Variable              | Default          | Description                |
|----------------------|------------------|----------------------------|
| `APP_NAME`            | automateCRM         | Application name           |
| `APP_ENV`             | production       | Environment                |
| `APP_KEY`             | (generated)      | Laravel encryption key     |
| `DB_HOST`             | mysql            | Database hostname          |
| `DB_DATABASE`         | automatecrm         | Database name              |
| `DB_USERNAME`         | automatecrm         | Database user              |
| `DB_PASSWORD`         | secret           | Database password          |
| `REDIS_HOST`          | redis            | Redis hostname             |
| `CACHE_DRIVER`        | redis            | Cache backend              |
| `SESSION_DRIVER`      | redis            | Session backend            |
| `QUEUE_CONNECTION`    | redis            | Queue backend              |
| `MAIL_MAILER`         | smtp             | Mail driver                |
| `MAIL_HOST`           | mailhog          | Mail server (dev)          |

---

## File Structure

```
├── .github/
│   └── workflows/
│       └── ci-cd.yml              # GitHub Actions pipeline
├── docker/
│   ├── nginx/
│   │   └── default.conf           # Nginx configuration
│   └── supervisor/
│       └── supervisord.conf       # Process manager config
├── scripts/
│   ├── docker-setup.sh            # First-time Docker setup
│   ├── deploy.sh                  # SSH deployment script
│   └── backup-db.sh              # Database backup utility
├── terraform/
│   ├── main.tf                    # Provider & backend config
│   ├── variables.tf               # Variable definitions
│   ├── networking.tf              # VPC, subnets, routing
│   ├── ecs.tf                     # ECS, ALB, RDS, ElastiCache
│   ├── outputs.tf                 # Output values
│   └── staging.tfvars             # Staging environment vars
├── Dockerfile                     # Multi-stage production build
├── .dockerignore                  # Docker build exclusions
├── docker-compose.yml             # Production compose
├── docker-compose.dev.yml         # Development overlay
├── Jenkinsfile                    # Jenkins pipeline
├── Makefile                       # Developer commands
└── sonar-project.properties       # SonarQube configuration
```

---

**Author:** Rafii Muhammad Afif  
**Repository:** [github.com/rafiimafif/automateCRM](https://github.com/rafiimafif/automateCRM)
