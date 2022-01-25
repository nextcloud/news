# Installation/Update

## Dependencies
* 64bit OS (starting with News 16.0.0)
* PHP >= 7.3
* Nextcloud 22
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

Also see the [Nextcloud documentation](https://docs.nextcloud.com/server/stable/admin_manual/configuration_database/linux_database_configuration.html?highlight=database). Oracle is currently not supported by news.

## Performance Notices
* Use MySQL/MariaDB or PostgreSQL for better database performance
* Use the [updater script to thread and speed up the update](https://github.com/nextcloud/news-updater)

## Before you install/update the News app
Before you install the app do the following:

* Check that your **nextcloud/data/** directory is owned by your web server user and that it is write/readable
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
