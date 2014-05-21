/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('Resource', () => {
    'use strict';

    let childResource;

    beforeEach(module('News'));

    beforeEach(inject((Resource, $http) => {
        class ChildResource extends Resource {
            constructor ($http) {
                super('id', $http);
            }
        }

        childResource = new ChildResource($http);
    }));


    it('should receive an object', () => {
        let objects = [
            {
                id: 2
            },
            {
                id: 3
            }
        ];

        childResource.receive(objects);

        expect(childResource.size()).toBe(2);
    });


    it('should add an object', () => {
        let object = {
            id: 3,
            name: 'test'
        };
        childResource.add(object);

        expect(childResource.get(3)).toBe(object);
    });


    it('should overwrite an object if it already exists', () => {
        let object1 = {
            id: 3,
            name: 'test',
            test: 'ho'
        };

        let object2 = {
            id: 3,
            name: 'test2'
        };

        childResource.add(object1);
        childResource.add(object2);

        expect(childResource.get(3).name).toBe('test2');
        expect(childResource.get(3).test).toBe('ho');
        expect(childResource.size()).toBe(1);
    });


    it('should delete a Resource', () => {
        let object1 = {
            id: 3,
            name: 'test',
            test: 'ho'
        };

        let object2 = {
            id: 4,
            name: 'test2'
        };

        childResource.add(object1);
        childResource.add(object2);

        childResource.delete(3);

        expect(childResource.get(3)).not.toBeDefined();
        expect(childResource.get(4).name).toBe('test2');
        expect(childResource.size()).toBe(1);
    });


    it('should clear all models', () => {
        let object1 = {
            id: 3,
            name: 'test',
            test: 'ho'
        };

        let object2 = {
            id: 4,
            name: 'test2'
        };

        childResource.add(object1);
        childResource.add(object2);

        childResource.clear();

        expect(childResource.get(3)).not.toBeDefined();
        expect(childResource.get(4)).not.toBeDefined();
        expect(childResource.size()).toBe(0);
    });


    it('should get all models', () => {
        let object1 = {
            id: 3,
            name: 'test',
            test: 'ho'
        };

        let object2 = {
            id: 4,
            name: 'test2'
        };

        childResource.add(object1);
        childResource.add(object2);

        expect(childResource.getAll()[1].id).toBe(4);
    });

});