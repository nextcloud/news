# DevContainer

This project uses a Nextcloud development container configured in [devcontainer.json](.devcontainer/devcontainer.json).

Image used for this container:
https://github.com/Grotax/nextcloud-news-devcontainer

The image is based on:
https://github.com/juliusknorr/nextcloud-dev

Most setup is automatic when you open the project in the container. On attach, [setup.sh](.devcontainer/setup.sh) bootstraps Nextcloud and installs dependencies.

The most important setting is NEXTCLOUD_VERSION in [devcontainer.json](.devcontainer/devcontainer.json), which lets you choose the Nextcloud version, if you use `master` the master branch of the server will be used.

## Visual Studio Code

For Dev Containers usage and configuration, see the official documentation:
https://code.visualstudio.com/docs/devcontainers/containers
