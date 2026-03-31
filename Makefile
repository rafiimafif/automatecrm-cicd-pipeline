# ============================================================================
# automateCRM - Development & DevOps Makefile
# ============================================================================
# Usage: make <target>
# Run `make help` to see all available commands.
# ============================================================================

.PHONY: help install dev build test lint clean docker-up docker-down docker-build \
        docker-logs docker-shell migrate seed fresh deploy-staging deploy-prod \
        sonar security-scan terraform-init terraform-plan terraform-apply \
        backup db-dump queue-work schedule-run

.DEFAULT_GOAL := help

# ──────────────────────────────────────────────────────────────────────────────
# Variables
# ──────────────────────────────────────────────────────────────────────────────
APP_NAME        := automatecrm
DOCKER_IMAGE    := ghcr.io/rafiimafif/$(APP_NAME)
DOCKER_TAG      := $(shell git rev-parse --short HEAD 2>/dev/null || echo "latest")
COMPOSE         := docker compose
COMPOSE_DEV     := $(COMPOSE) -f docker-compose.yml -f docker-compose.dev.yml
PHP_EXEC        := $(COMPOSE) exec app php
ARTISAN         := $(PHP_EXEC) artisan
TF_DIR          := terraform

# Colors for output
CYAN  := \033[36m
GREEN := \033[32m
YELLOW := \033[33m
RESET := \033[0m

# ──────────────────────────────────────────────────────────────────────────────
# Help
# ──────────────────────────────────────────────────────────────────────────────
help: ## Show this help message
	@echo ""
	@echo "$(CYAN)automateCRM$(RESET) - Development & DevOps Commands"
	@echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-22s$(RESET) %s\n", $$1, $$2}'
	@echo ""

# ──────────────────────────────────────────────────────────────────────────────
# Local Development
# ──────────────────────────────────────────────────────────────────────────────
install: ## Install all dependencies (PHP + Node)
	composer install
	npm ci
	cp -n .env.example .env || true
	php artisan key:generate --no-interaction
	@echo "$(GREEN)✓ Dependencies installed$(RESET)"

dev: ## Start local development server with Vite HMR
	php artisan serve & npm run dev

build: ## Build frontend assets for production
	npm run build
	@echo "$(GREEN)✓ Frontend assets built$(RESET)"

migrate: ## Run database migrations
	php artisan migrate
	@echo "$(GREEN)✓ Migrations complete$(RESET)"

seed: ## Seed the database
	php artisan db:seed
	@echo "$(GREEN)✓ Database seeded$(RESET)"

fresh: ## Drop all tables & re-run migrations with seeders
	php artisan migrate:fresh --seed
	@echo "$(GREEN)✓ Fresh database ready$(RESET)"

queue-work: ## Start the queue worker
	php artisan queue:work --tries=3 --timeout=90

schedule-run: ## Run the scheduler once
	php artisan schedule:run

# ──────────────────────────────────────────────────────────────────────────────
# Testing & Quality
# ──────────────────────────────────────────────────────────────────────────────
test: ## Run PHPUnit test suite
	php artisan test --parallel
	@echo "$(GREEN)✓ Tests passed$(RESET)"

test-coverage: ## Run tests with code coverage report
	php artisan test --coverage --min=60
	@echo "$(GREEN)✓ Coverage report generated$(RESET)"

lint: ## Run PHP CS Fixer (Laravel Pint)
	./vendor/bin/pint
	@echo "$(GREEN)✓ Code formatted$(RESET)"

lint-check: ## Check code style without fixing
	./vendor/bin/pint --test

# ──────────────────────────────────────────────────────────────────────────────
# Security
# ──────────────────────────────────────────────────────────────────────────────
security-scan: ## Run security audits on dependencies
	composer audit
	npm audit --audit-level=high
	@echo "$(GREEN)✓ Security scan complete$(RESET)"

trivy-scan: ## Scan Docker image with Trivy
	trivy image --severity HIGH,CRITICAL $(DOCKER_IMAGE):$(DOCKER_TAG)

# ──────────────────────────────────────────────────────────────────────────────
# Docker
# ──────────────────────────────────────────────────────────────────────────────
docker-up: ## Start all containers (production mode)
	$(COMPOSE) up -d
	@echo "$(GREEN)✓ Containers started$(RESET)"
	@echo "  App:   http://localhost:8000"

docker-up-dev: ## Start all containers (development mode with debug tools)
	$(COMPOSE_DEV) up -d
	@echo "$(GREEN)✓ Dev containers started$(RESET)"
	@echo "  App:         http://localhost:8000"
	@echo "  phpMyAdmin:  http://localhost:8081"
	@echo "  Redis Cmd:   http://localhost:8082"

docker-down: ## Stop and remove all containers
	$(COMPOSE) down
	@echo "$(GREEN)✓ Containers stopped$(RESET)"

docker-down-clean: ## Stop containers and remove volumes (destroys data)
	$(COMPOSE) down -v
	@echo "$(YELLOW)⚠ Containers stopped & volumes removed$(RESET)"

docker-build: ## Build Docker image
	docker build -t $(DOCKER_IMAGE):$(DOCKER_TAG) .
	docker tag $(DOCKER_IMAGE):$(DOCKER_TAG) $(DOCKER_IMAGE):latest
	@echo "$(GREEN)✓ Image built: $(DOCKER_IMAGE):$(DOCKER_TAG)$(RESET)"

docker-push: ## Push Docker image to registry
	docker push $(DOCKER_IMAGE):$(DOCKER_TAG)
	docker push $(DOCKER_IMAGE):latest
	@echo "$(GREEN)✓ Image pushed$(RESET)"

docker-logs: ## Tail container logs
	$(COMPOSE) logs -f --tail=100

docker-shell: ## Open a shell in the app container
	$(COMPOSE) exec app sh

docker-artisan: ## Run artisan command in container (usage: make docker-artisan cmd="migrate")
	$(ARTISAN) $(cmd)

docker-migrate: ## Run migrations inside container
	$(ARTISAN) migrate
	@echo "$(GREEN)✓ Container migrations complete$(RESET)"

docker-seed: ## Seed database inside container
	$(ARTISAN) db:seed
	@echo "$(GREEN)✓ Container database seeded$(RESET)"

docker-fresh: ## Fresh migrate & seed inside container
	$(ARTISAN) migrate:fresh --seed
	@echo "$(GREEN)✓ Container fresh database ready$(RESET)"

docker-test: ## Run tests inside container
	$(PHP_EXEC) artisan test

# ──────────────────────────────────────────────────────────────────────────────
# SonarQube
# ──────────────────────────────────────────────────────────────────────────────
sonar: ## Run SonarQube analysis locally
	sonar-scanner
	@echo "$(GREEN)✓ SonarQube analysis submitted$(RESET)"

# ──────────────────────────────────────────────────────────────────────────────
# Terraform (Infrastructure)
# ──────────────────────────────────────────────────────────────────────────────
terraform-init: ## Initialize Terraform
	cd $(TF_DIR) && terraform init

terraform-plan: ## Preview infrastructure changes (staging)
	cd $(TF_DIR) && terraform plan -var-file=staging.tfvars

terraform-apply: ## Apply infrastructure changes (staging)
	cd $(TF_DIR) && terraform apply -var-file=staging.tfvars

terraform-destroy: ## Destroy infrastructure (staging) — USE WITH CAUTION
	cd $(TF_DIR) && terraform destroy -var-file=staging.tfvars

terraform-output: ## Show Terraform outputs
	cd $(TF_DIR) && terraform output

# ──────────────────────────────────────────────────────────────────────────────
# Database
# ──────────────────────────────────────────────────────────────────────────────
db-dump: ## Dump database to file (Docker)
	$(COMPOSE) exec mysql mysqldump -u root -ppassword automatecrm > backup-$(shell date +%Y%m%d-%H%M%S).sql
	@echo "$(GREEN)✓ Database dumped$(RESET)"

db-restore: ## Restore database from file (usage: make db-restore file=backup.sql)
	$(COMPOSE) exec -T mysql mysql -u root -ppassword automatecrm < $(file)
	@echo "$(GREEN)✓ Database restored$(RESET)"

# ──────────────────────────────────────────────────────────────────────────────
# Deployment
# ──────────────────────────────────────────────────────────────────────────────
deploy-staging: docker-build docker-push ## Build, push & deploy to staging
	@echo "$(GREEN)✓ Deployed to staging$(RESET)"

deploy-prod: ## Deploy to production (requires confirmation)
	@echo "$(YELLOW)⚠ About to deploy to PRODUCTION$(RESET)"
	@read -p "Are you sure? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	$(COMPOSE) -f docker-compose.yml up -d --build
	@echo "$(GREEN)✓ Deployed to production$(RESET)"

# ──────────────────────────────────────────────────────────────────────────────
# Cleanup
# ──────────────────────────────────────────────────────────────────────────────
clean: ## Remove build artifacts and caches
	rm -rf node_modules vendor public/build
	php artisan cache:clear
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
	@echo "$(GREEN)✓ Cleaned$(RESET)"

cache-clear: ## Clear all Laravel caches
	php artisan cache:clear
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
	php artisan event:clear
	@echo "$(GREEN)✓ All caches cleared$(RESET)"

optimize: ## Optimize Laravel for production
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan event:cache
	@echo "$(GREEN)✓ Optimized for production$(RESET)"
