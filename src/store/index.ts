import { FOLDER_MUTATION_TYPES, FEED_MUTATION_TYPES, FEED_ITEM_MUTATION_TYPES, APPLICATION_MUTATION_TYPES } from '../types/MutationTypes'
import feeds, { FEED_ACTION_TYPES, FeedState } from './feed'
import folders, { FOLDER_ACTION_TYPES, FolderState } from './folder'
import items, { FEED_ITEM_ACTION_TYPES, ItemState } from './item'
import app, { APPLICATION_ACTION_TYPES, AppInfoState } from './app'

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

export type AppState = FolderState & FeedState & ItemState & AppInfoState;

type Func = (name: string, value: unknown) => void;
export type ActionParams<T> = { commit: Func; dispatch: Func; state: T };

export default {
	modules: {
		feeds,
		folders,
		items,
		app,
	},
}
