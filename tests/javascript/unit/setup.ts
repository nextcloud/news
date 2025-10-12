import { config } from '@vue/test-utils'
import { vi } from 'vitest'

(global as any).appName = 'news'

// Note: This was copied from nextcloud/tasks repo
import { OC } from './OC'
(global as any).OC = new OC()

// Mock nextcloud translate functions
config.global.mocks.$t = function(_app: any, string: any) {
	return string
}
config.global.mocks.t = config.global.mocks.$t
global.t = config.global.mocks.$t

config.global.mocks.$n = function(app: any, singular: any) {
	return singular
}
config.global.mocks.n = config.global.mocks.$n

// Mock nextcloud helpers
vi.mock('@nextcloud/axios')
vi.mock('@nextcloud/capabilities', () => ({
  getCapabilities: vi.fn(() => ({})),
}))
