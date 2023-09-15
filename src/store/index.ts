import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'
import feeds, { FEED_ACTION_TYPES } from './feed'
import folders, { FOLDER_ACTION_TYPES } from './folder'
import { FOLDER_MUTATION_TYPES, FEED_MUTATION_TYPES, FEED_ITEM_MUTATION_TYPES } from '../types/MutationTypes'
import items, { FEED_ITEM_ACTION_TYPES, ItemState } from './item'

export const MUTATIONS = {
	...FEED_MUTATION_TYPES,
	...FOLDER_MUTATION_TYPES,
	...FEED_ITEM_MUTATION_TYPES,
}

export const ACTIONS = {
	...FEED_ACTION_TYPES,
	...FOLDER_ACTION_TYPES,
	...FEED_ITEM_ACTION_TYPES,
}

export type AppState = {
  feeds: Feed[];
  folders: Folder[];
} & ItemState;

type Func = (name: string, value: unknown) => void;
export type ActionParams = { commit: Func; dispatch: Func; state: AppState };

export default {
	modules: {
		feeds,
		folders,
		items,
	},
}
