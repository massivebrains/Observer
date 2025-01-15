include ../Makefile.global
.PHONY: build build-dev start stop bash logs status refresh-ide-helper lint-code lint-fix

ifndef DOCKER_USER
override DOCKER_USER = $(shell id -u):$(shell id -g)
export DOCKER_USER
endif

MAKEPATH := $(abspath $(lastword $(MAKEFILE_LIST)))
PWD := $(dir $(MAKEPATH))
SRCDIR := ./src
COVERAGE := php -dpconv.enabled=1 -dpcov.directory=app vendor/bin/phpunit
TTY_PARAM := $(shell tty > /dev/null && echo "" || echo "-T")
WINPTY := $(shell command -v winpty && echo "winpty " ||  echo "")
NETWORK := $(shell docker network ls | grep soci | sed 's/ \{1,\}/ /g' | cut -d " " -f 2 | cut -d "_" -f 1)

ifeq ($(SOCI_NETWORK),)
	SOCI_NETWORK=$(NETWORK)
	export SOCI_NETWORK
endif

build:
	docker compose down
	docker compose rm
	docker compose build

start:
	docker compose up -d

stop:
	docker compose down -t 1

restart: stop start

bash shell:
	$(WINPTY)docker compose exec $(TTY_PARAM) status-monitoring sh

logs:
	docker compose logs status-monitoring

followlogs:
	docker compose logs -f status-monitoring

install composer composer-install:
	$(WINPTY)docker compose exec $(TTY_PARAM) status-monitoring sh -c "composer install"

test tests units phpunit:
	$(WINPTY)docker compose exec $(TTY_PARAM) status-monitoring sh -c "$(COVERAGE) --coverage-text"

coverage test-coverage tests-coverage units-coverage phpunit-coverage:
	$(WINPTY)docker compose exec $(TTY_PARAM) status-monitoring sh -c "$(COVERAGE) --coverage-html coverage-report"

refresh-ide-helper:
	$(WINPTY)docker compose exec $(TTY_PARAM) status-monitoring sh -c "php artisan ide-helper:generate"
	$(WINPTY)docker compose exec $(TTY_PARAM) status-monitoring sh -c "php artisan ide-helper:meta"

test-setup:
	$(WINPTY)docker compose exec $(TTY_PARAM) status-monitoring sh -c "./artisan test:setup"

status:
	docker compose ps

lint-code:
	$(WINPTY)docker-compose exec $(TTY_PARAM) status-monitoring sh -c "php ./vendor/bin/phpcs"

lint-fix:
	$(WINPTY)docker-compose exec $(TTY_PARAM) status-monitoring sh -c "php ./vendor/bin/phpcbf"

api-docs:
	rm -rf docs/openapi/status-monitoring.internal.meetsoci.com/*
	$(WINPTY)docker-compose exec $(TTY_PARAM) status-monitoring sh -c "php artisan scribe:generate -n"
	mv docs/openapi/status-monitoring.internal.meetsoci.com/openapi.yaml docs/openapi/status-monitoring.internal.meetsoci.com/status-monitoring-openapi.yaml
