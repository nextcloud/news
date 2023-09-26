import { shallowMount, createLocalVue } from '@vue/test-utils'
import ShareItem from '../../../../src/components/ShareItem.vue'
import { ShareService } from '../../../../src/dataservices/share.service'

describe('AddFeed.vue', () => {
	'use strict'

	let wrapper: any
	beforeEach(() => {
		const localVue = createLocalVue()
		wrapper = shallowMount(ShareItem, {
			localVue,
			propsData: {
				itemId: 123,
			},
		})
	})

	describe('clickUser()', () => {
		it('should add to selected if user not selected before', () => {
			wrapper.vm.selected = []

			wrapper.vm.clickUser({ displayName: 'display', shareName: 'share' })

			expect(wrapper.vm.selected.length).toEqual(1)
		})

		it('should remove from selected if user is selected before', () => {
			wrapper.vm.selected = [{ displayName: 'display', shareName: 'share' }]

			wrapper.vm.clickUser({ displayName: 'display', shareName: 'share' })

			expect(wrapper.vm.selected.length).toEqual(0)
		})
	})

	describe('searchUsers()', () => {
		it('should call ShareService to fetch users to add to user (display) list', async () => {
			ShareService.fetchUsers = jest.fn().mockReturnValue({
				data: {
					ocs: {
						data: {
							users: [],
						},
					},
				},
			})
			wrapper.vm.userName = 'search'

			await wrapper.vm.searchUsers()

			expect(ShareService.fetchUsers).toBeCalled()
		})
	})

	describe('share()', () => {
		it('should call ShareService to share article id with backend', async () => {
			ShareService.share = jest.fn()
			wrapper.vm.selected = [{ displayName: 'display', shareName: 'share' }]

			await wrapper.vm.share()

			expect(ShareService.share).toBeCalled()

			wrapper.vm.selected = [{ displayName: 'display', shareName: 'share' }, { displayName: 'display2', shareName: 'share2' }]

			await wrapper.vm.share()

			let args = (ShareService.share as any).mock.calls[0]
			expect(args[1]).toEqual(['share'])
			args = (ShareService.share as any).mock.calls[1]
			expect(args[1]).toEqual(['share', 'share2'])
		})
	})
})
