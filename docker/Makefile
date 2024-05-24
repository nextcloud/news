.PHONY: bash check-code show-log test build

# Try to use "docker compose" and fall back to "docker-compose" if not available
DOCKER_COMPOSE := $(shell docker compose > /dev/null && echo docker compose || echo docker-compose)

build:
	$(DOCKER_COMPOSE) build

ls-db:
	$(DOCKER_COMPOSE) run --rm app su -c "ls -hal data/mydb.db*" www-data

fetch-db:
	$(DOCKER_COMPOSE) run --rm app su -c "cp data/mydb.db apps/news" www-data

push-db:
	$(DOCKER_COMPOSE) run --rm app su -c "cp apps/news/mydb.db* data" www-data

bash:
	$(DOCKER_COMPOSE) run --rm app su -c "bash" www-data

check-code:
	$(DOCKER_COMPOSE) run --rm app su -c "./occ app:check-code news" www-data

show-log:
	$(DOCKER_COMPOSE) run --rm app tail -f /var/www/html/data/nextcloud.log

#test:
#	$(DOCKER_COMPOSE) run --rm app su -c "cd custom_apps/news && make test" www-data
