.PHONY: bash-root composer-install help install start stop restart add-migrations quality-checks assets dev encoredev encoreprod stopwatch npm-install
.DEFAULT_GOAL := help

CONTAINER_NAME = symfony-market-price-list
DOCKER_CONTAINER_ID := $(shell docker ps -qf "name=^/$(CONTAINER_NAME)$$")

DOCKER=docker exec -ti $(DOCKER_CONTAINER_ID)
DOCKER_ROOT := docker exec -ti --user root $(DOCKER_CONTAINER_ID)

NO_COLOR=\033[39m
OK_COLOR=\033[92m
OK_STRING=$(OK_COLOR)[OK]$(OK_COLOR) 😀

PHP_CSFIXER_BIN = vendor/bin/php-cs-fixer
PHP_INSIGHTS_BIN = vendor/bin/phpinsights
PHPSTAN_BIN = vendor/bin/phpstan
PHPCS_BIN = vendor/bin/phpcs
PHPCS_CODESNIFFERFIX_BIN = vendor/bin/phpcbf
PSALM_BIN = vendor/bin/psalm

get-container-id:
	@echo "Container ID: $(DOCKER_CONTAINER_ID)"

logs:
	docker logs $(DOCKER_CONTAINER_ID)

bash: ## Enter container as root
	docker exec -ti $(DOCKER_CONTAINER_ID) sh

bash-root: ## Enter container as root
	$(DOCKER_ROOT) sh

composer-install:
	$(DOCKER_ROOT) composer install

cache-clear:
	$(DOCKER_ROOT) php bin/console cache:clear --no-warmup
	$(DOCKER_ROOT) php bin/console cache:warmup

phpunit:
	$(DOCKER_ROOT) vendor/bin/phpunit

quality-checks: phpcs sniff phpstan psalm phpinsights

phpcs-fix-dry-run:
	$(DOCKER_ROOT) $(PHP_CSFIXER_BIN) fix --dry-run

phpcs-fix:
	$(DOCKER_ROOT) $(PHP_CSFIXER_BIN) fix

codesniffer-check:
	$(DOCKER_ROOT) $(PHPCS_BIN) --standard=phpcs.xml

codesniffer-fix:
	$(DOCKER_ROOT) $(PHPCS_CODESNIFFERFIX_BIN) --standard=phpcs.xml

phpstan:
	$(DOCKER_ROOT) $(PHPSTAN_BIN) analyse

psalm:
	$(DOCKER_ROOT) $(PSALM_BIN) --show-info=true

phpinsights:
	$(DOCKER_ROOT) $(PHP_INSIGHTS_BIN)

start: ## Start the project
	docker compose up -d --build
	@echo "$(OK_STRING) The web app should be accessible on http://localhost:8000"

stop: ## Stop the project
	docker compose down --remove-orphans

restart: stop start

add-migrations:
	$(DOCKER_ROOT) bin/console doctrine:migrations:migrate --no-interaction

add-fixtures:
	$(DOCKER_ROOT) bin/console doctrine:fixtures:load --no-interaction

npm-install: ## Install nodejs dependencies
	$(DOCKER_ROOT) npm install

assets: ## Build dev assets
	$(DOCKER_ROOT) npm run dev

encoredev: ## Build dev assets using Encore
	$(DOCKER_ROOT) ./node_modules/.bin/encore dev

encoreprod: ## Build production assets using Encore
	$(DOCKER_ROOT) ./node_modules/.bin/encore production

stopwatch: ## Watch and build assets in real-time using Encore
	$(DOCKER_ROOT) ./node_modules/.bin/encore dev --watch

consumer-logs:
	docker exec -ti $(DOCKER_CONTAINER_ID) tail -f /var/log/supervisor/messenger_consumer.log

webserver-logs:
	docker exec -ti $(DOCKER_CONTAINER_ID) tail -f /var/log/supervisor/php.log

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
