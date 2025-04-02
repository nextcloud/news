import { config } from '@vue/test-utils'

(global as any).appName = 'news'

// Note: This was copied from nextcloud/tasks repo
import { OC } from './OC'
(global as any).OC = new OC()

// Mock nextcloud translate functions
config.mocks.$t = function(_app: any, string: any) {
	return string
}
config.mocks.t = config.mocks.$t
global.t = config.mocks.$t

config.mocks.$n = function(app: any, singular: any) {
	return singular
}
config.mocks.n = config.mocks.$n

afterAll(() => {
	// TODO: afterAll tests?
})
