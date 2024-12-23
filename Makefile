DOCKER_COMPOSE = docker-compose
EXEC_PHP       = $(DOCKER_COMPOSE) exec php-apache
DOCKER_COMPOSE_FILE = -f docker-compose.yml

local-build: local-compose-file dc-build init-local
init-local: dc-down dc-up dc-certbot composer-i migrate

local-compose-file:
	$(eval DOCKER_COMPOSE_FILE = -f docker-compose.yml)

dev-compose-file:
	$(eval DOCKER_COMPOSE_FILE = -f docker-compose.base.yml -f docker-compose.dev.yml )

dc-build:
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_FILE) build postgres php-apache

dc-up:
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_FILE) up -d postgres php-apache

dc-down:
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_FILE) down --remove-orphans

dc-certbot:
	$(EXEC_PHP) sh -c "cd /etc/apache2/ssl; sh -c ./generate-ssl.sh"

bash:
	$(EXEC_PHP) bash

composer-i:
	$(EXEC_PHP) sh -c "cd api; composer update"

clear-cache:
	$(EXEC_PHP) sh -c "cd api; php bin/console cache:clear; rm -rf var/cache"

migrate:
	$(EXEC_PHP) sh -c "cd api;  php bin/console doctrine:migrations:migrate --no-interaction"

migrate-diff:
	$(EXEC_PHP) sh -c "cd api;  php bin/console doctrine:migrations:diff"
