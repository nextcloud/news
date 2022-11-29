import { Commit } from "vuex";

import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'
import { FEED_MUTATION_TYPES, FEED_ACTION_TYPES, FEED_MUTATIONS, FEED_ACTIONS } from "./feed";
import { FOLDER_MUTATION_TYPES, FOLDER_ACTION_TYPES, FOLDER_MUTATIONS, FOLDER_ACTIONS } from "./folder";

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

const state: AppState = {
  feeds: [],
  folders: [],
  items: []
} as AppState

const getters = {
  feeds (state: AppState) {
    return state.feeds;
  },
  folders (state: AppState) {
    return state.folders;
  },
}

const mutations = {
  ... FEED_MUTATIONS,
  ... FOLDER_MUTATIONS
}

const actions = {
  ... FEED_ACTIONS,
  ... FOLDER_ACTIONS
}

export default {
  state,
  mutations,
  actions,
  getters
}