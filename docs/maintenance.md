# Maintenance

## Release
Releases are created automatically by GitHub Actions. A release is triggered via a GitHub Release.
The GitHub Action will then start a build based on the git tag. A release can only be approved by [@Grotax](https://github.com/Grotax) or [@SMillerDev](https://github.com/SMillerDev). An admin of the Nextcloud organization can always overwrite these settings. The private key is stored as environmental secret in GitHub. The owner of the private key is [@Grotax](https://github.com/Grotax).

## Support
### PHP
While the app should try to support all PHP versions that Nextcloud currently supports,
the real focus when deciding to cut a PHP version should be on maintenance burden.
Users are nice, but devs should be a priority in decisions that are likely to impact them significantly.

### Issues
- Bug reports without test cases (feed URL and action is enough) can be closed with or without comment.

- Feature requests without thoughtful commentary or pull request can be closed with or without comment,
unless a developer is interested to support such a feature.

- Issues without activity in the last 30 days can be closed with or without comment.
If this is a bug you care about that isn't getting attention, fix it.
If you're good enough to understand the bug, you're good enough to fix it.


_Largely inspired by https://gist.github.com/ryanflorence/124070e7c4b3839d4573_
