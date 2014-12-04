all: install

install: install-systemd

preinstall:
	mkdir -p /etc/owncloud/news

install-systemd: preinstall
	cp $(CURDIR)/example-config.ini /etc/owncloud/news/updater.ini
	cp $(CURDIR)/systemd/owncloud-news-updater.service /etc/systemd/system/
	python3 setup.py install

	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    systemctl enable owncloud-news-updater"
	@echo "    systemctl start owncloud-news-updater"
	@echo "to run the updater on startup and:"
	@echo "    systemctl restart owncloud-news-updater"
	@echo "to reload the changes if you change the config in /etc/owncloud/news/updater.ini"


install-sysvinit: preinstall
	cp $(CURDIR)/example-config.ini /etc/owncloud/news/updater.ini
	cp $(CURDIR)/sysvinit/owncloud-news-updater /etc/rc.d/
	python3 setup.py install

	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    TBD"
	@echo "    TBD"
	@echo "to run the updater on startup and:"
	@echo "    TBD"
	@echo "to reload the changes if you change the config in /etc/owncloud/news/updater.ini"

make uninstall:
	rm -rf /etc/owncloud/news
	rm -rf /etc/systemd/systemd/owncloud-news-updater.service
	python3 setup.py uninstall

	@echo "Uninstalled files. Please run: "
	@echo "    systemctl disable owncloud-news-updater"
	@echo "to remove it from boot"