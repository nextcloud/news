/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */
exports.config = {
	seleniumAddress: 'http://localhost:4444/wd/hub',
	specs: ['../tests/e2e/**/*.js']
}