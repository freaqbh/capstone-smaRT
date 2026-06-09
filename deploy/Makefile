# ┌──────────────────────────────────────────────────────────────┐
# │  smaRT Backend — Deployment Commands                        │
# │                                                              │
# │  Usage: make <command>                                      │
# └──────────────────────────────────────────────────────────────┘

.PHONY: help setup deploy rollback logs ssh status restart stop clean backup

# Default target
help: ## Show this help
	@echo ""
	@echo "  smaRT Backend — Deployment Commands"
	@echo "  ════════════════════════════════════"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'
	@echo ""

# ── Server Setup ─────────────────────────────────────────────────

setup: ## Setup server (first time only, run as root)
	ansible-playbook playbooks/setup-server.yml -u root --ask-pass --ask-vault-pass -vv

# ── Deployment ───────────────────────────────────────────────────

deploy: ## Deploy latest version to server
	ansible-playbook playbooks/deploy.yml --ask-vault-pass

deploy-verbose: ## Deploy with verbose output
	ansible-playbook playbooks/deploy.yml --ask-vault-pass -vv

deploy-check: ## Dry-run deployment (no changes made)
	ansible-playbook playbooks/deploy.yml --ask-vault-pass --check --diff

seed: ## Run database seeders on server
	ansible-playbook playbooks/seed.yml --ask-vault-pass -vv

# ── Rollback ─────────────────────────────────────────────────────

rollback: ## Rollback to previous version
	ansible-playbook playbooks/rollback.yml --ask-vault-pass

rollback-list: ## List available rollback versions
	ansible-playbook playbooks/rollback.yml --ask-vault-pass --tags list

# ── Container Management ─────────────────────────────────────────

logs: ## View app container logs (follow)
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml logs -f --tail=100 app" --become --become-user=$(DEPLOY_USER)

logs-db: ## View database container logs
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml logs -f --tail=100 db" --become --become-user=$(DEPLOY_USER)

status: ## Show container status
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml ps" --become --become-user=$(DEPLOY_USER)

restart: ## Restart app container
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml restart app" --become --become-user=$(DEPLOY_USER)

stop: ## Stop all containers
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml down" --become --become-user=$(DEPLOY_USER)

# ── Utilities ────────────────────────────────────────────────────

ssh: ## SSH into the VPS
	@HOST=$$(grep 'ansible_host' inventory/hosts.yml | head -1 | awk '{print $$2}'); \
	USER=$$(grep 'ansible_user' inventory/hosts.yml | head -1 | awk '{print $$2}'); \
	echo "Connecting to $$USER@$$HOST..."; \
	ssh $$USER@$$HOST

shell: ## Open a shell in the app container
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml exec app sh" --become --become-user=$(DEPLOY_USER)

artisan: ## Run artisan command (usage: make artisan CMD="migrate:status")
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml exec -T app php artisan $(CMD)" --become --become-user=$(DEPLOY_USER)

backup: ## Create database backup
	ansible all -m shell -a "docker compose -f $(PROJECT_ROOT)/deploy/docker-compose.yml exec -T db mysqldump -u root -p\"$$DB_ROOT_PASSWORD\" --all-databases > $(BACKUP_DIR)/db-manual-$$(date +%Y%m%d_%H%M%S).sql" --become --become-user=$(DEPLOY_USER)

# ── Validation ───────────────────────────────────────────────────

lint: ## Check Ansible playbook syntax
	ansible-playbook playbooks/setup-server.yml --syntax-check
	ansible-playbook playbooks/deploy.yml --syntax-check
	ansible-playbook playbooks/rollback.yml --syntax-check
	@echo "✅ All playbooks are syntactically valid"

ping: ## Test connection to server
	ansible all -m ping

# ── Variables ────────────────────────────────────────────────────

DEPLOY_USER ?= deploy
PROJECT_ROOT ?= /home/$(DEPLOY_USER)/smart-backend
BACKUP_DIR ?= /home/$(DEPLOY_USER)/backups
