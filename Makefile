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
	--exclude=$(project_dir)/js/Gruntfile.js \
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
	--exclude=$(project_dir)/js/service \
	--exclude=$(project_dir)/js/tests \
	--exclude=$(project_dir)/js/vendor/jquery \
	--exclude=$(project_dir)/js/vendor/angular-mocks \
	--exclude=$(project_dir)/.travis.yml \
	--exclude=$(project_dir)/.scrutinizer.yml \
	--exclude=$(project_dir)/phpunit.xml \
	--exclude=$(project_dir)/Makefile \
	--exclude=$(project_dir)/tests \
	--exclude=$(project_dir)/3rdparty/simplepie/README.markdown \
	--exclude=$(project_dir)/3rdparty/simplepie/tests \
	--exclude=$(project_dir)/3rdparty/simplepie/build \
	--exclude=$(project_dir)/3rdparty/simplepie/compatibility_test \
	--exclude=$(project_dir)/3rdparty/simplepie/demo \
	--exclude=$(project_dir)/3rdparty/simplepie/idn \
	--exclude=$(project_dir)/3rdparty/simplepie/.travis.yml \
	--exclude=$(project_dir)/3rdparty/simplepie/composer.json \
	--exclude=$(project_dir)/3rdparty/simplepie/db.sql \
	--exclude=$(project_dir)/3rdparty/simplepie/phpunit.xml.dist \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/.gitattributes \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/composer.json \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/Doxyfile \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/FOCUS \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/INSTALL* \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/NEWS \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/phpdoc.ini \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/README \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/TODO \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/VERSION \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/WHATSNEW \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/WYSIWYG \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/art \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/benchmarks \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/configdoc \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/docs \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/extras \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/maintenance \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/plugins \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/smoketests \
	--exclude=$(project_dir)/3rdparty/ezyang/htmlpurifier/tests \
	--exclude=$(project_dir)/3rdparty/fguillot/picofeed/docs \
	--exclude=$(project_dir)/3rdparty/fguillot/picofeed/tests
