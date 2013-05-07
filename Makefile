# ownCloud - App Framework
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

# This makefile is for general project specific stuff like packaging a new 
# release for the app store and running php unittests which require core

build_directory=build/
app_name=news
package_name=$(build_directory)$(app_name)

all: dist


clean:
	rm -rf $(build_directory)


dist: clean test
	mkdir -p $(build_directory)
	git archive HEAD --format=zip --prefix=$(app_name)/ > $(package_name).zip


test: unit integration acceptance


unit:
	phpunit tests/unit


integration:
	phpunit tests/integration


acceptance:
	# TODO
	
