import { config } from '@vue/test-utils'

// Note: This was copied from nextcloud/tasks repo
// import { OC } from './OC.js'

// TODO: will this be used?
// global.OC = new OC()

// Mock nextcloud translate functions
config.mocks.$t = function(_app: any, string: any) {
	return string
}
config.mocks.t = config.mocks.$t

config.mocks.$n = function(app: any, singular: any, plural: any, count: any) {
	return singular
}
config.mocks.n = config.mocks.$n

afterAll(() => {
	
})
