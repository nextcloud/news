import type { AppInfoState } from './app'
import type { FeedState } from './feed'
import type { FolderState } from './folder'
import type { ItemState } from './item'

import { APPLICATION_MUTATION_TYPES, FEED_ITEM_MUTATION_TYPES, FEED_MUTATION_TYPES, FOLDER_MUTATION_TYPES } from '../types/MutationTypes'
import app, { APPLICATION_ACTION_TYPES } from './app'
import feeds, { FEED_ACTION_TYPES } from './feed'
import folders, { FOLDER_ACTION_TYPES } from './folder'
import items, { FEED_ITEM_ACTION_TYPES } from './item'

export const MUTATIONS = {
	...APPLICATION_MUTATION_TYPES,
	...FEED_MUTATION_TYPES,
	...FOLDER_MUTATION_TYPES,
	...FEED_ITEM_MUTATION_TYPES,
}

export const ACTIONS = {
	...APPLICATION_ACTION_TYPES,
	...FEED_ACTION_TYPES,
	...FOLDER_ACTION_TYPES,
	...FEED_ITEM_ACTION_TYPES,
}

export type AppState = FolderState & FeedState & ItemState & AppInfoState

type Func = (name: string, value: unknown) => void
export type ActionParams<T> = { commit: Func, dispatch: Func, state: T }

export default {
	modules: {
		feeds,
		folders,
		items,
		app,
	},
}
