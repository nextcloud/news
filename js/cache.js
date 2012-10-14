/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

/**
 * The cache is used to cache items and tell the program which items have been
 * loaded
 */

 (function(exports) {
    "use strict";

    var Cache = function(){
        this.reset();
    };



    Cache.prototype.reset = function(){
        this.items = [];
    };

    exports.Cache = Cache;

 }(typeof exports === "undefined" ? (this.moduleName = {}): exports));
