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

For linting JavaScript, a [jshint file](https://github.com/nextcloud/news/blob/master/js/.jshintrc) is used that is run before compiling the JavaScript.

## General Developer setup
Check the Nextcloud [documentation](https://docs.nextcloud.com/server/latest/developer_manual/getting_started/devenv.html) to learn how to setup a developer environment, alternatively to a proper web server you can also use the [builtin php server](https://www.php.net/manual/en/features.commandline.webserver.php) on demand, it is enough for development purposes.

When your setup is running, clone the news repository in the `apps/` directory inside the server.

Change into the news directory and run make to build the app, you will need php, composer, node, npm and maybe more.

Now you can basically use the news app and test your changes.

## Running Integration tests locally
We use [bats](https://bats-core.readthedocs.io/en/stable/) to run integration tests against the API and the cli.

Check how to install bats on your system in the [official documentation](https://bats-core.readthedocs.io/en/stable/installation.html).

You also need to pull the submodules of the news repo.
```bash
git submodules update --init
```

The cli tests expect that the feeds are reachable at `http://localhost:8090`, to achieve that you can use the [builtin php server](https://www.php.net/manual/en/features.commandline.webserver.php).

Change into the `tests/test_helpers/feeds` directory and execute `php -S localhost:8090` you can also run it in the background like this `php -S localhost:8090 &`.

Now the test feeds will be reachable for bats.
Run the tests by executing `bats tests/command` you can also only run specific tests for example `bats tests/command/feeds.bats`.

For the API tests you need to run a second php server or have another web server that provides Nextcloud and the News App.
The tests expect to find Nextcloud at `http://localhost:8080`
You can do this by running `php -S localhost:8080` in the Nextcloud server repository.

The bats tests can be executed like this `bats tests/api`.
