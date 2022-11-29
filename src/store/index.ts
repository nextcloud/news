import { Commit, Store } from "vuex";

import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'
import feeds, { FEED_MUTATION_TYPES, FEED_ACTION_TYPES } from "./feed";
import folders, { FOLDER_MUTATION_TYPES, FOLDER_ACTION_TYPES } from "./folder";

export const MUTATIONS = {
 ... FEED_MUTATION_TYPES,
 ... FOLDER_MUTATION_TYPES
}

export const ACTIONS = {
  ... FEED_ACTION_TYPES,
  ... FOLDER_ACTION_TYPES
}

export type ActionParams = { commit: Commit };

export type AppState = {
  feeds: Feed[];
  folders: Folder[];
  items: any[];
}


export default {
  modules: {
    feeds,
    folders
  }
};