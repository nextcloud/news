# Maintenance documentation

## Release
Releases should be done by checking `make test`, cleaning using `make distclean` and consequently running `make dist`.
This will create an app store ready package to be uploaded. 
This process should be done by someone who has the private keys and the access to sign and upload such a package.

## Support
### PHP
While the app should try to support all PHP versions that nextcloud currently supports,
the real focus when deciding to cut a PHP version should be on maintenance burden. 
Users are nice but devs should be a priority in decisions that are likely to impact them significantly.

### Issues
- Bug reports without test cases (feed url and action is enough) can be closed with or without comment.

- Feature requests without thoughtful commentary or pull request can be closed with or without comment,
unless a developer is interested to support such a feature.

- Issues without activity in the last 30 days can be closed with or without comment.
If this is a bug you care about that isn't getting attention, fix it. 
If you're good enough to understand the bug, you're good enough to fix it.


_Largely inspired by https://gist.github.com/ryanflorence/124070e7c4b3839d4573_
