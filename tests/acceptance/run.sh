# ownCloud - News
#
# @author Bernhard Posselt, Thomas Müller, Jakob Sack
# @copyright 2013 Bernhard Posselt nukeawhale@gmail.com
# @copyright 2012-2013 Thomas Müller thomas.mueller@tmit.eu
# @copyright 2012-2013 Jakob Sack
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



# set up rvm and xvfb dependencies
if [[ ! -e "/usr/bin/xvfb-run" ]]; then

	# archlinux
	if [[ -e "/usr/bin/pacman" ]]; then
		echo "installing xvfb, please enter password..."
		sudo pacman -S xorg-server-xvfb

	# fedora
	elif [[ -e "/usr/bin/yum" ]]; then
		echo "installing xvfb, please enter password..."
		yum install xorg-x11-server-Xvfb

	# debian
	elif [[ -e "/usr/bin/apt-get" ]]; then
		echo "installing xvfb, please enter password..."
		sudo apt-get install xvfb

	else
		echo "You have to install xvfb in order to run the test suite"
		exit 1
	fi
fi

# dont use --user-install for gems since this breaks rvm. abort setup in case it
# exists
if [[ -e "/etc/gemrc" ]]; then
	if grep -qe "^[^#].*user-install" /etc/gemrc; then
		echo "Found --user-install in /etc/gemrc"
		echo "Please remove it, as it will break rubygems in RVM."
		exit 1
	fi
fi

# set up rvm
if [[ -f "$HOME/.rvm/scripts/rvm" ]]; then
	source "$HOME/.rvm/scripts/rvm"
elif [[ -f "/usr/local/rvm/scripts/rvm" ]]; then
	source "/usr/local/rvm/scripts/rvm"
else
	# set up a local rvm installation
	curl -L get.rvm.io | bash -s stable
	[[ -s "$HOME/.rvm/scripts/rvm" ]] && source "$HOME/.rvm/scripts/rvm"
fi


# Set the gemset and ruby version
rvm install 2.0.0
rvm use ruby-2.0.0@oc_acceptance --create

bundle install
cucumber -f json -o ./logs/owncloud.json -f pretty HOST=localhost/owncloud features