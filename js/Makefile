# ownCloud - News
#
# @author Bernhard Posselt
# @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
#
# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
# License as published by the Free Software Foundation; either
# version 3 of the License, or any later version.
#
# This library is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU AFFERO GENERAL PUBLIC LICENSE for more details.
#
# You should have received a copy of the GNU Affero General Public
# License along with this library.  If not, see <http://www.gnu.org/licenses/>.

firefox_bin=/usr/bin/firefox
chrome_bin=/usr/bin/chromium
coffee=$(CURDIR)/node_modules/coffee-script/bin/coffee
grunt=$(CURDIR)/node_modules/grunt-cli/bin/grunt
phantomjs=$(CURDIR)/node_modules/phantomjs/bin/phantomjs

all: compile

deps:
	cd $(CURDIR)/
	npm install --deps
	cd ..

watch: compile
	$(coffee) --compile --watch --output $(CURDIR)/build/app $(CURDIR)/app/ & \
	$(coffee) --compile --watch --output $(CURDIR)/build/tests $(CURDIR)/tests/ & \
	$(grunt) --config $(CURDIR)/Gruntfile.coffee run

testacular: deps
	export CHROME_BIN=$(chrome_bin) && export FIREFOX_BIN=$(firefox_bin) && \
	$(grunt) --config $(CURDIR)/Gruntfile.coffee testacular

phpunit:
	$(grunt) --config $(CURDIR)/Gruntfile.coffee testphp	

compile: deps
	mkdir -p $(CURDIR)/build/app
	mkdir -p $(CURDIR)/build/tests
	mkdir -p $(CURDIR)/public
	$(coffee) --compile --output $(CURDIR)/build/app $(CURDIR)/app/
	$(coffee) --compile --output $(CURDIR)/build/tests $(CURDIR)/tests/
	$(grunt) --config $(CURDIR)/Gruntfile.coffee compile

test: deps compile
	export PHANTOMJS_BIN=$(phantomjs) && \
	$(grunt) --config $(CURDIR)/Gruntfile.coffee ci

clean:
	rm -rf $(CURDIR)/build
	rm -rf $(CURDIR)/test-results.xml
