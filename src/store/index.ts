import axios from "@nextcloud/axios";
import { generateUrl } from "@nextcloud/router";
import { Commit } from "vuex";

export const MUTATIONS = {
  SET_FEEDS: 'SET_FEEDS',
  SET_FOLDERS: 'SET_FOLDERS'
}

export const ACTIONS = {
  FETCH_FEEDS: 'FETCH_FEEDS',
  FETCH_FOLDERS: 'FETCH_FOLDERS'
}

type Feed = {
  folderId?: number;
  unreadCount: number;
  url: string;
  title?: string;
  autoDiscover?: boolean;
  faviconLink?: string;
}

type Folder = {
  feeds: any[];
  feedCount: number;
  name: string;
  id: number;
}

export type AppState = {
  feeds: any[];
  folders: Folder[];
  items: any[];
}

const state: AppState = {
  feeds: [],
  folders: [],
  items: []
} as AppState

const mutations = {
  [MUTATIONS.SET_FEEDS] (state: AppState, feeds: Feed[]) {
    feeds.forEach(it => {
      state.feeds.push(it)
      const folder = state.folders.find(folder => folder.id === it.folderId)
      if (folder) {
        folder.feeds.push(it)
        folder.feedCount += it.unreadCount
      }
    })
  },
  [MUTATIONS.SET_FOLDERS] (state: AppState, folders: Folder[]) {
    folders.forEach(it => {
      it.feedCount = 0
      it.feeds = []
      state.folders.push(it)
    })
  },
}


const feedUrl = generateUrl("/apps/news/feeds")
const folderUrl = generateUrl("/apps/news/folders")


type ActionParams = { commit: Commit };

const actions = {
  async [ACTIONS.FETCH_FEEDS] ({ commit }: ActionParams) {
    const feeds = await axios.get(
      generateUrl("/apps/news/feeds")
    );

    commit(MUTATIONS.SET_FEEDS, feeds.data.feeds);
  },
  async [ACTIONS.FETCH_FOLDERS] ({ commit }: ActionParams) {
    const folders = await axios.get(
      generateUrl("/apps/news/folders")
    );

    commit(MUTATIONS.SET_FOLDERS, folders.data.folders);
  },
  addFolder({ commit }: ActionParams, { folder }: { folder: Folder}) {
    console.log(folder)
    axios.post(folderUrl, {folderName: folder.name}).then(
        response => commit(MUTATIONS.SET_FOLDERS, response.data.folders)
    );
  },
  deleteFolder({ commit }: ActionParams, { folder }: { folder: Folder}) {
      /**
      this.getByFolderId(folderId).forEach(function (feed) {
          promises.push(self.reversiblyDelete(feed.id, false, true));
      });
      this.updateUnreadCache();
      */
      axios.delete(folderUrl + '/' + folder.id).then()
  },
  // loadFolder({commit}) {
  //     console.log('loading folders')
  //     axios.get(folderUrl).then(
  //         response => {
  //             commit('addFolders', response.data.folders);
  //             axios.get(feedUrl).then(
  //                 response => commit('addFeeds', response.data.feeds)
  //             )
  //         }
  //     )
  // },
  addFeed({ commit }: ActionParams, { feedReq }: { feedReq: { url: string; folder?: { id: number } } }) {
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

const getters = {
  feeds (state: AppState) {
    return state.feeds;
  },
  folders (state: AppState) {
    return state.folders;
  },
}

export default {
  state,
  mutations,
  actions,
  getters
}