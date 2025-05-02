import type { AppInfoState } from './app.ts'
import type { FeedState } from './feed.ts'
import type { FolderState } from './folder.ts'
import type { ItemState } from './item.ts'

import { APPLICATION_MUTATION_TYPES, FEED_ITEM_MUTATION_TYPES, FEED_MUTATION_TYPES, FOLDER_MUTATION_TYPES } from '../types/MutationTypes.ts'
import app, { APPLICATION_ACTION_TYPES } from './app.ts'
import feeds, { FEED_ACTION_TYPES } from './feed.ts'
import folders, { FOLDER_ACTION_TYPES } from './folder.ts'
import items, { FEED_ITEM_ACTION_TYPES } from './item.ts'

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
