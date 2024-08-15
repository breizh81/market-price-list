.PHONY: bash-root composer-install help install start stop restart add-fixtures quality-checks assets dev encoredev encoreprod stopwatch npm-install phpunit phpunit-coverage phpunit-setup phpunit-schema phpunit-fixtures

.DEFAULT_GOAL := help

CONTAINER_NAME = symfony-market-price-list
DOCKER_CONTAINER_ID := $(shell docker ps -qf "name=^/$(CONTAINER_NAME)$$")

DOCKER=docker exec -ti $(DOCKER_CONTAINER_ID)
DOCKER_ROOT := docker exec -ti --user root $(DOCKER_CONTAINER_ID)

NO_COLOR=\033[39m
OK_COLOR=\033[92m
OK_STRING=$(OK_COLOR)[OK]$(OK_COLOR) 😀

PHP_CSFIXER_BIN = vendor/bin/php-cs-fixer
PHPCS_BIN = vendor/bin/phpcs
PHPCS_CODESNIFFERFIX_BIN = vendor/bin/phpcbf

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

start: ## Start the project
	docker compose up -d --build
	@echo "$(OK_STRING) The web app should be accessible on http://localhost:8000"

stop: ## Stop the project
	docker compose down --remove-orphans

restart: stop start

# Development commands
add-migrations:
	$(DOCKER_ROOT) bin/console doctrine:migrations:migrate --no-interaction

add-fixtures:
	$(DOCKER_ROOT) bin/console doctrine:fixtures:load --no-interaction

npm-install: ## Install nodejs dependencies
	$(DOCKER_ROOT) npm install

npm-saas:
	$(DOCKER_ROOT) npm install sass-loader@^14.0.0 sass --save-dev

encoredev: ## Build dev assets using Encore
	$(DOCKER_ROOT) ./node_modules/.bin/encore dev

encorewatch: ## Watch and build assets in real-time using Encore
	$(DOCKER_ROOT) ./node_modules/.bin/encore dev --watch

consumer-logs:
	docker exec -ti $(DOCKER_CONTAINER_ID) tail -f /var/log/supervisor/messenger_consumer.log

webserver-logs:
	docker exec -ti $(DOCKER_CONTAINER_ID) tail -f /var/log/supervisor/php.log

# Testing commands
phpunit: ## Run tests
	$(DOCKER_ROOT) bin/phpunit

phpunit-coverage: ## Run tests with coverage report
	$(DOCKER_ROOT) vendor/bin/phpunit --coverage-html build/coverage

phpunit-schema: ## Set up the test database schema
	$(DOCKER_ROOT) php bin/console doctrine:schema:update --force --env=test

phpunit-fixtures: ## Load fixtures for the test database
	$(DOCKER_ROOT) php bin/console doctrine:fixtures:load --no-interaction --env=test

phpunit-setup: ## Set up the test database schema and load fixtures
	$(DOCKER_ROOT) php bin/console doctrine:schema:update --force --env=test
	$(DOCKER_ROOT) php bin/console doctrine:fixtures:load --no-interaction --env=test

phpcs-fix-dry-run:
	$(DOCKER_ROOT) $(PHP_CSFIXER_BIN) fix --dry-run

phpcs-fix:
	$(DOCKER_ROOT) $(PHP_CSFIXER_BIN) fix

codesniffer-check:
	$(DOCKER_ROOT) $(PHPCS_BIN) --standard=phpcs.xml

codesniffer-fix:
	$(DOCKER_ROOT) $(PHPCS_CODESNIFFERFIX_BIN) --standard=phpcs.xml
