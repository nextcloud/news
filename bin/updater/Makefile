all: install


install: install-systemd


clean:
	rm -rf dist
	rm -rf MANIFEST
	rm -rf build
	rm -rf owncloud_news_updater.egg-info


update: clean
	pip3 uninstall owncloud_news_updater
	python3 setup.py install


preinitsetup: clean
	mkdir -p /etc/owncloud/news
	cp $(CURDIR)/example-config.ini /etc/owncloud/news/updater.ini
	python3 setup.py install --install-scripts=/usr/bin


install-systemd: preinitsetup
	cp $(CURDIR)/systemd/owncloud-news-updater.service /etc/systemd/system/

	@echo ""
	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    systemctl enable owncloud-news-updater.service"
	@echo "    systemctl start owncloud-news-updater.service"
	@echo "to run the updater on startup and:"
	@echo "    systemctl restart owncloud-news-updater.service"
	@echo "to reload the changes if you change the config in /etc/owncloud/news/updater.ini"


install-sysvinit: preinitsetup
	cp $(CURDIR)/sysvinit/owncloud-news-updater /etc/init.d/

	@echo ""
	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    sudo update-rc.d owncloud-news-updater defaults"
	@echo "    sudo /etc/init.d/owncloud-news-updater start"
	@echo "to run the updater on startup and:"
	@echo "    sudo /etc/init.d/owncloud-news-updater restart"
	@echo "to reload the changes if you change the config in /etc/owncloud/news/updater.ini"


uninstall: clean
	rm -rf /etc/owncloud/news/updater.ini
	rm -rf /etc/systemd/systemd/owncloud-news-updater.service
	rm -rf /etc/init.d/owncloud-news-updater
	pip3 uninstall owncloud_news_updater

	@echo ""
	@echo "Uninstalled files. Please run: "
	@echo "    systemctl disable owncloud-news-updater"
	@echo "to remove it from boot"
