# Developer

Welcome to the Nextcloud News App developer documentation.

News is open for contributions, if you plan to implement a new feature make sure to open a [discussion](https://github.com/nextcloud/news/discussions/new?category=Features). Describe the feature that you are planing and your first idea how to implement it.
This ensures that you don't start working on something which collides with the targets of the maintainers.

For small fixes and improvements feel free to directly create a PR, the maintainers are happy to review your code.

## APIs

News offers an API that can be used by clients to synchronize with the server.
There are two API declarations, so far only V1 has been fully implemented.
Work on V2 has started with low priority.

- [API-V1.2](api/api-v1-2.md)
- [API-V1.3](api/api-v1-3.md)
- [API-V2](api/api-v2.md)

## Coding Style Guidelines

The PHP code should all adhere to [PSR-2](https://www.php-fig.org/psr/psr-2/).
*Note that this is a different codestyle than Nextcloud itself uses.*
To test the codestyle you can run `make phpcs`.

The application Front End uses Vue 3.5 and the Nextcloud Libraries [Vue Components](https://github.com/nextcloud-libraries/nextcloud-vue) for building the Application running inside your Nextcloud instance. For linting these files, we are using eslint, see the [config file](https://github.com/nextcloud/news/blob/master/eslint.config.mjs). We also have Unit Tests for the components that run with vitest, please ensure these pass when adding new features/fixing bugs.

## Developer setup
TL;DR:

- Clone [nextcloud server repository](https://github.com/nextcloud/server)
- run `git submodule update --init`
- Install the server `php ./occ maintenance:install`
- Clone the viewer repo if you want to be able to upgrade the setup
    - `cd apps && git clone https://github.com/nextcloud/viewer.git`
- Inside apps dir clone the news app: `git clone https://github.com/nextcloud/news.git`

For more information check the Nextcloud [documentation](https://docs.nextcloud.com/server/latest/developer_manual/getting_started/devenv.html), the setup of a webserver is not strictly needed for backend development.

Change into the news directory and run `make` to build the app, you will need php, composer, node, npm and maybe more.

Now you can basically use the news app and test any changes you make on your local development environment. Check out the `appinfo/routes.php` file and `lib/controller/` directory for details on API controllers. Or check out `package.json` for npm scripts and the `src/` directory for the front end Vue Application.

### Docker
We also have a docker based environment check the README in the `docker/` directory.

This setup is nice since you get a full nextcloud installation and you can open the interface in the browser, which allows you to easily test your changes.

There is also a nix-shell config and zellij layout prepared if you are interested in that.

### Devcontainer
Check the README in the .devcontainer directory.

If you have issues with setting up a developer environment create a [new discussion](https://github.com/nextcloud/news/discussions/categories/developer).

### Frontend Tips/Organization

- We use the Nextcloud Vue component library for most of the form controls and navigation
- Vuex is used for state management, this is similar to Redux and has Actions/Mutations and Getters
- We are using the Nextcloud Vite configuration and have enabled Typescript support and importing in the Vue components
- We use ESLint and StyleLint for ensuring correct formatting of the Scripts and HTML

## Testing

When submitting your PR the tests will be run automatically, try to fix any errors. 

### Frontend Unit Tests

Frontend unit tests are using vitest and can be run with `npm run test`.

### API and CLI Integration Tests

We use [bats](https://bats-core.readthedocs.io/en/stable/) to run integration tests against the API and the cli.

Check how to install bats on your system in the [official documentation](https://bats-core.readthedocs.io/en/stable/installation.html).

You also need to pull the submodules of the news repo.

```bash
git submodules update --init
```

The cli tests expect that the feeds are reachable at `http://localhost:8090`, to achieve that you can use `make feed-server &` the `&` means it'll run in the background.

Now the test feeds will be reachable for bats.
Run the tests by executing `bats tests/command` you can also only run specific tests for example `bats tests/command/feeds.bats`.

For the API tests you need to run a second php server or have another web server that provides Nextcloud and the News App.
The tests expect to find Nextcloud at `http://localhost:8080`
You can do this by running `make nextcloud-server`.

The bats tests can be executed like this `bats tests/api`.
