# Installation/Update & Uninstall

## Dependencies
* 64bit OS (starting with News 16.0.0)
* PHP >= 8.0
* Nextcloud (current stable version)
* libxml >= 2.7.8

You also need some PHP extensions:

* json
* simplexml
* xml
* dom
* curl
* iconv

## Supported Databases
* PostgreSQL >= 10
* MariaDB >= 10.2
* MySQL >= 8.0
* SQLite (discouraged)

Also see the [Nextcloud documentation](https://docs.nextcloud.com/server/stable/admin_manual/configuration_database/linux_database_configuration.html?highlight=database). Oracle is currently not supported by News.

## Performance Notices
* Use MySQL/MariaDB or PostgreSQL for better database performance
* Use the [updater script to thread and speed up the update](https://github.com/nextcloud/news-updater)

## Cache
News and it's libraries require a writeable temporary directory used as cache. The base directory depends on your system.
You can [configure a custom directory](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/config_sample_php_parameters.html?highlight=temp#tempdirectory) if you want.

In most cases the base directory will be `/tmp`. News will create a folder `news-$instanceID` the [instance ID is defined by Nextcloud](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/config_sample_php_parameters.html?highlight=temp#instanceid).

Inside that folder a subfolder `cache` is created, inside this cache folder news and libraries will try to create cache directories for caching images, html and more.

You need to ensure that your web-server user can write to that directory.

## Before you install/update the News app
Before you install the app do the following:

* Check that your installation fulfills the [requirements listed above](#dependencies)
* [Set up Nextcloud Background Jobs](https://docs.nextcloud.org/server/latest/admin_manual/configuration_server/background_jobs_configuration.html#cron) to enable feed updates.

Then proceed to install the app either from an archive (zip/tar.gz) or clone it from the repository using git

## Installing from the [app store](https://apps.nextcloud.com/apps/news)
This is the easiest solution: Simply go the apps page (section: "Multimedia") and enable the News app

## Installing from archive
* Go to the [Nextcloud News GitHub releases page](https://github.com/nextcloud/news/releases) and download the latest release/archive to your server
* The news.tar.gz file contains the compiled and signed app files, if you install from source you have to build the app on your own.
* On your server, check if there is a folder called **nextcloud/apps/news**. If there is one, delete it.
* Extract the downloaded archive to the **nextcloud/apps/** folder.
* Remove the version from the extracted folder (e.g. rename **nextcloud/apps/news-4.0.3/** to **nextcloud/apps/news/**
* If you are a version greater than or equal to 8.0.0 and downloaded the **Source code** zip or tar.gz, you need to install the JavaScript and PHP dependencies and compile the JavaScript first. On your terminal, change into the **nextcloud/apps/news/** directory and run the following command (requires node >5.6, npm, curl, make and which):

        sudo -u www-data make  # www-data might vary depending on your distribution

* Finally make sure that the **nextcloud/apps/news** directory is owned by the web server user

        sudo chown -R www-data:www-data nextcloud/apps/news  # www-data:www-data might vary depending on your distribution

* Activate the **News** app in the apps menu

## Installing from Git (development version)

### Build Dependencies
These Dependencies are only relevant if you want to build the source code:

* make
* which
* Node.js >= 6
* npm
* composer

* The master branch will always be stable in conjunction with the latest master branch from Nextcloud
* JavaScript and PHP libraries are not included anymore since 8.0.0 and will require you to run **make** after updating/installing the app
* In your terminal go into the **nextcloud/apps/** directory and then run the following command:

        git clone https://github.com/nextcloud/news.git
        cd news
        make

* If you are using a stable Nextcloud release, stay with the [latest git tag release which is running on your version](https://github.com/nextcloud/news/releases). To get an overview over all existing tags run:

        git tag

 You can switch to a release which will be supported on your installation by running:

      git checkout tags/TAG
      make  # if News version >= 8.0.0

 For instance, to use the 5.2.8 release, run:

      git checkout tags/5.2.8

* Activate the **News** app in the apps menu

To update the News app use change into the **nextcloud/apps/news/** directory using your terminal and then run:

    git pull --rebase origin master
    make

## Uninstall with cleanup

First uninstall the app via the web-interface or via occ:

```console
./occ app:remove news
```

This currently does not remove any of the database tables.
Data in your `/tmp` directory will be automatically deleted by the OS.
If you changed the temporary directory for Nextcloud you need to check on your own.

Careful, this next part is only intended for admins, that know what they are doing.

To remove the tables from the DB we drop the tables of News.
Your installation might have a different prefix than `oc_` but it is the default in most installations.
Connect to your DB and execute the commands. Don't forget to switch to the right database.
For example in mysql: `use nextcloud;`

```sql
DROP TABLE oc_news_items;
DROP TABLE oc_news_feeds;
DROP TABLE oc_news_folders;
```

Then we remove the traces in the migrations table.

```sql
DELETE FROM oc_migrations WHERE app='news';
```

Next delete the app configuration.

```sql
DELETE FROM oc_appconfig WHERE appid = 'news';
```

And finally remove the jobs from the job table.
The last two lines are only needed for older installations.

```sql
DELETE FROM oc_jobs WHERE class='OCA\\News\\Cron\\UpdaterJob';
DELETE FROM oc_jobs WHERE class='OCA\\News\\Cron\\Updater';
DELETE FROM oc_jobs WHERE argument='["OCA\\\\News\\\\Cron\\\\Updater","run"]';
```

Now nothing is left from News in your Nextcloud installation.
