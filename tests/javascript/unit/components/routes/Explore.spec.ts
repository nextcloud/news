import axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'
import { getLanguage } from '@nextcloud/l10n'
import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import Explore from '../../../../../src/components/routes/Explore.vue'

vi.mock('@nextcloud/initial-state', () => ({
	loadState: vi.fn((app: string, key: string, fallback: any) => fallback),
}))

vi.mock('@nextcloud/l10n', async (importOriginal) => {
	const actual = await importOriginal()
	return {
		...actual,
		getLanguage: vi.fn(() => 'en'),
	}
})

describe('Explore.vue', () => {
	'use strict'

	beforeEach(() => {
		vi.restoreAllMocks()
		vi.clearAllMocks()
		vi.mocked(getLanguage).mockReturnValue('en')
		vi.mocked(loadState).mockImplementation((app: string, key: string, fallback: any) => fallback)
	})

	it('should initialize without showing AddFeed Component', () => {
		axios.get.mockResolvedValue({ data: { } })

		const wrapper = shallowMount(Explore, {
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
				},
			},
		})

		expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
	})

	it('should use custom URL with language detection (German)', async () => {
		vi.mocked(loadState).mockImplementation((app: string, key: string) => {
			if (key === 'exploreUrl') {
				return 'https://custom.example.com/feeds/'
			}
			if (key === 'defaultExploreUrl') {
				return 'https://default.example.com/'
			}
			return ''
		})
		vi.mocked(getLanguage).mockReturnValue('de')

		const axiosGetSpy = vi.spyOn(axios, 'get').mockResolvedValue({ data: { } })

		const wrapper = shallowMount(Explore, {
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
				},
			},
		})

		await wrapper.vm.$nextTick()

		expect(axiosGetSpy).toHaveBeenCalledWith('https://custom.example.com/feeds/feeds.de.json')
	})

	it('should use default URL when custom URL is empty (always English)', async () => {
		vi.mocked(loadState).mockImplementation((app: string, key: string) => {
			if (key === 'exploreUrl') {
				return ''
			}
			if (key === 'defaultExploreUrl') {
				return 'https://default.example.com/'
			}
			return ''
		})
		vi.mocked(getLanguage).mockReturnValue('de') // User is German but default is English

		const axiosGetSpy = vi.spyOn(axios, 'get').mockResolvedValue({ data: { } })

		const wrapper = shallowMount(Explore, {
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
				},
			},
		})

		await wrapper.vm.$nextTick()

		// Default always uses English since that's all the backend has
		expect(axiosGetSpy).toHaveBeenCalledWith('https://default.example.com/feeds.en.json')
	})

	it('should construct custom URL correctly without trailing slash', async () => {
		vi.mocked(loadState).mockImplementation((app: string, key: string) => {
			if (key === 'exploreUrl') {
				return 'https://custom.example.com/feeds'
			}
			if (key === 'defaultExploreUrl') {
				return 'https://default.example.com/'
			}
			return ''
		})
		vi.mocked(getLanguage).mockReturnValue('en')

		const axiosGetSpy = vi.spyOn(axios, 'get').mockResolvedValue({ data: { } })

		const wrapper = shallowMount(Explore, {
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
				},
			},
		})

		await wrapper.vm.$nextTick()

		expect(axiosGetSpy).toHaveBeenCalledWith('https://custom.example.com/feeds/feeds.en.json')
	})

	it('should fallback to English when custom URL language file not found', async () => {
		vi.mocked(loadState).mockImplementation((app: string, key: string) => {
			if (key === 'exploreUrl') {
				return 'https://custom.example.com/feeds/'
			}
			if (key === 'defaultExploreUrl') {
				return 'https://default.example.com/'
			}
			return ''
		})
		vi.mocked(getLanguage).mockReturnValue('fr')

		const mockData = {
			data: {
				category1: [
					{ title: 'Feed 1', url: 'https://example.com/feed1' },
				],
			},
		}

		const axiosGetSpy = vi.spyOn(axios, 'get')
			.mockRejectedValueOnce(new Error('404 Not Found')) // French file not found
			.mockResolvedValueOnce(mockData) // English fallback succeeds

		const wrapper = shallowMount(Explore, {
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
				},
			},
		})

		await wrapper.vm.$nextTick()

		expect(axiosGetSpy).toHaveBeenCalledWith('https://custom.example.com/feeds/feeds.fr.json')
		expect(axiosGetSpy).toHaveBeenCalledWith('https://custom.example.com/feeds/feeds.en.json')
		expect(wrapper.vm.$data.exploreSites).toHaveLength(1)
	})

	it('should not fallback when default URL fails (always uses English)', async () => {
		vi.mocked(loadState).mockImplementation((app: string, key: string) => {
			if (key === 'exploreUrl') {
				return ''
			}
			if (key === 'defaultExploreUrl') {
				return 'https://default.example.com/'
			}
			return ''
		})
		vi.mocked(getLanguage).mockReturnValue('de')

		const axiosGetSpy = vi.spyOn(axios, 'get')
			.mockRejectedValueOnce(new Error('404 Not Found'))

		const wrapper = shallowMount(Explore, {
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
				},
			},
		})

		await wrapper.vm.$nextTick()

		expect(axiosGetSpy).toHaveBeenCalledTimes(1)
		// Default always tries English
		expect(axiosGetSpy).toHaveBeenCalledWith('https://default.example.com/feeds.en.json')
		expect(wrapper.vm.$data.exploreSites).toBeUndefined()
	})
})
