###
# ownCloud news app
#
# @author Alessandro Cosentino
# @author Bernhard Posselt
# Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
###

###
Turns a normal select into a folder select with the ability to create new folders
###
angular.module('News').directive 'addFolderSelect', ['$rootScope', ->

        return (scope, elm, attr) ->

                options =
                        singleSelect: true
                        selectedFirst: true
                        createText: $(elm).data('create')
                        createdCallback: (selected, value) ->
                                console.log selected
                                console.log value

                $(elm).multiSelect(options)

]