all: watch


deps:
        cd $(CURDIR)
        npm install --save-dev

watch: compile
        $(CURDIR)/node_modules/.bin/grunt --config $(CURDIR)/grunt.coffee run

compile:
        mkdir -p $(CURDIR)/build
        $(CURDIR)/node_modules/.bin/grunt --config $(CURDIR)/grunt.coffee compile

clean:
        rm -rf $(CURDIR)/build
