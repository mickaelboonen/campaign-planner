DOCKER_COMPOSE = docker compose

.PHONY: build up down restart logs shell composer console test

build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

restart:
	$(DOCKER_COMPOSE) restart

logs:
	$(DOCKER_COMPOSE) logs -f

shell:
	$(DOCKER_COMPOSE) exec app bash

composer:
	$(DOCKER_COMPOSE) exec app composer $(filter-out $@,$(MAKECMDGOALS))

console:
	$(DOCKER_COMPOSE) exec app php bin/console $(filter-out $@,$(MAKECMDGOALS))

test:
	$(DOCKER_COMPOSE) exec app php bin/phpunit

%:
	@:
