import { shallowMount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick, reactive } from 'vue'
import FeedItemDisplay from '../../../../../src/components/feed-display/FeedItemDisplay.vue'
import { MEDIA_TYPE, SHOW_MEDIA } from '../../../../../src/enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../../../../src/store/index.ts'

describe('FeedItemDisplay.vue', () => {
	'use strict'
	let wrapper: any
	let mockGetters

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
	}
	const mockFeeds = [
		{
			id: 1,
			fullTextEnabled: false,
		},
		{
			id: 2,
			fullTextEnabled: true,
		},
	]

	const defaultMediaOptions = {
		[MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.ALWAYS,
		[MEDIA_TYPE.IMAGES]: SHOW_MEDIA.ALWAYS,
		[MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ALWAYS,
		[MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.ALWAYS,
	}

	const dispatchStub = vi.fn()
	const commitStub = vi.fn()

	beforeEach(() => {
		mockGetters = reactive({
			feeds: mockFeeds,
			mediaOptions: defaultMediaOptions,
		})
		wrapper = shallowMount(FeedItemDisplay, {
			props: {
				item: mockItem,
				fetchKey: 'all',
			},
			global: {
				mocks: {
					$store: {
						getters: mockGetters,
						state: {
							feeds: [],
							folders: [],
						},
						dispatch: dispatchStub,
						commit: commitStub,
					},
				},
				stubs: {
					NcChip: {
						name: 'NcChip',
						props: ['text', 'variant', 'noClose'],
						template: '<span class="nc-chip">{{ text }}</span>',
					},

				},
			},
		})
		dispatchStub.mockReset()
		commitStub.mockReset()
	})

	it('should format date to match locale', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = wrapper.vm.formatDate(epoch / 1000)

		expect(formattedDate).toEqual(new Date(epoch).toLocaleString(OC.getLanguage(), {
			year: 'numeric',
			month: '2-digit',
			day: '2-digit',
			hour: 'numeric',
			minute: '2-digit',
			second: '2-digit',
		}))
	})

	it('should retrieve feed by ID', () => {
		const feed = wrapper.vm.feed

		expect(feed).toEqual(mockFeeds[0])
	})

	it('should focus on new selected item when using screen reader mode', async () => {
		const el = { focus: vi.fn() }
		Object.defineProperty(wrapper.vm.$refs, 'titleLink', { value: el, configurable: true })

		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(true)
		wrapper.vm.$options.watch.isSelected.call(wrapper.vm, true)
		await nextTick()

		expect(el.focus).toHaveBeenCalled()
	})

	it('should not focus on new selected item when not using screen reader mode', async () => {
		const el = { focus: vi.fn() }
		Object.defineProperty(wrapper.vm.$refs, 'titleLink', { value: el, configurable: true })

		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(false)
		wrapper.vm.$options.watch.isSelected.call(wrapper.vm, true)
		await nextTick()

		expect(el.focus).not.toHaveBeenCalled()
	})

	it('should send SET_SELECTED_ITEM with undefined id', () => {
		wrapper.vm.clearSelected()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
	})

	it('should send SET_SELECTED_ITEM with item on focus when using screen reader mode and item is not selected', () => {
		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(true)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(false)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem.id, key: 'all' })
	})

	it('should not send SET_SELECTED_ITEM with item on focus when not using screen reader mode', () => {
		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(false)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(false)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).not.toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM)

		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(false)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(true)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).not.toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM)
	})

	it('should not send SET_SELECTED_ITEM with item on focus when item is already selected', () => {
		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(true)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(true)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).not.toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM)
	})

	it('should toggle starred state', () => {
		wrapper.vm.$props.item.starred = true

		wrapper.vm.toggleStarred()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.UNSTAR_ITEM, {
			item: wrapper.vm.$props.item,
		})

		wrapper.vm.$props.item.starred = false

		wrapper.vm.toggleStarred()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.STAR_ITEM, {
			item: wrapper.vm.$props.item,
		})
	})

	it('should toggle unread state', () => {
		wrapper.vm.$props.item.keepUnread = false
		wrapper.vm.$props.item.unread = true

		wrapper.vm.toggleRead()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.MARK_READ, {
			item: wrapper.vm.$props.item,
		})

		wrapper.vm.$props.item.unread = false

		wrapper.vm.toggleRead()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.MARK_UNREAD, {
			item: wrapper.vm.$props.item,
		})
	})

	it('should not toggle unread state if keepUnread is set', () => {
		wrapper.vm.$props.item.keepUnread = true
		wrapper.vm.$props.item.unread = true

		wrapper.vm.toggleRead()
		expect(dispatchStub).not.toHaveBeenCalledWith(ACTIONS.MARK_READ, {
			item: wrapper.vm.$props.item,
		})
	})

	it('should set showShareMenu to false', () => {
		wrapper.vm.showShareMenu = true

		wrapper.vm.closeShareMenu()
		expect(wrapper.vm.showShareMenu).toEqual(false)
	})

	it('should return the correct media type', () => {
		let mime = wrapper.vm.getMediaType('audio/mp4')
		expect(mime).toEqual('audio')

		mime = wrapper.vm.getMediaType('video/mpeg')
		expect(mime).toEqual('video')

		mime = wrapper.vm.getMediaType('image/jpeg')
		expect(mime).toEqual('image')

		mime = wrapper.vm.getMediaType('application/pdf')
		expect(mime).toEqual(false)
	})

	it('should send SET_PLAYING_ITEM with item', () => {
		const item = { id: 123 }
		wrapper.vm.playAudio(item)

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_PLAYING_ITEM, item)
	})

	it('should stop all audio elements in page when playing video', () => {
		const pauseStub = vi.fn()
		document.getElementsByTagName = vi.fn().mockReturnValue([{ pause: pauseStub }])

		wrapper.vm.stopAudio()

		expect(pauseStub).toBeCalled()
	})

	it('should emit "prevItem" when calling prevItem', () => {
		wrapper.vm.prevItem()

		expect(wrapper.emitted()).toHaveProperty('prevItem')
		expect(wrapper.emitted('prevItem')!.length).toBe(1)
	})

	it('should emit "nextItem" when calling nextItem', () => {
		wrapper.vm.nextItem()

		expect(wrapper.emitted()).toHaveProperty('nextItem')
		expect(wrapper.emitted('nextItem')!.length).toBe(1)
	})

	it('should emit "showDetails" when calling closeDetails', () => {
		wrapper.vm.closeDetails()

		expect(wrapper.emitted()).toHaveProperty('showDetails')
		expect(wrapper.emitted('showDetails')!.length).toBe(1)
	})

	it('should show no chips when item has no categories', () => {
		const chips = wrapper.findAllComponents({ name: 'NcChip' })

		expect(chips.length).toBe(0)
	})

	it('should show no chips when item.categories is empty', async () => {
		await wrapper.setProps({
			item: { ...mockItem, categories: [] },
		})
		const chips = wrapper.findAllComponents({ name: 'NcChip' })

		expect(chips.length).toBe(0)
	})

	it('should show three chips with text from item.categories', async () => {
		await wrapper.setProps({
			item: { ...mockItem, categories: ['Nextcloud', 'News', 'Reader'] },
		})
		const chips = wrapper.findAllComponents({ name: 'NcChip' })
		expect(chips.length).toBe(3)

		expect(chips[0].text()).toBe('Nextcloud')
		expect(chips[1].text()).toBe('News')
		expect(chips[2].text()).toBe('Reader')
	})

	it('should show thumbnail when media thumbnail is set', async () => {
		await wrapper.setProps({
			item: { ...mockItem, mediaThumbnail: 'thumbnail.jpg', enclosureMime: 'audio/mp3' },
		})

		const enclosure = wrapper.find('div.enclosure.thumbnail')
		expect(enclosure.exists()).toBe(true)

		expect(enclosure.find('.consent-button').exists()).toBe(false)
		expect(enclosure.find('img').exists()).toBe(true)
	})

	it('should not show thumbnail when media thumbnail is set and media mime type is video', async () => {
		await wrapper.setProps({
			item: { ...mockItem, mediaThumbnail: 'thumbnail.jpg', enclosureMime: 'video/mp4' },
		})

		const enclosure = wrapper.find('div.enclosure.thumbnail')
		expect(enclosure.exists()).toBe(false)
	})

	it('should show consent button when show thumbnails is set to ask and remove button and show thumbnail when clicked', async () => {
		wrapper.vm.allowEnclosureThumbnail = false
		mockGetters.mediaOptions = { ...mockGetters.mediaOptions, [MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.ASK }
		await wrapper.setProps({
			item: { ...mockItem, mediaThumbnail: 'thumbnail.jpg' },
		})

		const enclosure = wrapper.find('div.enclosure.thumbnail')
		expect(enclosure.exists()).toBe(true)

		expect(wrapper.find('.consent-button').exists()).toBe(true)
		expect(enclosure.find('img').exists()).toBe(false)

		wrapper.vm.allowThumbnail()
		await nextTick()

		expect(wrapper.find('.consent-button').exists()).toBe(false)
		expect(enclosure.find('img').exists()).toBe(true)
	})

	it('should not show thumbnail when show thumbnails is set to never', async () => {
		mockGetters.mediaOptions = { ...mockGetters.mediaOptions, [MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.NEVER }
		await wrapper.setProps({
			item: { ...mockItem, mediaThumbnail: 'thumbnail.jpg' },
		})

		const enclosure = wrapper.find('div.enclosure.thumbnail')
		expect(enclosure.exists()).toBe(false)
	})

	it('should show enclosure image when enclosureLink is set and enclosureMime is image/*', async () => {
		await wrapper.setProps({
			item: { ...mockItem, enclosureLink: 'image.jpg', enclosureMime: 'image/jpeg' },
		})

		const enclosure = wrapper.find('div.enclosure.image')
		expect(enclosure.exists()).toBe(true)

		expect(enclosure.find('.consent-button').exists()).toBe(false)
		expect(enclosure.find('img').exists()).toBe(true)
	})

	it('should not show enclosure image when enclosureLink is set and enclosureMime is not image/*', async () => {
		await wrapper.setProps({
			item: { ...mockItem, enclosureLink: 'image.jpg', enclosureMime: 'video/mp4' },
		})

		const enclosure = wrapper.find('div.enclosure.image')
		expect(enclosure.exists()).toBe(false)
	})

	it('should not show enclosure image when enclosureLink is set and full text is enabled', async () => {
		await wrapper.setProps({
			item: { ...mockItem, feedId: 2, enclosureLink: 'image.jpg', enclosureMime: 'image/jpeg' },
		})

		const enclosure = wrapper.find('div.enclosure.image')
		expect(enclosure.exists()).toBe(false)
	})

	it('should show consent button when enclosureLink is set and show images is set to ask and remove button when clicked', async () => {
		wrapper.vm.allowEnclosureImage = false
		mockGetters.mediaOptions = { ...mockGetters.mediaOptions, [MEDIA_TYPE.IMAGES]: SHOW_MEDIA.ASK }
		await wrapper.setProps({
			item: { ...mockItem, enclosureLink: 'image.jpg', enclosureMime: 'image/jpeg' },
		})

		const enclosure = wrapper.find('div.enclosure.image')
		expect(enclosure.exists()).toBe(true)

		expect(enclosure.find('.consent-button').exists()).toBe(true)
		expect(enclosure.find('img').exists()).toBe(false)

		wrapper.vm.allowImage()
		await nextTick()

		expect(enclosure.find('.consent-button').exists()).toBe(false)
		expect(enclosure.find('img').exists()).toBe(true)
	})

	it('should not show enclosure image when enclosureLink is set and show images is set to never', async () => {
		mockGetters.mediaOptions = { ...mockGetters.mediaOptions, [MEDIA_TYPE.IMAGES]: SHOW_MEDIA.NEVER }
		await wrapper.setProps({
			item: { ...mockItem, enclosureLink: 'image.jpg', enclosureMime: 'image/jpeg' },
		})

		expect(wrapper.find('.enclosure.thumbnail').exists()).toBe(false)
		expect(wrapper.find('.consent-button').exists()).toBe(false)
	})

	describe('sanitizedBody', () => {
		it('should set audio preload to none', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<audio controls="" preload="auto"></audio>' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ALWAYS },
				modifyNode: vi.fn(),
			}

			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)
			expect(html).toBe('<audio controls="" preload="none"></audio>')
		})

		it('should set video preload to none', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<video controls="" preload="auto"></video>' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ALWAYS },
				modifyNode: vi.fn(),
			}

			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)
			expect(html).toBe('<video controls="" preload="none"></video>')
		})

		it('should not call modifyNode on picture when show media is set to always', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<picture><img src="image1.jpg"><source srcset="image1.jpg 1x, image2.jpg 2x"></picture>' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ALWAYS },
				modifyNode: vi.fn(),
				createConsentButton: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<picture')
			expect(sanitizedVm.modifyNode).not.toHaveBeenCalledWith(expect.anything(), 'img')
			expect(sanitizedVm.modifyNode).not.toHaveBeenCalledWith(expect.anything(), 'source')
		})

		it('should call modifyNode on picture when show media is set to ask', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<picture><img src="image1.jpg"><source srcset="image1.jpg 1x, image2.jpg 2x"></picture>' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ASK },
				modifyNode: vi.fn(),
				createConsentButton: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<picture')
			expect(sanitizedVm.modifyNode).toHaveBeenCalledWith(expect.anything(), 'img')
			expect(sanitizedVm.modifyNode).toHaveBeenCalledWith(expect.anything(), 'source')
		})

		it('should not call modifyNode on picture when show media is set to never', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<picture><img src="image1.jpg"><source srcset="image1.jpg 1x, image2.jpg 2x"></picture>' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.NEVER },
				modifyNode: vi.fn(),
				createConsentButton: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).not.toContain('<picture')
			expect(sanitizedVm.modifyNode).not.toHaveBeenCalledWith(expect.anything(), 'img')
			expect(sanitizedVm.modifyNode).not.toHaveBeenCalledWith(expect.anything(), 'source')
		})

		it('should not call modifyNode on img when show media is set to always', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<img src="image.jpg" srcset="image1.jpg 1x, image2.jpg 2x">' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ALWAYS },
				modifyNode: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<img')
			expect(sanitizedVm.modifyNode).not.toHaveBeenCalledWith(expect.anything(), 'img', SHOW_MEDIA.ALWAYS)
		})

		it('should call modifyNode on img when show media is set to ask', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<img src="image.jpg" srcset="image1.jpg 1x, image2.jpg 2x">' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ASK },
				modifyNode: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<img')
			expect(sanitizedVm.modifyNode).toHaveBeenCalledWith(expect.anything(), 'img', SHOW_MEDIA.ASK)
		})

		it('should call modifyNode on img when show media is set to never', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<img src="image.jpg" srcset="image1.jpg 1x, image2.jpg 2x">' },
				mediaOptions: { [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.NEVER },
				modifyNode: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<img')
			expect(sanitizedVm.modifyNode).toHaveBeenCalledWith(expect.anything(), 'img', SHOW_MEDIA.NEVER)
		})

		it('should not call modifyNode on iframes show media is set to always', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<iframe src="video.mp4">' },
				mediaOptions: { [MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.ALWAYS },
				modifyNode: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<iframe')
			expect(sanitizedVm.modifyNode).not.toHaveBeenCalledWith(expect.anything(), 'iframe', SHOW_MEDIA.ALWAYS)
		})

		it('should call modifyNode on iframes when show media is set to ask', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<iframe src="video.mp4">' },
				mediaOptions: { [MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.ASK },
				modifyNode: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<iframe')
			expect(sanitizedVm.modifyNode).toHaveBeenCalledWith(expect.anything(), 'iframe', SHOW_MEDIA.ASK)
		})

		it('should call modifyNode on iframes when show media is set to never', () => {
			const sanitizedVm = {
				item: { ...mockItem, body: '<iframe src="video.mp4">' },
				mediaOptions: { [MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.NEVER },
				modifyNode: vi.fn(),
			}
			const html = wrapper.vm.$options.computed?.sanitizedBody.call(sanitizedVm)

			expect(html).toContain('<iframe')
			expect(sanitizedVm.modifyNode).toHaveBeenCalledWith(expect.anything(), 'iframe', SHOW_MEDIA.NEVER)
		})
	})

	describe('modifyNode', () => {
		const createConsentButtonMock = vi.fn()
		beforeEach(() => {
			wrapper.vm.createConsentButton = createConsentButtonMock
			createConsentButtonMock.mockClear()
		})

		it('SHOW_MEDIA.NEVER should remove element completely', () => {
			const container = document.createElement('div')
			container.innerHTML = '<img src="image.jpg">'

			wrapper.vm.modifyNode(container, 'img', SHOW_MEDIA.NEVER)

			const img = container.querySelector('img')
			expect(img).toBeNull()
			expect(createConsentButtonMock).not.toHaveBeenCalled()
		})

		it('SHOW_MEDIA.ASK should add consent button, hide element and move src/srcset to data-*', () => {
			const container = document.createElement('div')
			container.innerHTML = '<img src="image.jpg" srcset="image2.jpg" alt="Test">'

			wrapper.vm.modifyNode(container, 'img', SHOW_MEDIA.ASK)

			const img = container.querySelector('img')
			expect(img).not.toBeNull()
			expect(img?.hidden).toBe(true)
			expect(img?.getAttribute('src')).toBeNull()
			expect(img?.getAttribute('srcset')).toBeNull()
			expect(img?.dataset.src).toBe('image.jpg')
			expect(img?.dataset.srcset).toBe('image2.jpg')

			expect(createConsentButtonMock).toHaveBeenCalledTimes(1)
			expect(createConsentButtonMock).toHaveBeenCalledWith(img, 'image.jpg', 'Test')
		})

		it('should create no extra consent-button for img or source in picture tag', () => {
			const container = document.createElement('div')
			container.innerHTML = '<picture><img src="image.jpg" alt="A picture"></picture>'

			wrapper.vm.modifyNode(container, 'img', SHOW_MEDIA.ASK)

			const img = container.querySelector('img')
			expect(img?.hidden).toBe(true)
			expect(createConsentButtonMock).not.toHaveBeenCalled()
		})
	})

	describe('createConsentButton', () => {
		it('should create a consent button with the correct information', () => {
			const src = 'https://example.com/video.mp4'
			const element = document.createElement('img')

			const container = document.createElement('div')
			container.appendChild(element)

			wrapper.element.appendChild(container)
			wrapper.vm.createConsentButton(element, src)

			const button = wrapper.element.querySelector('.consent-button')
			expect(button).not.toBeNull()

			const titleElement = button.querySelector('.consent-title')
			expect(titleElement.textContent).toBe('Show external media (img)')

			const srcElement = button.querySelector('.consent-src')
			expect(srcElement.textContent).toBe('from example.com')
			expect(srcElement.title).toBe('https://example.com/video.mp4')
			expect(srcElement.ariaLabel).toBe('External media loaded from example.com')

			const banner = wrapper.element.querySelector('.consent-banner')
			expect(banner).not.toBeNull()
			expect(banner.contains(button)).toBe(true)
			expect(banner.contains(element)).toBe(true)
		})

		it('should use url encoded string when showing src in consent button tooltip', () => {
			const src = 'https://example.com/image.png\u0000\u200B\u001F\uFEFF'
			const element = document.createElement('img')

			const container = document.createElement('div')
			container.appendChild(element)

			wrapper.element.appendChild(container)
			wrapper.vm.createConsentButton(element, src)

			const srcElement = wrapper.element.querySelector('.consent-src')
			expect(srcElement.title).toBe('https://example.com/image.png%00%E2%80%8B%1F%EF%BB%BF')
		})

		it('should add a description if given', () => {
			const src = 'https://example.com/image.jpg'
			const element = document.createElement('img')

			const container = document.createElement('div')
			container.appendChild(element)

			wrapper.element.appendChild(container)
			wrapper.vm.createConsentButton(element, src, 'A picture')

			const descElement = wrapper.element.querySelector('.consent-desc')
			expect(descElement).not.toBeNull()
			expect(descElement.textContent).toBe('A picture')
		})

		it('should disable text-decoration on a possible parent a-href element', () => {
			const src = 'http://example.com/video.mp4'
			const element = document.createElement('img')

			const container = document.createElement('a')
			container.appendChild(element)
			wrapper.element.appendChild(container)
			wrapper.vm.createConsentButton(element, src)

			const button = wrapper.element.querySelector('.consent-button')
			expect(button).not.toBeNull()

			const parentElement = button.closest('a')
			expect(parentElement).not.toBeNull()
			expect(parentElement.style.textDecoration).toBe('none')
		})
	})

	describe('onConsentClick', () => {
		it('should update picture sources', () => {
			const banner = document.createElement('div')
			banner.className = 'consent-banner'

			const button = document.createElement('button')
			button.textContent = 'Show picture'

			const picture = document.createElement('picture')
			picture.hidden = true
			const source = document.createElement('source')
			source.setAttribute('data-srcset', 'http://example.com/image.jpg')
			picture.appendChild(source)

			banner.appendChild(button)
			banner.appendChild(picture)

			const event = {
				preventDefault: vi.fn(),
				stopPropagation: vi.fn(),
				target: banner,
			}

			wrapper.vm.onConsentClick(event)

			expect(source.srcset).toBe('http://example.com/image.jpg')
			expect(picture.hidden).toBe(false)
		})

		it('should update img src and remove hidden attribute', () => {
			const banner = document.createElement('div')
			banner.className = 'consent-banner'

			const button = document.createElement('button')
			button.textContent = 'Show image'

			const img = document.createElement('img')
			img.setAttribute('data-src', 'http://example.com/image.jpg')
			img.setAttribute('data-srcset', 'http://example.com/image-large.jpg')
			img.hidden = true

			banner.appendChild(button)
			banner.appendChild(img)

			const event = {
				preventDefault: vi.fn(),
				stopPropagation: vi.fn(),
				target: banner,
			}

			wrapper.vm.onConsentClick(event)

			expect(img.src).toBe('http://example.com/image.jpg')
			expect(img.srcset).toBe('http://example.com/image-large.jpg')
			expect(img.loading).toBe('lazy')
			expect(img.decoding).toBe('async')
			expect(img.hidden).toBe(false)
		})

		it('should update iframe src and remove hidden attribute', () => {
			const banner = document.createElement('div')
			banner.className = 'consent-banner'

			const button = document.createElement('button')
			button.textContent = 'Show video'

			const iframe = document.createElement('iframe')
			iframe.setAttribute('data-src', 'http://example.com/video.mp4')
			iframe.hidden = true

			banner.appendChild(button)
			banner.appendChild(iframe)

			const event = {
				preventDefault: vi.fn(),
				stopPropagation: vi.fn(),
				target: banner,
			}

			wrapper.vm.onConsentClick(event)

			expect(iframe.src).toBe('http://example.com/video.mp4')
			expect(iframe.hidden).toBe(false)
		})

		it('should remove the button after click', () => {
			const banner = document.createElement('div')
			banner.className = 'consent-banner'

			const button = document.createElement('button')
			button.textContent = 'Show image'

			const img = document.createElement('img')
			img.setAttribute('data-src', 'http://example.com/image.jpg')
			img.hidden = true

			banner.appendChild(button)
			banner.appendChild(img)

			const event = {
				preventDefault: vi.fn(),
				stopPropagation: vi.fn(),
				target: banner,
			}

			wrapper.vm.onConsentClick(event)

			expect(banner.querySelector('button')).toBeNull()
		})

		it('should not throw an error if no consent-banner is found', () => {
			const noBanner = document.createElement('div')
			const event = {
				preventDefault: vi.fn(),
				stopPropagation: vi.fn(),
				target: noBanner,
			}

			expect(() => wrapper.vm.onConsentClick(event)).not.toThrow()
		})
	})

	afterEach(() => {
		wrapper?.unmount()
		vi.clearAllMocks()
		vi.restoreAllMocks()
	})
})
