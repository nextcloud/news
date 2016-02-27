# Makefile for building the project

app_name=news
project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
package_name=$(app_name)

all: appstore

clean:
	rm -rf $(build_dir)

update-composer:
	rm -f composer.lock
	git rm -r vendor
	composer install --prefer-dist

dist: clean
	mkdir -p $(source_dir)
	tar cvzf $(source_dir)/$(package_name).tar.gz $(project_dir) \
	--exclude-vcs \
	--exclude=$(project_dir)/build/artifacts \
	--exclude=$(project_dir)/js/node_modules \
	--exclude=$(project_dir)/js/coverage

appstore: clean
	mkdir -p $(appstore_dir)
	tar cvzf $(appstore_dir)/$(package_name).tar.gz $(project_dir) \
	--exclude-vcs \
	--exclude=$(project_dir)/build/artifacts \
	--exclude=$(project_dir)/js/node_modules \
	--exclude=$(project_dir)/js/.bowerrc \
	--exclude=$(project_dir)/js/.jshintrc \
	--exclude=$(project_dir)/js/.jshintignore \
	--exclude=$(project_dir)/js/gulpfile.js \
	--exclude=$(project_dir)/js/*.json \
	--exclude=$(project_dir)/js/*.conf.js \
	--exclude=$(project_dir)/js/*.log \
	--exclude=$(project_dir)/js/README.md \
	--exclude=$(project_dir)/js/.bowerrc \
	--exclude=$(project_dir)/js/app \
	--exclude=$(project_dir)/js/controller \
	--exclude=$(project_dir)/js/coverage \
	--exclude=$(project_dir)/js/directive \
	--exclude=$(project_dir)/js/filter \
	--exclude=$(project_dir)/js/gui \
	--exclude=$(project_dir)/js/plugin \
	--exclude=$(project_dir)/js/service \
	--exclude=$(project_dir)/js/tests \
	--exclude=$(project_dir)/js/vendor/jquery \
	--exclude=$(project_dir)/js/vendor/angular-mocks \
	--exclude=$(project_dir)/\.* \
	--exclude=$(project_dir)/phpunit*xml \
	--exclude=$(project_dir)/composer* \
	--exclude=$(project_dir)/issue_template.md \
	--exclude=$(project_dir)/Makefile \
	--exclude=$(project_dir)/tests \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/.gitattributes \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/Doxyfile \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/FOCUS \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/INSTALL* \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/NEWS \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/phpdoc.ini \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/README \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/TODO \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/VERSION \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/WHATSNEW \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/WYSIWYG \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/art \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/benchmarks \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/configdoc \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/docs \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/extras \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/maintenance \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/plugins \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/smoketests \
	--exclude=$(project_dir)/vendor/ezyang/htmlpurifier/tests \
	--exclude=$(project_dir)/vendor/fguillot/picofeed/docs \
	--exclude=$(project_dir)/vendor/fguillot/picofeed/tests \
	--exclude=$(project_dir)/vendor/pear/net_url2/docs \
	--exclude=$(project_dir)/vendor/pear/net_url2/tests
