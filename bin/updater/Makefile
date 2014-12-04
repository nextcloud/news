all: install

install: install-systemd

preinit:
	mkdir -p /etc/owncloud/news
	cp $(CURDIR)/example-config.ini /etc/owncloud/news/updater.ini
	python3 setup.py install

install-systemd: preinit
	cp $(CURDIR)/systemd/owncloud-news-updater.service /etc/systemd/system/

	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    systemctl enable owncloud-news-updater"
	@echo "    systemctl start owncloud-news-updater"
	@echo "to run the updater on startup and:"
	@echo "    systemctl restart owncloud-news-updater"
	@echo "to reload the changes if you change the config in /etc/owncloud/news/updater.ini"


install-sysvinit: preinit
	cp $(CURDIR)/sysvinit/owncloud-news-updater /etc/rc.d/

	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    TBD"
	@echo "    TBD"
	@echo "to run the updater on startup and:"
	@echo "    TBD"
	@echo "to reload the changes if you change the config in /etc/owncloud/news/updater.ini"

make uninstall:
	rm -rf /etc/owncloud/news/updater.ini
	rm -rf /etc/systemd/systemd/owncloud-news-updater.service
	# TBD uninstall sysvinit
	python3 setup.py uninstall

	@echo "Uninstalled files. Please run: "
	@echo "    systemctl disable owncloud-news-updater"
	@echo "to remove it from boot"