# Developer
Welcome to the Nextcloud News App developer documentation.

News is open for contributions, if you plan to implement a new feature make sure to open a [discussion](https://github.com/nextcloud/news/discussions/new?category=Features). Describe the feature that you are planing and your first idea how to implement it.
This ensures that you don't start working on something which collides with the targets of the maintainers.

For small fixes and improvements feel free to directly create a PR, the maintainers are happy to review your code.

## APIs
News offers an API that can be used by clients to synchronize with the server.
There are two API declarations, so far only V1 has been fully implemented.
Work on V2 has started with low priority.

- [API-V1](api/api-v1.md)
- [API-V2](api/api-v2.md)

## Coding Style Guidelines
The PHP code should all adhere to [PSR-2](https://www.php-fig.org/psr/psr-2/).
*Note that this is a different codestyle than Nextcloud itself uses.*
To test the codestyle you can run `make phpcs`.

For linting JavaScript, a [jshint file](https://github.com/nextcloud/news/blob/master/js/.jshintrc) is used that is run before compiling the JavaScript.
