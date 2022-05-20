import axios from "@nextcloud/axios";
import { generateUrl } from "@nextcloud/router";

export const MUTATIONS = {
  SET_FEEDS: 'SET_FEEDS',
  SET_FOLDERS: 'SET_FOLDERS'
}

export const ACTIONS = {
  FETCH_FEEDS: 'FETCH_FEEDS',
  FETCH_FOLDERS: 'FETCH_FOLDERS'
}

const state = {
  feeds: [],
  folders: [],
  items: []
}

const mutations = {
  [MUTATIONS.SET_FEEDS] (state, feeds) {
    feeds.forEach(it => {
      state.feeds.push(it)
      const folder = state.folders.find(folder => folder.id === it.folderId)
      if (folder) {
        folder.feeds.push(it)
        folder.feedCount += it.unreadCount
      }
    })
  },
  [MUTATIONS.SET_FOLDERS] (state, folders) {
    folders.forEach(it => {
      it.feedCount = 0
      it.feeds = []
      state.folders.push(it)
    })
  },
}


const feedUrl = generateUrl("/apps/news/feeds")
const folderUrl = generateUrl("/apps/news/folders")

const actions = {
  async [ACTIONS.FETCH_FEEDS] ({ commit }) {
    const feeds = await axios.get(
      generateUrl("/apps/news/feeds")
    );

    commit(MUTATIONS.SET_FEEDS, feeds.data.feeds);
  },
  async [ACTIONS.FETCH_FOLDERS] ({ commit }) {
    const folders = await axios.get(
      generateUrl("/apps/news/folders")
    );

    commit(MUTATIONS.SET_FOLDERS, folders.data.folders);
  },
  addFolder({commit}, {folder}) {
    axios.post(folderUrl, {folderName: folder.name}).then(
        response => commit('addFolders', response.data.folders)
    );
  },
  deleteFolder({commit}, {folder}) {
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
  addFeed({commit}, {feedReq}) {
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

      let feed = {
          url: url,
          folderId: feedReq.folder.id || 0,
          title: null,
          unreadCount: 0
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
      }).then();
  }
}

const getters = {
  feeds (state) {
    return state.feeds;
  },
  folders (state) {
    return state.folders;
  },
}

export default {
  state,
  mutations,
  actions,
  getters
}
