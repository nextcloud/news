# https://hub.docker.com/_/nextcloud/
FROM nextcloud:30-apache
#FROM ghcr.io/pbek/nextcloud-docker-pre-apache:latest
#FROM ghcr.io/digital-blueprint/nextcloud-docker-pre-apache:latest

COPY entrypoint.sh /

RUN apt-get update && apt-get install -y sqlite3
RUN deluser www-data
RUN useradd -u 1000 -ms /bin/bash www-data
RUN usermod -a -G www-data www-data
RUN mkdir /var/www/deploy
