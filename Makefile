# Makefile for building the project

app_name=news
project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
package_name=$(app_name)

all: dist

clean:
	rm -rf $(build_dir)

dist: appstore

appstore: clean
	mkdir -p $(appstore_dir)
	tar cvzf $(appstore_dir)/$(package_name).tar.gz $(project_dir) \
	--exclude-vcs --exclude=$(project_dir)/build/artifacts \
	--exclude=$(project_dir)/js/node_modules
