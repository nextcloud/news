all: install

install: install-systemd

install-systemd:
	mkdir -p /etc/owncloud/news

	# install systemd unit file
	cp $(CURDIR)/example-config.ini /etc/owncloud/news/updater.ini
	cp $(CURDIR)/systemd/owncloud-news.service /etc/systemd/system/
	python3 setup.py install

	@echo "Installed files. Please edit your config in /etc/owncloud/news/updater.ini and run:"
	@echo "    systemctl enable owncloud-news"
	@echo "    systemctl start owncloud-news"
	@echo "to run the updater on startup and:"
	@echo "    systemctl restart owncloud-news"
	@echo "if you change the config in /etc/owncloud/news/updater.ini"

make uninstall:
	rm -rf /etc/owncloud/news
	rm -rf /etc/systemd/systemd/owncloud-news.service
	python3 setup.py uninstall

	@echo "Uninstalled files. Please run: "
	@echo "    systemctl disable owncloud-news"
	@echo "to remove it from boot"