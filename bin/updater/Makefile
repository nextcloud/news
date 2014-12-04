all: install

install: install-systemd

clean:
	sudo rm -rf dist
	sudo rm -rf MANIFEST
	sudo rm -rf build
	sudo rm -rf owncloud_news_updater.egg-info


preinitsetup: clean
	mkdir -p /etc/owncloud/news
	cp $(CURDIR)/example-config.ini /etc/owncloud/news/updater.ini
	python3 setup.py install


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
	cp $(CURDIR)/sysvinit/owncloud-news-updater /etc/rc.d/

	@echo ""
	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    TBD"
	@echo "    TBD"
	@echo "to run the updater on startup and:"
	@echo "    TBD"
	@echo "to reload the changes if you change the config in /etc/owncloud/news/updater.ini"


make uninstall: clean
	rm -rf /etc/owncloud/news/updater.ini
	rm -rf /etc/systemd/systemd/owncloud-news-updater.service
	# TBD uninstall sysvinit
	pip3 uninstall owncloud_news_updater

	@echo ""
	@echo "Uninstalled files. Please run: "
	@echo "    systemctl disable owncloud-news-updater"
	@echo "to remove it from boot"