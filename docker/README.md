# Nextcloud Development Environment

## Installation / Running

```bash
make build
docker compose up
```

Afterward you should be able to open <http://localhost:8081/index.php/apps/news> (admin/admin) to
log in to your Nextcloud instance.

## Check nextcloud.log

For debugging, you can show the `nextcloud.log`:

```bash
make show-log
```

There also is a [logging web interface](http://localhost:8081/index.php/settings/admin/logging).

## Tip

In case something is broken try to reset the container:

```bash
docker compose build; docker compose down; docker volume rm nextcloud-news_nextcloud
```
