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
	--exclude-vcs \
	--exclude=$(project_dir)/build/artifacts \
	--exclude=$(project_dir)/js/node_modules

appstore: clean
	mkdir -p $(appstore_dir)
	tar cvzf $(appstore_dir)/$(package_name).tar.gz $(project_dir) \
	--exclude-vcs \
	--exclude=$(project_dir)/build/artifacts \
	--exclude=$(project_dir)/js/node_modules \
	--exclude=$(project_dir)/js/phpunit.xml \
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
	--exclude=$(project_dir)/js/utility \
	--exclude=$(project_dir)/js/tests \
	--exclude=$(project_dir)/js/vendor/jquery \
	--exclude=$(project_dir)/js/vendor/angular-mocks \
	--exclude=$(project_dir)/js/vendor/angular/angular.js \
	--exclude=$(project_dir)/js/vendor/angular/angular.min.js.gzip \
	--exclude=$(project_dir)/js/vendor/angular/angular.min.js.map \
	--exclude=$(project_dir)/js/vendor/angular-animate/angular-animate.js \
	--exclude=$(project_dir)/js/vendor/angular-animate/angular-animate.min.js.map \
	--exclude=$(project_dir)/js/vendor/angular-route/angular-route.js \
	--exclude=$(project_dir)/js/vendor/angular-route/angular-route.min.js.map \
	--exclude=$(project_dir)/js/vendor/angular-sanitize/angular-sanitize.js \
	--exclude=$(project_dir)/js/vendor/angular-sanitize/angular-sanitize.min.js.map \
	--exclude=$(project_dir)/js/vendor/momentjs/lang \
	--exclude=$(project_dir)/js/vendor/momentjs/moment.js \
	--exclude=$(project_dir)/js/vendor/momentjs/min/langs.js \
	--exclude=$(project_dir)/js/vendor/momentjs/min/langs.min.js \
	--exclude=$(project_dir)/js/vendor/momentjs/min/moment.min.js \
	--exclude=$(project_dir)/js/vendor/momentjs/min/moment-with-langs.js \
	--exclude=$(project_dir)/js/vendor/traceur-runtime/traceur-runtime.js \
	--exclude=$(project_dir)/js/vendor/traceur-runtime/traceur-runtime.min.map \
	--exclude=$(project_dir)/.travis.yml \
	--exclude=$(project_dir)/.scrutinizer.yml \
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
	--exclude=$(project_dir)/3rdparty/htmlpurifier/.gitattributes \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/composer.json \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/Doxyfile \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/FOCUS \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/INSTALL* \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/NEWS \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/phpdoc.ini \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/README \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/TODO \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/VERSION \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/WHATSNEW \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/WYSIWYG \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/art \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/benchmarks \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/configdoc \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/docs \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/extras \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/maintenance \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/plugins \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/smoketests \
	--exclude=$(project_dir)/3rdparty/htmlpurifier/tests
