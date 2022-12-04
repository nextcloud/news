import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'
import feeds, { FEED_MUTATION_TYPES, FEED_ACTION_TYPES } from './feed'
import folders, { FOLDER_MUTATION_TYPES, FOLDER_ACTION_TYPES } from './folder'

export const MUTATIONS = {
	...FEED_MUTATION_TYPES,
	...FOLDER_MUTATION_TYPES,
}

export const ACTIONS = {
	...FEED_ACTION_TYPES,
	...FOLDER_ACTION_TYPES,
}

type Func = (name: string, value: unknown) => void;
export type ActionParams = { commit: Func };

export type AppState = {
  feeds: Feed[];
  folders: Folder[];
}

export default {
	modules: {
		feeds,
		folders,
	},
}
