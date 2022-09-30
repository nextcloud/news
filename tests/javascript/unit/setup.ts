// import { OC } from './OC.js'

import { config } from '@vue/test-utils'
// eslint-disable-next-line node/no-unpublished-import
// import MockDate from 'mockdate'
// eslint-disable-next-line node/no-unpublished-import
// import 'regenerator-runtime/runtime'

// Set date to fixed value
// MockDate.set(new Date('2019-01-01T12:34:56'))

// global.OC = new OC()

// Mock nextcloud translate functions
config.mocks.$t = function(_app: any, string: any) {
	return string
}
config.mocks.t = config.mocks.$t
// (global as any).t = config.mocks.$t

config.mocks.$n = function(app: any, singular: any, plural: any, count: any) {
	return singular
}
config.mocks.n = config.mocks.$n
// (global as any).n = config.mocks.$n


afterAll(() => {
	// MockDate.reset()
})
