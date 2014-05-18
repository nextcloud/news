/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('Model', function () {
    'use strict';

    var childModel;

    beforeEach(module('News'));

    beforeEach(inject(function (Model) {
        var ChildModel = function () {
            Model.call(this, 'id');
        };
        ChildModel.prototype = Object.create(Model.prototype);

        childModel = new ChildModel();
    }));


    it('should receive an object', function () {
        var objects = [
            {
                id: 2
            },
            {
                id: 3
            }
        ];

        childModel.receive(objects);

        expect(childModel.size()).toBe(2);
    });


    it('should add an object', function () {
        var object = {
            id: 3,
            name: 'test'
        };
        childModel.add(object);

        expect(childModel.get(3)).toBe(object);
    });


    it('should overwrite an object if it already exists', function () {
        var object1,
            object2;

        object1 = {
            id: 3,
            name: 'test',
            test: 'ho'
        };

        object2 = {
            id: 3,
            name: 'test2'
        };

        childModel.add(object1);
        childModel.add(object2);

        expect(childModel.get(3).name).toBe('test2');
        expect(childModel.get(3).test).toBe('ho');
        expect(childModel.size()).toBe(1);
    });


    it('should delete a model', function () {
        var object1,
            object2;

        object1 = {
            id: 3,
            name: 'test',
            test: 'ho'
        };

        object2 = {
            id: 4,
            name: 'test2'
        };

        childModel.add(object1);
        childModel.add(object2);

        childModel.delete(3);

        expect(childModel.get(3)).not.toBeDefined();
        expect(childModel.get(4).name).toBe('test2');
        expect(childModel.size()).toBe(1);
    });

});