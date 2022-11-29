import axios from "@nextcloud/axios";
import { generateUrl } from "@nextcloud/router";

import { ActionParams, AppState } from 'src/store'
import { Feed } from '../types/Feed'

export const FEED_MUTATION_TYPES = {
  SET_FEEDS: 'SET_FEEDS',
}

export const FEED_ACTION_TYPES = {
  ADD_FEED: 'ADD_FEED',
  FETCH_FEEDS: 'FETCH_FEEDS',
}

const state = {
  feeds: []
}

const getters = {
  feeds (state: AppState) {
    return state.feeds;
  },
}

const feedUrl = generateUrl("/apps/news/feeds")

export const actions = {
  async [FEED_ACTION_TYPES.FETCH_FEEDS] ({ commit }: ActionParams) {
    const feeds = await axios.get(
      generateUrl("/apps/news/feeds")
    );

    commit(FEED_MUTATION_TYPES.SET_FEEDS, feeds.data.feeds);
  },
  [FEED_ACTION_TYPES.ADD_FEED] ({ commit }: ActionParams, { feedReq }: { feedReq: { url: string; folder?: { id: number } } }) {
      console.log(feedReq)
      let url = feedReq.url.trim();
      if (!url.startsWith('http')) {
          url = 'https://' + url;
      }

      /**
      if (title !== undefined) {
          title = title.trim();
      }
      */

      let feed: Feed = {
          url: url,
          folderId: feedReq.folder?.id || 0,
          title: undefined,
          unreadCount: 0,
          autoDiscover: undefined // TODO
      };

      // this.add(feed);
      // this.updateFolderCache();

      axios.post(feedUrl, {
          url: feed.url,
          parentFolderId: feed.folderId,
          title: null,
          user: null,
          password: null,
          fullDiscover: feed.autoDiscover
      }).then(() => {
        commit('addFeed', feed)
      });
  }
}

export const mutations = {
  [FEED_MUTATION_TYPES.SET_FEEDS] (state: AppState, feeds: Feed[]) {
    feeds.forEach(it => {
      state.feeds.push(it)
      const folder = state.folders.find(folder => folder.id === it.folderId)
      if (folder) {
        folder.feeds.push(it)
        folder.feedCount += it.unreadCount
      }
    })
  },
}

export default {
  state,
  getters,
  actions,
  mutations
}