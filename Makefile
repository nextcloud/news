# Makefile for building the project

app_name=news
project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
package_name=$(app_name)

all: dist

clean:
	rm -rf $(build_dir)

dist: clean
	appstore: clean
	mkdir -p $(source_dir)
	tar cvzf $(source_dir)/$(package_name).tar.gz $(project_dir) \
	--exclude-vcs --exclude=$(project_dir)/build/artifacts \
	--exclude=$(project_dir)/js/node_modules

appstore: clean
	mkdir -p $(appstore_dir)
	tar cvzf $(appstore_dir)/$(package_name).tar.gz $(project_dir) \
	--exclude-vcs --exclude=$(project_dir)/build/artifacts \
	--exclude=$(project_dir)/js/node_modules \
	--exclude=$(project_dir)/js/tests \
	--exclude=$(project_dir)/travis \
	--exclude=$(project_dir)/tests
