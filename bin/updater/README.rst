ownCloud News Updater
=====================

ownCloud does not require people to install threading or multiprocessing libraries. Because the feed update process is mainly limited by I/O, parallell fetching of RSS feed updates can speed up the updating process significantly. In addition the cronjob can get `into a deadlock <https://github.com/owncloud/core/issues/3221>`_ which will cause the updater to get stuck resulting in your feeds not to being updated anymore. This can be soled by using a script that uses the `updater API <https://github.com/owncloud/news/wiki/Cron-1.2>`_

Preinstallation
---------------

To run the updates via an external threaded script the cron updater has to be disabled. To do that go to the admin section an uncheck the "Use ownCloud cron" checkbox or open **owncloud/data/news/config/config.ini** set::

    useCronUpdates = true

to::

    useCronUpdates = false

Then install the following packages (my vary depending on your distribution):

* python3-pip
* python3-setuptools
* make

If you are **on Debian 7** you want to create a symlink for pip to make use of the Makefile::

    sudo ln -s /usr/bin/pip-3.2 /usr/bin/pip3

Updating
--------

If you have installed the updater on your system you can update it by running::

    make update

The **init and config files won't be updated** and you need to update them manually in case there is a breaking change therefore follow the `CHANGELOG.md <https://github.com/owncloud/news/blob/master/CHANGELOG.md>`_ to stay up to date with the updater changes.

Finally reload the systemd service::

    sudo systemctl restart owncloud-news-updater

or SysVinit script::

    sudo /etc/init.d/owncloud-news-updater restart


Installation: No init system
----------------------------

If you decide against using an init system to run the script simply run::

    sudo setup.py install

Then you can run the updater daemon using::

    owncloud-news-updater --user USERNAME --password PASSWORD http://yourcloud.com

or if you are using a config file::

    owncloud-news-updater -c /path/to/config


To see all config options run::

    owncloud-news-updater -h

Installation: SystemD
---------------------

To install the script for systemd run::

    sudo make install-systemd

Then edit the config in **/etc/owncloud/news/updater.ini** with your details and run::

    owncloud-news-updater -c /etc/owncloud/news/updater.ini

to test your settings. If everything worked out fine, enable the systemd unit with::

    sudo systemctl enable owncloud-news-updater.service
    sudo systemctl start owncloud-news-updater.service

If you make changes to the **updater.ini** file don't forget to reload the service with::

    sudo systemctl restart owncloud-news-updater.service


Installation: SysVinit
----------------------

.. note:: Debian 7 (wheezy) is the only supported Linux distribution for SysVinit and support will be dropped once Debian 8.1 is released

To install the script for SysVinit run::

    sudo make install-sysvinit

Then edit the config in **/etc/owncloud/news/updater.ini** with your details and run::

    owncloud-news-updater -c /etc/owncloud/news/updater.ini

to test your settings. If everything worked out fine, enable the init script with::

    sudo update-rc.d /etc/init.d/owncloud-news-updater defaults
    sudo /etc/init.d/owncloud-news-updater start

If you make changes to the **updater.ini** file don't forget to reload the service with::

    sudo /etc/init.d/owncloud-news-updater restart


Uninstallation
--------------

To uninstall the script make sure that pip3 is installed (usually called python3-pip) and run::

    make uninstall


Self signed certificates
------------------------

Should you use a self signed certificate over SSL, first consider getting a free valid cert signed by `StartSSL <http://startssl.com>`_. If you don't want to get a valid certificate, you need to add it to the installed certs::

    cat /path/to/your/cert/cacert.pem >> /usr/local/lib/python3.X/dist-packages/requests/cacert.pem

The directories might vary depending on your distribution and Python version.


Development
-----------

If you want to edit the python code and test it run::

    python3 -m owncloud_news -c /path/to/config.ini
