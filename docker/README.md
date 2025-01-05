# Nextcloud Development Environment

## Installation / Running

```bash
make build
docker compose up
```

Afterward you should be able to open <http://localhost:8081/index.php/apps/news> (admin/admin) to
log in to your Nextcloud instance.

Press Ctrl+C to stop the container.

## Check nextcloud.log

For debugging, you can show the `nextcloud.log`:

```bash
make show-log
```

There also is a [logging web interface](http://localhost:8081/index.php/settings/admin/logging).

## Create shell in docker container

To check if the container is still running:
```bash
docker ps
```
It should show something like this:
```bash
CONTAINER ID   IMAGE                  COMMAND                  CREATED         STATUS         PORTS                                       NAMES
a1b2c3d4e5f6   nextcloud-news-app     "docker-entrypoint.…"   2 hours ago     Up 2 hours     0.0.0.0:8081->80/tcp                        nextcloud-news-app
```

To open a shell as www-data which you need to run occ commands run:
``` bash
docker exec -u www-data -it nextcloud-news-app /bin/bash
```

To open a shell as root run
``` bash
docker exec -it nextcloud-news-app /bin/bash
```

To exit press Ctrl+D

###  Inside the shell

Use sqlite3 to open the db

```bash
sqlite3 data/mydb.db
```

More on the sqlite3 cli: https://www.sqlite.org/cli.html

## Tip

In case something is broken try to reset the container:

```bash
docker compose build; docker compose down; docker volume rm nextcloud-news_nextcloud
```
