# Dev Containers

## Image Used in This Project

This project uses a Nextcloud Docker image for the development container. The image is defined in the `.devcontainer.json` configuration file. For more details about the image and its configuration, you can refer to the [GitHub repository](https://github.com/juliusknorr/nextcloud-dev).

# VS Code

## Introduction to Dev Containers

Dev Containers are a feature in Visual Studio Code that allows you to develop inside a containerized environment. This ensures that your development environment is consistent across different machines and setups. By using Dev Containers, you can easily share your development environment with your team, ensuring that everyone is working with the same dependencies and configurations.

## How to Use Dev Containers with VS Code

1. **Install Docker**: Make sure you have Docker installed on your machine. You can download it from [Docker's official website](https://www.docker.com/products/docker-desktop).

2. **Install VS Code**: If you haven't already, download and install Visual Studio Code from [here](https://code.visualstudio.com/).

3. **Install the Remote - Containers Extension**: Open VS Code and go to the Extensions view by clicking on the Extensions icon in the Activity Bar on the side of the window. Search for "Remote - Containers" and install it.

4. **Open a Folder in a Container**:
    - Open your project folder in VS Code.
    - Press `F1` to open the Command Palette, then type `Remote-Containers: Open Folder in Container...` and select it.
    - VS Code will build the container and open your project inside it.


For further information on how to configure and use Dev Containers, please visit the [official documentation](https://code.visualstudio.com/docs/remote/containers).
